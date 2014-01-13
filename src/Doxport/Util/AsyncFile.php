<?php

namespace Doxport\Util;

use Doxport\Exception\IOException;
use \LogicException;

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
    public function __construct($path, $mode = null)
    {
        if (!is_dir($dir = dirname($path))) {
            mkdir($dir, 0644, true);
        }

        $this->path = $path;

        if ($mode) {
            $this->open($mode);
        }
    }

    /**
     * Opens the file
     *
     * @param string $mode
     * @throws LogicException If the file is already open
     * @throws IOException    If the file could not be opened
     * @return void
     */
    public function open($mode)
    {
        if ($this->isOpen()) {
            throw new LogicException('File already open');
        }

        $this->file = fopen($this->path, $mode);

        if (!$this->file) {
            throw new IOException('Could not open file: ' . $this->path);
        }
    }

    /**
     * @return bool
     */
    public function isOpen()
    {
        return (bool)$this->file;
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
     *
     * @throws IOException If the sync fails
     * @return void
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
     * @param string $string
     * @param int    $length
     * @return void
     */
    public function write($string, $length = null)
    {
        fwrite($this->file, $string, $length);
    }

    /**
     * Writes a line to the file
     *
     * @param string $string
     * @param int    $length
     * @return void
     */
    public function writeln($string, $length = null)
    {
        fwrite($this->file, $string . "\n", $length);
    }


    /**
     * Writes a CSV row to the file
     *
     * @param array $values
     * @return void
     */
    public function writeCsvRow(array $values)
    {
        fputcsv($this->file, $values);
    }

    /**
     * Closes the file
     *
     * @return void
     */
    public function close()
    {
        if ($this->file) {
            $this->flush();
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
        return $this->getPath();
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
}
