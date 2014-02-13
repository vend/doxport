<?php

namespace Doxport\File;

use InvalidArgumentException;
use LogicException;

class JsonFile extends AsyncFile
{
    /**
     * Whether the file is ready to receive writes
     *
     * @var boolean
     */
    protected $prepared = false;

    /**
     * Extra behaviour for JSON:
     *  - Only support readable streams
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

        $encoded = json_encode($object);

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
                case JSON_ERROR_UTF8:
                    throw new InvalidArgumentException('Malformed UTF-8 characters, possibly incorrectly encoded');
                case JSON_ERROR_NONE:
                    break;
                default:
                    throw new InvalidArgumentException('Unknown error in JSON encode');
            }
        }

        $this->write($encoded . ',');
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

        return json_decode($content, true);
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
