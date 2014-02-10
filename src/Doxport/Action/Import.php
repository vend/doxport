<?php

namespace Doxport\Action;

use Doxport\Action\Base\Action;
use LogicException;

class Import extends Action
{
    protected $dataDirectory;

    public function setDataDirectory($dir)
    {
        $this->dataDirectory = $dir;
    }

    public function run()
    {
        $this->validate();
    }

    protected function validate()
    {
        if (!is_dir($this->dataDirectory)) {
            throw new LogicException('Cannot find data directory: ' . $this->dataDirectory);
        }


    }
}
