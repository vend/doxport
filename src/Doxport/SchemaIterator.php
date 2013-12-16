<?php

namespace Doxport;

class SchemaIterator implements RecursiveIterator
{
    protected $schema;

    /**
     * @param Schema[] $schema
     */
    public function __construct(array $schema)
    {
        $this->schema = $schema;
    }
}
