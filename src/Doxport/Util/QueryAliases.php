<?php

namespace Doxport\Util;

class QueryAliases
{
    /**
     * Table alias map
     *
     * @var array<string => string>
     */
    protected $aliases = [];

    /**
     * @return string
     */
    protected function getClass($name)
    {
        $parts = explode('\\', $name);
        return $parts[count($parts) - 1];
    }

    /**
     * @param string $name
     * @return string
     */
    public function get($name)
    {
        if (!isset($this->aliases[$name])) {
            $char   = strtolower(strrchr($name, '\\')[1]);
            $simple = $char;
            $index  = 1;

            while (in_array($simple, $this->aliases)) {
                $simple = $char . (++$index);
            }

            $this->aliases[$name] = $simple;
        }

        return $this->aliases[$name];
    }
}
