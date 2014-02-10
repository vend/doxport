<?php

namespace Doxport\File;

use Doxport\Exception\IOException;
use \LogicException;

/**
 * File helper class
 *
 * Provides fsync support via eio extension
 */
abstract class AsyncFile
{
    /**
     * @var resource
     */
    protected $file;

    /**
     * Writes the given object to the file
     *
     * @param \stdClass|array $object
     * @return void
     */
    abstract public function writeObject($object);

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
     * Reads from the current position to the end of the file into a string
     * and returns it
     *
     * @return string
     */
    public function readAll()
    {
        if (!$this->isOpen()) {
            return file_get_contents($this->path);
        }

        $contents = '';

        while (!feof($this->file)) {
            $contents .= fread($this->file, 8192);
        }

        return $contents;
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
     * @return int
     */
    public function write($string)
    {
        return fwrite($this->file, $string);
    }

    /**
     * Writes a line to the file
     *
     * @param string $string
     * @return int
     */
    public function writeln($string)
    {
        return fwrite($this->file, $string . "\n");
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
            $this->sync();
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
