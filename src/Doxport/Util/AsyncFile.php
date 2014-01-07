<?php

namespace Doxport\Util;

use Doxport\Exception\IOException;
use LogicException;

class AsyncFile
{
    /**
     * @var resource
     */
    protected $file;

    /**
     * @param string $path
     * @param string $mode
     */
    public function __construct($path, $mode = 'a')
    {
        if (!is_dir($dir = dirname($path))) {
            mkdir($dir, 0644, true);
        }

        $this->path = $path;
        $this->open($mode);
    }

    /**
     * Opens the file
     *
     * Usually only called directly when reopening a file, because the constructor
     * will open the file to start with.
     *
     * @param string $path
     * @param string $mode
     * @throws \LogicException
     * @throws \IOException
     */
    public function open($mode)
    {
        if ($this->file) {
            throw new LogicException('File already open');
        }

        $this->file = fopen($this->path, $mode);

        if (!$this->file) {
            throw new IOException('Could not open file: ' . $this->path);
        }
    }

    /**
     * Flushes the file to disk
     *
     * @return void
     */
    public function flush()
    {
        fflush($this->file);
    }

    /**
     * Doesn't do anything unless the eio extension is loaded
     *
     * Will block until the sync is complete
     */
    public function sync()
    {
        if (extension_loaded('eio')) {
            $success = false;

            eio_fsync($this->file, null, function ($data, $result) use (&$success) {
                if ($result === 0) {
                    $success = true;
                }
            });

            eio_event_loop();

            if (!$success) {
                throw new IOException('Could not sync file to disk');
            }
        }
    }

    /**
     * Writes to the file
     *
     * @param $string
     * @param null $length
     */
    public function write($string, $length = null)
    {
        fwrite($this->file, $string, $length);
    }

    /**
     * Writes a CSV row to the file
     *
     * @param array $values
     */
    public function writeCsvRow(array $values)
    {
        fputcsv($this->file, $values);
    }

    /**
     * Closes the file
     */
    public function close()
    {
        if ($this->file) {
            fclose($this->file);
        }

        $this->file = null;
    }

    /**
     * Closes the file on destruct
     *
     * Better not to rely on this, and just call ->close() yourself
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->path;
    }
}
