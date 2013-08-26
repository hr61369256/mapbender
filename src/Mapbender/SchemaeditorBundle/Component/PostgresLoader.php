<?php

namespace Mapbender\SchemaeditorBundle\Component;

class PostgresLoader implements DatabaseLoaderInterface {
    private $conn = null;

    /**
     * Constructor
     * @param array $config configuration
     */
    public function __construct($config) {
        $this->conn = pg_connect(
            "host="         . $config['host'] . 
            " port="        . $config['port'] . 
            " dbname="      . $config['name'] . 
            " user="        . $config['user'] . 
            " password="    . $config['pass']
        ) or die ("Postgres error : " . pg_last_error($this->conn));
    }

    /**
     * Destructor
     */
    public function __destruct() {
        pg_close($this->conn);
    }

    /**
     * Returns geometry tables.
     * @return array geometry tables
     */
    public function getGeomTables() {
        $result = pg_query($this->conn, 'SELECT f_table_name FROM geometry_columns GROUP BY f_table_name ORDER BY f_table_name;');
        return pg_fetch_all_columns($result);
    }

    /**
     * Returns columns by tablename.
     * @param  string $table tablename
     * @return array table columns
     */
    public function getColumnsByTable($table) {
        // $result = pg_query_params($this->conn, "SELECT column_name FROM information_schema.columns WHERE table_name = $1;", array($table));
        $fields = array();
        $result = pg_query($this->conn, "SELECT * FROM " . pg_escape_string($table) . " LIMIT 1;");

        $pkey = $this->getPkeyByTable($table);

        for($i=0, $iL=pg_num_fields($result);$i<$iL;$i++) {
            $row = array(
                'name' => pg_field_name($result, $i),
                'size' => pg_field_size($result, $i),
                'type' => pg_field_type($result, $i),
                'len' => pg_field_prtlen($result, $i)
            );

            if($row['name'] === $pkey)
                $row['primary'] = true;

            $fields[] = $row;
        }

        return $fields;
    }

    /**
     * Returns primary key fieldname by table
     * @param  string $table table name
     * @return string        first primary key field name
     */
    private function getPkeyByTable($table) {
        $result = pg_query($this->conn, "SELECT
            c.column_name
            FROM
            information_schema.table_constraints tc 
            JOIN information_schema.constraint_column_usage AS ccu USING (constraint_schema, constraint_name) 
            JOIN information_schema.columns AS c ON c.table_schema = tc.constraint_schema AND tc.table_name = c.table_name AND ccu.column_name = c.column_name
            where constraint_type = 'PRIMARY KEY' and tc.table_name = '" . pg_escape_string($table) . "';");

        if($result) {
            $row = pg_fetch_row($result);
            return $row[0];
        }

        return false;
    }
}