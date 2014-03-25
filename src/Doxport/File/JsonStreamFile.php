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
     * @var string
     */
    protected $chunk = '';

    /**
     * Current chunk of JSON content being processed
     *
     * Converted from $chunk -> $jsonChunk by $reader
     *
     * @var string
     */
    protected $jsonChunk = '';

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
            throw new IOException('Cannot read all from file: file is not open');
        }

        while (!$this->jsonChunk) {
            if (!$this->chunk) {
                $this->chunk = $this->readChunk();
            }

            if ($this->chunk === '') {
                return false; // No next object available
            }

            // Dequeue a char
            $char = substr($this->chunk, 0, 1);
            $this->chunk = substr($this->chunk, 1);

            // Pass to char input reader
            $this->reader->readChar($char);

            if ($this->jsonChunk) {
                $decoded = $this->decode(json_decode($this->jsonChunk, true));
                $this->jsonChunk = false;

                return $decoded;
            }
        }
    }

    /**
     * Receives a valid JSON "chunk" when the processor has recieved enough
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
