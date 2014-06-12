<?php

namespace Doxport\Action\Base;

use Doctrine\ORM\EntityManager;
use Doxport\File\Factory;
use Doxport\Metadata\Driver;
use Doxport\Util\Chunk;
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
     * @var Chunk
     */
    protected $chunk;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;

        // 500 rows initial estimate and max, target perform in 0.2 seconds
        $this->chunk = new Chunk(500, 0.2, ['max' => 500, 'min' => 5]);
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
     * @param Chunk $chunk
     */
    public function setChunk(Chunk $chunk)
    {
        $this->chunk = $chunk;
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
