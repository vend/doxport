<?php

namespace Doxport\Action;

use Doxport\Criteria;
use Doxport\Util\AsyncFile;

abstract class FileAction extends Action
{
    /**
     * @var AsyncFile
     */
    protected $file;

    /**
     * @param Criteria $criteria
     * @return string
     */
    abstract protected function getFilePath(Criteria $criteria);

    /**
     * @param Criteria $criteria
     * @return string
     */
    protected function getFileMode(Criteria $criteria)
    {
        return 'a';
    }

    /**
     * @param Criteria $criteria
     * @return void
     */
    abstract protected function doProcess(Criteria $criteria);

    /**
     * @param Criteria $criteria
     * @return mixed
     */
    protected function process(Criteria $criteria)
    {
        $this->file = new AsyncFile(
            $this->getFilePath($criteria),
            $this->getFileMode($criteria)
        );

        $return = $this->doProcess($criteria);

        $this->file->close();
        unset($this->file);

        return $return;
    }
}
