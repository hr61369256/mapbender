<?php
namespace Mapbender\CoreBundle\Element;

use Mapbender\CoreBundle\Component\Element;
use Symfony\Component\HttpFoundation\Response;

/**
 * Map element.
 *
 * @author Christian Wygoda
 */
class Map extends Element
{

    /**
     * @inheritdoc
     */
    static public function getClassTitle()
    {
        return "mb.core.map.class.title";
    }

    /**
     * @inheritdoc
     */
    static public function getClassDescription()
    {
        return "mb.core.mapabs.class.description";
    }

    /**
     * @inheritdoc
     */
    static public function getClassTags()
    {
        return array(
            "mb.core.map.tag.map",
            "mb.core.map.tag.mapquery",
            "mb.core.map.tag.openlayers");
    }

    /**
     * @inheritdoc
     */
    public static function getDefaultConfiguration()
    {
        return array(
            'layerset' => null,
            'dpi' => 72,
            'srs' => 'EPSG:4326',
            'otherSrs' => array("EPSG:31466", "EPSG:31467"),
            'units' => 'degrees',
            'extents' => array(
                'max' => array(0, 40, 20, 60),
                'start' => array(5, 45, 15, 55)),
            'maxResolution' => 'auto',
            "scales" => array(25000000, 10000000, 5000000, 1000000, 500000),
            'imgPath' => 'bundles/mapbendercore/mapquery/lib/openlayers/img');
    }

    /**
     * @inheritdoc
     */
    public function getWidgetName()
    {
        return 'mapbender.mbMap';
    }

    /**
     * @inheritdoc
     */
    public function getAssets()
    {
        return array(
            'js' => array(
                'mapquery/lib/openlayers/OpenLayers.js',
                'mapquery/lib/jquery/jquery.tmpl.js',
                'mapquery/src/jquery.mapquery.core.js',
                'proj4js/proj4js-compressed.js',
                'mapbender.element.map.js'),
            'css' => array());
    }

    /**
     * @inheritdoc
     */
    public function getConfiguration()
    {
        $configuration = parent::getConfiguration();

        if (isset($configuration["scales"])) {
            $scales = array();
            if (is_string($configuration["scales"])) { // from database
                $scales = preg_split("/\s?[\,\;]\s?/", $configuration["scales"]);
            } else if (is_array($configuration["scales"])) { // from twig
                $scales = $configuration["scales"];
            }
            // sort scales high to low
            $scales = array_map(
                create_function('$value', 'return (int)$value;'), $scales);
            arsort($scales, SORT_NUMERIC);
            $configuration["scales"] = $scales;
        }

        $extra = array();

        // @TODO: Move into DataTransformer of MapAdminType
        $configuration = array_merge(array('extra' => $extra), $configuration);
        $allsrs = array();
        if (is_int(stripos($configuration["srs"], "|"))) {
            $srsHlp = preg_split("/\s?\|{1}\s?/", $configuration["srs"]);
            $configuration["srs"] = trim($srsHlp[0]);
            $allsrs[] = array(
                "name" => trim($srsHlp[0]),
                "title" => strlen(trim($srsHlp[1])) > 0 ? trim($srsHlp[1]) : '');
        } else {
            $configuration["srs"] = trim($configuration["srs"]);
            $allsrs[] = array(
                "name" => $configuration["srs"],
                "title" => '');
        }

        if (isset($configuration["otherSrs"])) {
            if (is_array($configuration["otherSrs"])) {
                $otherSrs = $configuration["otherSrs"];
            } else if (is_string($configuration["otherSrs"]) && strlen(trim($configuration["otherSrs"]))
                > 0) {
                $otherSrs = preg_split("/\s?,\s?/", $configuration["otherSrs"]);
            }
            foreach ($otherSrs as $srs) {
                if (is_int(stripos($srs, "|"))) {
                    $srsHlp = preg_split("/\s?\|{1}\s?/", $srs);
                    $allsrs[] = array(
                        "name" => trim($srsHlp[0]),
                        "title" => strlen(trim($srsHlp[1])) > 0 ? trim($srsHlp[1])
                                : '');
                } else {
                    $allsrs[] = array(
                        "name" => $srs,
                        "title" => '');
                }
            }
        }

        $configuration["srsDefs"] = $this->getSrsDefinitions($allsrs);
        $srs_req = $this->container->get('request')->get('srs');
        if ($srs_req) {
            if (!isset($allsrs[$srs])) {
                throw new \RuntimeException('The srs: "' . $srs_req
                . '" does not supported.');
            }
            $configuration = array_merge($configuration,
                array('targetsrs' => $srs_req));
        }

        $pois = $this->container->get('request')->get('poi');
        if ($pois) {
            $extra['pois'] = array();
            if (array_key_exists('point', $pois)) {
                $pois = array($pois);
            }
            foreach ($pois as $poi) {
                $point = explode(',', $poi['point']);
                $extra['pois'][] = array(
                    'x' => floatval($point[0]),
                    'y' => floatval($point[1]),
                    'label' => isset($poi['label']) ? $poi['label'] : null,
                    'scale' => isset($poi['scale']) ? intval($poi['scale']) : null
                );
            }
        }

        $bbox = $this->container->get('request')->get('bbox');
        if (!isset($extra['pois']) && $bbox) {
            $bbox = explode(',', $bbox);
            if (count($bbox) === 4) {
                $extra['bbox'] = array(
                    floatval($bbox[0]),
                    floatval($bbox[1]),
                    floatval($bbox[2]),
                    floatval($bbox[3])
                );
            }
        }

        $configuration['extra'] = $extra;

        if (!isset($configuration['scales'])) {
            throw new \RuntimeException('The scales does not defined.');
        } else if (is_string($configuration['scales'])) {
            $configuration['scales'] = preg_split(
                "/\s?,\s?/", $configuration['scales']);
        }
        return $configuration;
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        return $this->container->get('templating')
                ->render('MapbenderCoreBundle:Element:map.html.twig',
                    array(
                    'id' => $this->getId()));
    }

