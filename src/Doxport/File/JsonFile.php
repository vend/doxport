<?php

namespace Doxport\File;

use InvalidArgumentException;
use LogicException;

/**
 * JSON file output
 *
 * Two things JSON doesn't quite support that we need here:
 *  - We need a file with a streaming format, so we can just append new entries
 *     to the end. So, this class ensures there's a wrapping array, and adds objects
 *     to it ([]).
 *  - We need to support arbitrary binary strings (JSON only supports valid UTF-8
 *     strings). This class encodes such values with base64, and decodes when the
 *     objects are read.
 */
class JsonFile extends AsyncFile
{
    /**
     * JSON does not support UTF-8
     *
     * Where we're trying to encode an object into the JSON file, and that object
     * has invalid UTF-8 binary data, we further encode the value with base64. This
     * key tracks which keys have been encoded, so that we can decode the correct
     * binary value back out when we read the file.
     */
    const ENCODED_KEY = '__encoded';

    /**
     * Whether the file is ready to receive writes
     *
     * @var boolean
     */
    protected $prepared = false;

    /**
     * Extra behaviour for JSON:
     *  - On open, check the first character is an open bracket of a JSON array
     *  - If not, truncate the file to the first character
     *  - If it is, check the end of the file, and remove the close bracket, add a comma
     *  - Leave the file pointer position at the correct place to start writing
     *
     * @throws LogicException
     */
    protected function prepare()
    {
        if ($this->prepared) {
            return;
        }

        if ($this->getFirstCharacter() != '[') {
            fwrite($this->file, '[');
            ftruncate($this->file, 1);
        } else {
            if ($this->getLastCharacter() != ']') {
                throw new LogicException('Invalid JSON file: mismatched wrapping array brackets');
            }

            if (ftell($this->file) != 2) {
                $this->writeToLastCharacter(',');
            }
        }

        $this->prepared = true;
    }

    /**
     * Writes the given object to the file
     *
     * @param \stdClass $object
     * @return void
     * @throws InvalidArgumentException
     */
    public function writeObject($object)
    {
        if (!$this->prepared) {
            $this->prepare();
            $this->prepared = true;
        }

        foreach ($object as &$value) {
            if (is_resource($value)) {
                $value = stream_get_contents($value);
            }
        }

        $encoded = $this->encode($object);
        $this->write($encoded . ',');
    }


    /**
     * @param object $object
     * @param bool $allowBinary
     * @return string
     * @throws InvalidArgumentException
     */
    protected function encode($object, $allowBinary = true)
    {
        $json = json_encode($object);

        if ($last = json_last_error()) {
            switch ($last) {
                case JSON_ERROR_DEPTH:
                    throw new InvalidArgumentException('Maximum stack depth exceeded');
                case JSON_ERROR_STATE_MISMATCH:
                    throw new InvalidArgumentException('Underflow or the modes mismatch');
                case JSON_ERROR_CTRL_CHAR:
                    throw new InvalidArgumentException('Unexpected control character found');
                case JSON_ERROR_SYNTAX:
                    throw new InvalidArgumentException('Syntax error, malformed JSON');
                case JSON_ERROR_NONE:
                    break;
                default:
                    throw new InvalidArgumentException('Unknown error in JSON encode');
                case JSON_ERROR_UTF8:
                    if (!$allowBinary) {
                        throw new InvalidArgumentException('Invalid binary string in object to encode; JSON strings must be UTF-8');
                    } else {
                        $object = $this->encodeBinary($object);
                        $json = $this->encode($object, false);
                    }
            }
        }

        return $json;
    }

    protected function encodeBinary($object)
    {
        $object[self::ENCODED_KEY] = [];

        foreach ($object as $key => &$value) {
            if ($key == self::ENCODED_KEY) {
                continue;
            }

            if (!is_scalar($value)) {
                $b = 2;
            }

            if (!mb_check_encoding($value, 'utf-8')) {
                $value = base64_encode($value);
                $object[self::ENCODED_KEY][] = $key;
            }
        }

        return $object;
    }

    protected function decode($content)
    {
        $object = json_decode($content, true);

        if (isset($object[self::ENCODED_KEY])) {
            foreach ($object[self::ENCODED_KEY] as $encoded) {
                if (!isset($object[$encoded])) {
                    continue;
                }

                $object[$encoded] = base64_decode($object[$encoded]);
            }

            unset($object[self::ENCODED_KEY]);
        }

        return $object;
    }

    /**
     * @inheritDoc
     * @return array
     */
    public function readObjects()
    {
        $content = trim($this->readAll());

        if (!$content) {
            return [];
        }

        return $this->decode($content);
    }

    /**
     * Extra behaviour for JSON:
     *  - Check the last character of the file is a comma (or open bracket if file otherwise empty)
     *  - Remove the
     *
     * @inheritDoc
     * @throws LogicException
     */
    public function close()
    {
        if ($this->prepared) {
            $last = $this->getLastCharacter();

            if ($last == ',') {
                $this->writeToLastCharacter(']');
            } elseif ($last == '[') {
                fwrite($this->file, ']');
            } elseif ($last != ']') {
                throw new LogicException('Unexpected character at end of file');
            }

            $this->prepared = false;
        }

        parent::close();
    }

    /**
     * Writes to the last character
     *
     * Leaves the file pointer just after the last character, at the end of the
     * file
     *
     * @param string $char
     */
    protected function writeToLastCharacter($char)
    {
        fseek($this->file, -1, SEEK_END);
        fwrite($this->file, $char);
    }

    /**
     * Gets the first character of the file
     *
     * Leaves the file pointer just after the first character in the file
     *
     * @return string
     */
    protected function getFirstCharacter()
    {
        fseek($this->file, 0, SEEK_SET);
        return fread($this->file, 1);
    }

    /**
     * Gets the last character of the file
     *
     * Leaves the file point just after the last character in the file
     *
     * @return string
     */
    protected function getLastCharacter()
    {
        fseek($this->file, -1, SEEK_END);
        return fread($this->file, 1);
    }
}
