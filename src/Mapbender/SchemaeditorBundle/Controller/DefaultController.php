<?php

/**
 * Mapbender schema management
 *
 * @author A.R.P & S.W
 */

namespace Mapbender\SchemaeditorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOM\ManagerBundle\Configuration\Route as ManagerRoute;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;




use Mapbender\SchemaeditorBundle\Component\PostgresLoader;
use Mapbender\SchemaeditorBundle\Entity\Schemaconfig;
/**
 * @ManagerRoute("/schemaeditor")
 */
class DefaultController extends Controller {
    /**
     *
     * @ManagerRoute("/")
     * @Method("GET")
     * @Template
     */
    public function indexAction() {
        return array();
    }

    /**
     *
     * @ManagerRoute("/new")
     * @Method("GET")
     * @Template
     */
    public function newAction() {
        return array();
    }

    /**
     *
     * @ManagerRoute("/new")
     * @Method("POST")
     * @Template("MapbenderSchemaeditorBundle:Default:new.html.twig")
     */
    public function createAction() {
        $params = $this->get('request')->request->get('schemaeditor');
        $config = array('type' => null);
        $database = null;

        if(isset($params['yml'])) {
            // $yaml = new Parser();
            // $value = $yaml->parse(trim($params['yml']));

            // $dumper = new Dumper();
            // $yaml = $dumper->dump($value);
            // 
            $schemaconfig = new Schemaconfig();
            $schemaconfig->setDescription(trim($params['description']));
            $schemaconfig->setYml(trim($params['yml']));

            // DATABASE FLUSH BLA BLA
            die('<pre>' . $params['description']. '</pre>');
        }
        else if(isset($params['url'])) {
            preg_match('/([^:]+):\/\/([^\:]+):([^@]+)@([^:]+):([^\/]+)\/(.+)/', $params['url'], $matches);

            if(count($matches) === 7) {
                $config = array(
                    'type' => $matches[1],
                    'user' => $matches[2],
                    'pass' => $matches[3],
                    'host' => $matches[4],
                    'port' => $matches[5],
                    'name' => $matches[6],
                );
            }

            switch($config['type']) {
                case 'postgresql' :
                    $database = new PostgresLoader($config);
                    break; 
            }
        }

        if(is_object($database)) {
            if(isset($params['table'])) {
                $columns = $database->getColumnsByTable($params['table']);
                $primary = ""; $geom = ""; $attributes = ""; $columnNames = "";

                foreach($columns as $column) {
                    if(isset($column['primary']) && $column['primary'] == true)
                        $primary = $column['name'];
                    elseif($column['type'] == 'geometry')
                        $geom = $column['name'];

                    $columnNames .= ', ' . $column['name'];

                    $attributes .= "
    - name: ".$column['name']."
      label: ".ucfirst($column['name'])."
      widget: ". $this->parseType($column['type'])."";
                }

                $yml = "schemaid:
  datasource:
    type: ".$config['type']."
    table: ".$params['table']."
  geom: ".$geom."
  srid: 31467
  id: ".$primary."
  attributes: " . $attributes;

            } elseif(isset($params['url'])) {
                $tables = $database->getGeomTables();
            }
        }

        return array(
            'url' => isset($params['url']) ? $params['url'] : '',
            'tables' => !empty($tables) ? $tables : null,
            'columns' => !empty($columnNames) ? substr($columnNames, 2) : null,
            'yml' => !empty($yml) ? trim($yml) : null,
        );
    }

    public function parseType($type) {
        $type = strtolower($type);

        if(substr($type,0,3) == 'int')
            return 'integer';
        elseif(substr($type,0,5) == 'float')
            return 'float';
        elseif(substr($type,0,7) == 'varchar')
            return 'text';
        elseif(substr($type,0,7) == 'geometry')
            return 'geometry';

        return $type;
    }
}