    /**
     * @inheritdoc
     */
    public static function getType()
    {
        return 'Mapbender\CoreBundle\Element\Type\MapAdminType';
    }

    /**
     * @inheritdoc
     */
    public static function getFormTemplate()
    {
        return 'MapbenderManagerBundle:Element:map.html.twig';
    }

    public function httpAction($action)
    {
        $session = $this->container->get("session");

        if ($session->get("proxyAllowed", false) !== true) {
            throw new AccessDeniedHttpException('You are not allowed to use this proxy without a session.');
        }
        switch ($action) {
            case 'loadsrs':
                $srsList = $this->container->get('request')->get("srs", null);
                return $this->loadSrsDefinitions($srsList);
                break;
            default:
                throw new NotFoundHttpException('No such action');
        }
    }

    protected function loadSrsDefinitions($srsList)
    {
        $srses = preg_split("/\s?,\s?/", $srsList);
        $allsrs = array();
        foreach ($srses as $srs) {
            if (is_int(stripos($srs, "|"))) {
                $srsHlp = preg_split("/\s?\|{1}\s?/", $srs);
                $allsrs[] = array(
                    "name" => trim($srsHlp[0]),
                    "title" => strlen(trim($srsHlp[1])) > 0 ? trim($srsHlp[1]) : '');
            } else {
                $allsrs[] = array(
                    "name" => trim($srs),
                    "title" => '');
            }
        }
        $result = $this->getSrsDefinitions($allsrs);
        if (count($result) > 0) {
            return new Response(json_encode(
                    array("data" => $result)), 200,
                array('Content-Type' => 'application/json'));
        } else {
            return new Response(json_encode(
                    array("error" => $this->trans("mb.core.map.srsnotfound",
                            array('%srslist%', $srsList)))), 200,
                array('Content-Type' => 'application/json'));
        }
    }

    protected function getSrsDefinitions(array $srsNames)
    {
        $result = array();
        if (is_array($srsNames) && count($srsNames) > 0) {
            $names = array();
            foreach ($srsNames as $srsName) {
                $names[] = $srsName['name'];
            }
            $em = $this->container->get("doctrine")->getEntityManager();
            $query = $em->createQuery("SELECT srs FROM MapbenderCoreBundle:SRS srs"
                    . " Where srs.name IN (:name)  ORDER BY srs.id ASC")
                ->setParameter('name', $names);
            $srses = $query->getResult();
            foreach ($srsNames as $srsName) {
                foreach ($srses as $srs) {
                    if ($srsName['name'] === $srs->getName()) {
                        $result[] = array(
                            "name" => $srs->getName(),
                            "title" => strlen($srsName["title"]) > 0 ? $srsName["title"]
                                    : $srs->getTitle(),
                            "definition" => $srs->getDefinition());
                        break;
                    }
                }
            }
        }
        return $result;
    }

}
