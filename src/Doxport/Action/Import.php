<?php

namespace Doxport\Action;

use Doxport\Action\Base\Action;
use Doxport\Util\EntityArrayHelper;
use LogicException;

class Import extends Action
{
    /**
     * @var string
     */
    protected $constraintPath;

    /**
     * @var string
     */
    protected $constraints = [];

    /**
     * @return void
     */
    public function run()
    {
        $this->validate();
        $this->parseConstraints();

        foreach ($this->constraints as $constraint) {
            $class = $this->getClassName($constraint);
            $file = $this->fileFactory->getFile($class);
            $this->process($constraint, $file->readObjects());
        }
    }

    protected function process($entityName, $entities)
    {
        $this->logger->notice('Processing import of {entityName}', ['entityName' => $entityName]);

        $helper = new EntityArrayHelper($this->em);

        foreach ($entities as $values) {
            $entity = $helper->toEntity($entityName, $values);

            // Save entity
            $this->em->merge($entity);
        }

        $this->em->close();
    }

    /**
     * @throws LogicException
     * @return void
     */
    protected function validate()
    {
        if (!is_dir($this->fileFactory->getPath())) {
            throw new LogicException('Cannot find data directory: ' . $this->fileFactory->getPath());
        }

        $this->constraintPath = $this->fileFactory->getPathForFile('constraints', 'txt');

        if (!is_readable($this->constraintPath)) {
            throw new LogicException('Unreadable constraints.txt in data directory - invalid data dir?');
        }
    }

    /**
     * Parses the constraints file to find the order to import data
     */
    protected function parseConstraints()
    {
        $contents = file_get_contents($this->constraintPath);
        $contents = explode(PHP_EOL, $contents);
        $this->constraints = array_reverse($contents);
    }

}
