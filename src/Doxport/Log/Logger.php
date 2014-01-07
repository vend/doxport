<?php

namespace Doxport\Log;

use Psr\Log\AbstractLogger;

abstract class Logger extends AbstractLogger
{
    /**
     * @param string $message
     * @param array  $context
     * @return string
     */
    protected function interpolate($message, array $context = [])
    {
        $replace = [];

        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }

        return strtr($message, $replace);
    }
}
