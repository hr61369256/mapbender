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
class SchemaController extends Controller {
    /**
     *
     * @ManagerRoute("/")
     * @Method("GET")
     * @Template("MapbenderSchemaeditorBundle:Schema:index.html.twig")
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('MapbenderSchemaeditorBundle:Schemaconfig');
        $schemes = $repository->findAll();
        return array('schemes' => $schemes);
    }

    /**
     *
     * @ManagerRoute("/new")
     * @Method("GET")
     * @Template("MapbenderSchemaeditorBundle:Schema:new.html.twig")
     */
    public function newAction() {
        return array();
    }

    /**
     *
     * @ManagerRoute("/new")
     * @Method("POST")
     * @Template("MapbenderSchemaeditorBundle:Schema:new.html.twig")
     */
    public function createAction() {
        $params = $this->get('request')->request->get('schemaeditor');    
        $config = array('type' => null);
        $database = null;

        if(isset($params['url'])) {        
            $config = $this->parseURL($params['url']);
            
            if($config) {
                $database = $this->getDatabase($config);
            }
        }

        if(is_object($database)) {
            $tables = $database->getGeomTables();
        }

        return array(
            'url' => isset($params['url']) ? $params['url'] : '',
            'tables' => !empty($tables) ? $tables : null
        );
    }
    
    /**
     *
     * @ManagerRoute("/edit")
     * @Method({"POST"})
     * @Template("MapbenderSchemaeditorBundle:Schema:insert.html.twig")
     */
    public function editAction() {
        $params = $this->get('request')->request->get('schemaeditor');   
        
        if(isset($params['url'])) {
            $config = $this->parseURL($params['url']);
            
            if($config) {
                $database = $this->getDatabase($config);
            }
        }
          
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
            }
    

        return array(
            'url' => isset($params['url']) ? $params['url'] : '',
            'columns' => !empty($columnNames) ? substr($columnNames, 2) : null,
            'yml' => !empty($yml) ? trim($yml) : @$params['yml'],
        );
    }
    
    /**
     *
     * @ManagerRoute("/edit")
     * @Method({"GET"})
     * @Template("MapbenderSchemaeditorBundle:Schema:update.html.twig")
     */
    public function edit2Action() {
        $schemaId = $this->get('request')->get('schemaId', null);       

        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('MapbenderSchemaeditorBundle:Schemaconfig');
        $schemaconfig = $repository->find($schemaId);

        $params['yml'] = $schemaconfig->getYml();
        $params['description'] = $schemaconfig->getDescription();
        

        return array(
            'yml' => $params['yml'],
            'description' => $params['description'],
            'id' => $schemaId
        );
    }
    
    /**
     *
     * @ManagerRoute("/insert")
     * @Method({"POST"})
     */
    public function insertAction() {
        $params = $this->get('request')->request->get('schemaeditor');
        
        $em = $this->getDoctrine()->getEntityManager();

        $schemaconfig = new Schemaconfig();

        $schemaconfig->setDescription(trim($params['description']));
        $schemaconfig->setYml(trim($params['yml']));

        $em->persist($schemaconfig);
        $em->flush();

        return $this->redirect($this->generateUrl('mapbender_schemaeditor_schema_index'));
    }
    
     /**
     *
     * @ManagerRoute("/update")
     * @Method({"POST"})
     */
    public function updateAction() {
        $params = $this->get('request')->request->get('schemaeditor');
        
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('MapbenderSchemaeditorBundle:Schemaconfig');
        $schemaconfig = $repository->find($params['id']);

        $schemaconfig->setDescription(trim($params['description']));
        $schemaconfig->setYml(trim($params['yml']));

        $em->flush();

        return $this->redirect($this->generateUrl('mapbender_schemaeditor_schema_index'));
    }
    
    /**
     *
     * @ManagerRoute("/delete/{schemaId}")
     * @Method({"POST"})
     */
    public function deleteAction($schemaId)
    {
        
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('MapbenderSchemaeditorBundle:Schemaconfig');
        $schema = $repository->find($schemaId);
        
        $em->remove($schema);
        $em->flush();

        $this->get('session')->setFlash('success', "Your schema has been deleted");
        return $this->redirect($this->generateUrl("mapbender_schemaeditor_schema_index"));
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
    
    public function parseURL($url) {
        preg_match('/([^:]+):\/\/([^\:]+):([^@]+)@([^:]+):([^\/]+)\/(.+)/', $url, $matches);

        if(count($matches) === 7) {
            return array(
                'type' => $matches[1],
                'user' => $matches[2],
                'pass' => $matches[3],
                'host' => $matches[4],
                'port' => $matches[5],
                'name' => $matches[6],
            );
        }
        
        return false;
    }
    
    public function getDatabase($config) {
        $database = null;
        
        switch($config['type']) {
            case 'postgresql' :
                $database = new PostgresLoader($config);
                break; 
        }
        
        return $database;
    }
}