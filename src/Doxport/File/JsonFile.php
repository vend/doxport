<?php

namespace Doxport\File;

use Doxport\Exception\InvalidArgumentException;
use Doxport\Exception\LogicException;

/**
 * A file formed by concatenating JSON objects with newlines between them
 *
 * The overall file is not a valid JSON file, but may be converted into one
 * easily:
 *   - Replace literal newlines with commas
 *   - Wrap entire file in square brackets
 *
 * Assumptions:
 *  - PHP's json_encode does not return any literal newlines
 *  - We need to support arbitrary binary strings (JSON only supports valid UTF-8
 *     strings). This class encodes such values with base64, and decodes when the
 *     objects are read.
 */
class JsonFile extends AbstractFile
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
     * Writes the given object to the file
     *
     * @param array $object
     * @return void
     * @throws InvalidArgumentException
     */
    public function writeObject($object)
    {
        foreach ($object as &$value) {
            if (is_resource($value)) {
                $value = stream_get_contents($value);
            }
        }

        $encoded = $this->encode($object);
        $this->write($encoded . "\n");
    }

    /**
     * Read the next object from the file, using the already-decoded array
     *
     * @throws LogicException
     * @return false|array
     */
    public function readObject()
    {
        $content = $this->readLine();

        if (!$content || feof($this->file)) {
            return false;
        }

        $decoded = json_decode($content, true);
        $decoded = $this->decode($decoded);

        if (!is_array($decoded)) {
            throw new LogicException('Expected JSON to contain an object');
        }

        return $decoded;
    }

    /**
     * @param array   $object
     * @param boolean $allowBinary
     * @return string
     * @throws InvalidArgumentException
     */
    protected function encode($object, $allowBinary = true)
    {
        $json = @json_encode($object);

        if ($last = json_last_error()) {
            switch ($last) {
                case JSON_ERROR_NONE:
                    break;

                case JSON_ERROR_DEPTH:
                    throw new InvalidArgumentException('Maximum stack depth exceeded');
                case JSON_ERROR_STATE_MISMATCH:
                    throw new InvalidArgumentException('Underflow or the modes mismatch');
                case JSON_ERROR_CTRL_CHAR:
                    throw new InvalidArgumentException('Unexpected control character found');
                case JSON_ERROR_SYNTAX:
                    throw new InvalidArgumentException('Syntax error, malformed JSON');

                case JSON_ERROR_UTF8:
                    if (!$allowBinary) {
                        throw new InvalidArgumentException(
                            'Invalid binary string in object to encode; JSON strings must be UTF-8'
                        );
                    }

                    $object = $this->encodeBinary($object);
                    $json = $this->encode($object, false);
                    break;

                default:
                    throw new InvalidArgumentException('Unknown error in JSON encode');
            }
        }

        return $json;
    }

    /**
     * @param array $object
     * @return array
     */
    protected function encodeBinary($object)
    {
        $encoded = [];

        foreach ($object as $key => $value) {
            if ($key == self::ENCODED_KEY) {
                continue;
            }

            if (is_scalar($value) && !mb_check_encoding($value, 'utf-8')) {
                $object[$key] = base64_encode($value);
                $encoded[] = $key;
            }
        }

        $object[self::ENCODED_KEY] = $encoded;
        return $object;
    }

    /**
     * @param array $object
     * @return array
     */
    protected function decode($object)
    {
        if (!empty($object[self::ENCODED_KEY])) {
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
}
