<?php

namespace Doxport\File;

use Doxport\Exception\IOException;
use janeklb\json\JSONCharInputReader;
use janeklb\json\JSONChunkProcessor;

class JsonStreamFile extends AbstractJsonFile implements JSONChunkProcessor
{
    /**
     * Current chunk of file content being processed
     *
     * Character array (the strings in this array are all of length 1)
     *
     * @var array<string>
     */
    protected $chunk = [];

    /**
     * Current chunk of JSON content being processed
     *
     * Converted from $chunk -> $jsonChunk by $reader
     *
     * @var string
     */
    protected $jsonChunk = null;

    /**
     * The stream JSON reader we use to pull chunks out of the stream
     *
     * @var \janeklb\json\JSONCharInputReader
     */
    protected $reader;

    /**
     * @inheritDoc
     */
    public function __construct($path)
    {
        parent::__construct($path);

        $this->reader = new JSONCharInputReader($this);
    }

    /**
     * Reads the next object from the file
     *
     * If there are no more objects to be read, returns false
     *
     * @throws IOException
     * @return array|false
     */
    public function readObject()
    {
        if (!$this->file) {
            throw new IOException('Cannot read object from file: file is not open');
        }

        while (true) {
            if (!count($this->chunk)) {
                $read = $this->readChunk();

                if ($read === false || $read === '') {
                    if (!feof($this->file)) {
                        throw new IOException('End of stream processing, but not at end of file?!');
                    }

                    return false;
                }

                $this->chunk = str_split($read);
            }

            $this->reader->readChar(array_shift($this->chunk));

            if ($this->jsonChunk !== null) {
                $decoded = $this->decode(json_decode($this->jsonChunk, true));
                $this->jsonChunk = null;
                return $decoded;
            }
        }
    }

    /**
     * Receives a valid JSON "chunk" when the processor has received enough
     * chars
     *
     * @param string $jsonChunk a chunk of JSON data
     * @return void
     */
    public function process($jsonChunk)
    {
        $this->jsonChunk = $jsonChunk;
    }
}
