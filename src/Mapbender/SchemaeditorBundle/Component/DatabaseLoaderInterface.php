<?php

namespace Mapbender\SchemaeditorBundle\Component;

interface DatabaseLoaderInterface {
    /**
     * Constructor
     * @param array $config configuration
     */
    public function __construct($config);

    /**
     * Returns geometry tables.
     * @return array geometry tables
     */
    public function getGeomTables();

    /**
     * Returns columns by tablename.
     * @param  string $table tablename
     * @return array table columns
     */
    public function getColumnsByTable($table);
}