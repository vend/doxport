<?php

namespace Doxport\File;

class CsvFile extends AsyncFile
{
    /**
     * Writes the given object to the file
     *
     * @param \stdClass|array $object
     * @return int
     */
    public function writeObject($object)
    {
        if ($object instanceof \stdClass) {
            $object = (array)$object;
        }

        return fputcsv($this->file, $object);
    }
}
