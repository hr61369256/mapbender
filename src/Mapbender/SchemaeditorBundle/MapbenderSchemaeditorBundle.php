<?php

namespace Mapbender\SchemaeditorBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Mapbender\CoreBundle\Component\MapbenderBundle;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

class MapbenderSchemaeditorBundle extends MapbenderBundle
{
    public function getManagerControllers()
    {
        return array(
            array(
                'weight' => 40,
                'title' => 'Schemaconf',
                'route' => 'mapbender_schemaeditor_schema_index',
                'routes' => array(
                    'mapbender_schemaeditor_schema_index',
                    'mapbender_schemaeditor_schema_new',
                ),
                'subroutes' => array(
                    array('title'=>'New Schema',
                          'route'=>'mapbender_schemaeditor_schema_new',
                          'enabled' => function($securityContext) {
                                $oid = new ObjectIdentity('class', 'Mapbender\CoreBundle\Entity\Application');
                                return $securityContext->isGranted('CREATE', $oid);
                          })
                )
            )
        );
    }

    public function getRoles()
    {
        return array(
            'ROLE_ADMIN_MAPBENDER_APPLICATION'
                => 'Can administrate applications');
    }
}
