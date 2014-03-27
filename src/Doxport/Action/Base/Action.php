<?php

namespace Doxport\Action\Base;

use Doctrine\ORM\EntityManager;
use Doxport\File\Factory;
use Doxport\Metadata\Driver;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

abstract class Action implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Driver
     */
    protected $driver;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var Factory
     */
    protected $fileFactory;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

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

    /**
     * @param Driver $driver
     */
    public function setMetadataDriver(Driver $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Outputs memory information to the logger
     *
     * @return void
     */
    protected function debugMemory()
    {
        $a = [
            memory_get_usage(),
            memory_get_peak_usage(),
            round(100 * memory_get_usage() / memory_get_peak_usage()),
            round(100 * memory_get_peak_usage() / 160000000),
        ];

        $s = '';
        foreach ($a as $v) {
            $s .= str_pad(round($v), 15, ' ', STR_PAD_LEFT);
        }

        $this->logger->notice('Memory: ' . $s);
    }
}
