<?php

namespace Doxport\Action\Base;

use Doxport\File\Factory;
use Fhaculty\Graph\Walk;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

abstract class Action implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var Factory
     */
    protected $fileFactory;

    /**
     * @param Walk   $fromTargetToRoot
     * @return void
     */
    abstract public function process(Walk $fromTargetToRoot);

    /**
     * @param Walk  $fromTargetToRoot
     * @param array $associationToAndFromTarget
     * @return void
     */
    abstract public function processSelfJoin(Walk $fromTargetToRoot, array $associationToAndFromTarget);

    /**
     * @param string $class
     * @return mixed
     */
    protected function getClassName($class)
    {
        $parts = explode('\\', $class);
        return $parts[count($parts) - 1];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return lcfirst($this->getClassName(get_class($this)));
    }

    /**
     * @param Factory $fileFactory
     */
    public function setFileFactory(Factory $fileFactory)
    {
        $this->fileFactory = $fileFactory;
    }
}
