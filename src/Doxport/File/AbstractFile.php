<?php

namespace Doxport\File;

use Doxport\Exception\IOException;

/**
 * File helper class
 *
 * Provides fsync support via eio extension
 */
abstract class AbstractFile
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
     * Reads the next object from the file
     *
     * If there are no more objects to be read, returns false
     *
     * @return array|false
     */
    abstract public function readObject();

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

        rewind($this->file);
    }

    /**
     * Reads the whole of the file into a string and returns it
     *
     * @throws IOException
     * @return string
     */
    public function readAll()
    {
        if (!$this->file) {
            throw new IOException('Cannot read all from file: file is not open');
        }

        rewind($this->file);

        $this->rewind();

        $contents = '';

        while (!feof($this->file)) {
            $contents .= $this->readChunk();
        }

        return $contents;
    }

    public function rewind()
    {
        if (!$this->file) {
            throw new IOException('Cannot rewind file: file is not open');
        }

        rewind($this->file);
    }

    /**
     * Reads a chunk of the file into a string and returns it
     *
     * @param int $size
     * @throws IOException
     * @return string
     */
    public function readChunk($size = 8192)
    {
        if (!$this->file) {
            throw new IOException('Cannot read from file: file is not open');
        }

        return fread($this->file, $size);
    }

    /**
     * Flushes the file to disk
     *
     * @throws IOException
     * @return void
     */
    public function flush()
    {
        if (!$this->file) {
            throw new IOException('Cannot flush file: file is not open');
        }

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
        if (!$this->file) {
            throw new IOException('Cannot sync file: file is not open');
        }

        if (extension_loaded('eio')) {
            $success = false;

            eio_fsync($this->file, null, function ($data, $result) use (&$success) {
                if ($result === 0) {
                    $success = true;
                }
            });

            eio_event_loop();

            if (!$success) {
                throw new IOException('Failed to sync file to disk');
            }
        }
    }

    /**
     * Writes to the file
     *
     * @param string $string
     * @throws IOException
     * @return int
     */
    protected function write($string)
    {
        if (!$this->file) {
            throw new IOException('Cannot write to file: file is not open');
        }

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
        return $this->write($string . "\n");
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
