<?php

namespace Doxport\File;

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
     */
    public function writeObject($object)
    {
        if (!$this->prepared) {
            $this->prepare();
            $this->prepared = true;
        }

        $this->write(json_encode($object) . ',');
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
