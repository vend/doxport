<?php

namespace Doxport\File;

use LogicException;

/**
 * A JSON file that follows the naive approach to decoding large JSON files:
 * the whole file is read into memory, then json_decode() is uses to decode it
 * into the correct type.
 *
 * @see JsonStreamFile
 */
class JsonWholeFile extends AbstractJsonFile
{
    /**
     * An array of decoded objects, ready to be returned by readObject
     *
     * @var array<array>
     */
    protected $objects;

    /**
     * @inheritDoc
     */
    public function __construct($path)
    {
        parent::__construct($path);

        $this->objects = null;
    }

    /**
     * Reads all objects from the file, all at once
     *
     * @return array
     * @throws LogicException
     */
    protected function readObjects()
    {
        $content = trim($this->readAll());

        if (!$content) {
            return [];
        }

        $decoded = json_decode($content, true);

        if (!is_array($decoded)) {
            throw new LogicException('Expected wrapping array within JSON content');
        }

        foreach ($decoded as &$object) {
            $object = $this->decode($object);
        }

        return $decoded;
    }

    /**
     * Read the next object from the file, using the already-decoded array
     *
     * @return false|array
     */
    public function readObject()
    {
        if (!isset($this->objects)) {
            $this->objects = $this->readObjects();
        }

        if (count($this->objects) < 1) {
            return false;
        }

        return array_shift($this->objects);
    }
}
