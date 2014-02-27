<?php

namespace Doxport\File;

use Doxport\Exception\IOException;

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
     * @var string
     */
    protected $path;

    /**
     * Writes the given object to the file
     *
     * @param \stdClass|array $object
     * @return void
     */
    abstract public function writeObject($object);

    /**
     * Reads all objects in the file
     *
     * @return array
     */
    abstract public function readObjects();

    /**
     * @param string $path
     * @throws IOException
     */
    public function __construct($path)
    {
        if (!is_dir($dir = dirname($path))) {
            mkdir($dir, 0755, true);
        }

        $this->path = $path;
        $this->file = fopen($this->path, 'c+');

        if (!$this->file) {
            throw new IOException('Could not open file: ' . $this->path);
        }

        // Seek to end of file (can't use 'a+' mode to do so, won't allow seek for writes)
        fseek($this->file, 0, SEEK_END);
    }

    /**
     * Reads the whole of the file into a string and returns it
     *
     * @return string
     */
    public function readAll()
    {
        rewind($this->file);

        $contents = '';
        while (!feof($this->file)) {
            $contents .= fread($this->file, 8192);
        }

        return $contents;
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
    protected function write($string)
    {
        fseek($this->file, 0, SEEK_END);
        return fwrite($this->file, $string);
    }

    /**
     * Writes a line to the file
     *
     * @param string $string
     * @return int
     */
    protected function writeln($string)
    {
        fseek($this->file, 0, SEEK_END);
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
