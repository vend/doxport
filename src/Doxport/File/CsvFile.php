<?php

namespace Doxport\File;

use Doxport\Exception\UnimplementedException;

class CsvFile extends AbstractFile
{
    /**
     * Writes the given object to the file
     *
     * @param array $object
     * @return int
     */
    public function writeObject($object)
    {
        return fputcsv($this->file, $object);
    }

    /**
     * @inheritDoc
     * @return array
     * @throws UnimplementedException
     * @todo Unimplemented
     * @todo Should read line by line
     */
    public function readObject()
    {
        throw new UnimplementedException();
    }
}
