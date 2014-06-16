<?php

namespace Doxport\Console;

use Doxport\Exception\Exception;
use Doxport\Version;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;

class ConsoleRunner
{
    /**
     * Runs the application using the given HelperSet
     *
     * This method is responsible for creating the console application, adding
     * relevant commands, and running it. Other code is responsible for producing
     * the HelperSet itself (your cli-config.php or bootstrap code), and for
     * calling this method (the actual bin command file).
     *
     * @param HelperSet $helperSet
     * @param Application $application
     * @throws \Doxport\Exception\Exception
     * @return integer 0 if everything went fine, or an error code
     */
    public static function run(HelperSet $helperSet, Application $application = null)
    {
        if (!$helperSet->has('em')) {
            throw new Exception('Helper set passed to Doxport console runner must have an entity manager ("em")');
        }

        if (!$application) {
            $application = new Application('Doxport relational data tool', Version::VERSION);
        }

        $application->setCatchExceptions(true);
        $application->setHelperSet($helperSet);

        $application->add(new ExportCommand());
        $application->add(new DeleteCommand());
        $application->add(new ImportCommand());

        return $application->run();
    }
}
