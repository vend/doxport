<?php

namespace Doxport\File;

class CsvFile extends AsyncFile
{
    /**
     * Writes the given object to the file
     *
     * @param \stdClass $object
     * @return void
     */
    public function writeObject($object)
    {
        return fputcsv($this->file, $object);
    }
}
