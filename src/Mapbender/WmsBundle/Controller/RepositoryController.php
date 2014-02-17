<?php
namespace Mapbender\WmsBundle\Controller;

use FOM\ManagerBundle\Configuration\Route as ManagerRoute;
use Mapbender\CoreBundle\Component\Utils;
use Mapbender\CoreBundle\Component\Exception\NotSupportedVersionException;
use Mapbender\WmsBundle\Component\Exception\WmsException;
use Mapbender\CoreBundle\Component\Exception\XmlParseException;
use Mapbender\WmsBundle\Component\WmsCapabilitiesParser;
use Mapbender\WmsBundle\Entity\WmsInstance;
use Mapbender\WmsBundle\Entity\WmsInstanceLayer;
use Mapbender\WmsBundle\Entity\WmsLayerSource;
use Mapbender\WmsBundle\Entity\WmsSource;
use Mapbender\WmsBundle\Form\Type\WmsInstanceInstanceLayersType;
use Mapbender\WmsBundle\Form\Type\WmsSourceSimpleType;
use Mapbender\WmsBundle\Form\Type\WmsSourceType;
use Mapbender\WmsBundle\Form\Type\WmsInstanceType;
use OwsProxy3\CoreBundle\Component\ProxyQuery;
use OwsProxy3\CoreBundle\Component\CommonProxy;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @ManagerRoute("/repository/wms")
 *
 * @author Christian Wygoda
 */
class RepositoryController extends Controller
{

    /**
     * @ManagerRoute("/new")
     * @Method({ "GET" })
     * @Template
     */
    public function newAction()
    {
        $form = $this->get("form.factory")->create(new WmsSourceSimpleType(),
            new WmsSource());
        return array(
            "form" => $form->createView()
        );
    }

    /**
     * @ManagerRoute("/start")
     * @Method({ "GET" })
     * @Template("MapbenderWmsBundle:Repository:form.html.twig")
     */
    public function startAction()
    {
        $form = $this->get("form.factory")->create(new WmsSourceSimpleType(),
            new WmsSource());
        return array(
            "form" => $form->createView()
        );
    }

    /**
     * @ManagerRoute("{wms}")
     * @Method({ "GET"})
     * @Template
     */
    public function viewAction(WmsSource $wms)
    {
        if (!$this->get('security.context')->isGranted('OWNER', $wms)) {
            throw new AccessDeniedException();
        }
        return array("wms" => $wms);
    }

    /**
     * @ManagerRoute("/create")
     * @Method({ "POST" })
     * @Template("MapbenderWmsBundle:Repository:new.html.twig")
     */
    public function createAction()
    {
        $request = $this->get('request');
        $wmssource_req = new WmsSource();

        $securityContext = $this->get('security.context');
        $oid = new ObjectIdentity('class', 'Mapbender\CoreBundle\Entity\Source');
        if (false === $securityContext->isGranted('CREATE', $oid)) {
            throw new AccessDeniedException();
        }

        $form = $this->get("form.factory")->create(new WmsSourceSimpleType(),
            $wmssource_req);
        $form->bindRequest($request);
        if ($form->isValid()) {
            $purl = parse_url($wmssource_req->getOriginUrl());
            if (!isset($purl['scheme']) || !isset($purl['host'])) {
                $this->get("logger")->debug("The url is not valid.");
                $this->get('session')->setFlash('error', "The url is not valid.");
                return $this->redirect($this->generateUrl(
                            "mapbender_manager_repository_new", array(), true));
            }
            $proxy_config = $this->container->getParameter("owsproxy.proxy");
            $proxy_query = ProxyQuery::createFromUrl(trim($wmssource_req->getOriginUrl()),
                    $wmssource_req->getUsername(), $wmssource_req->getPassword());
            if ($proxy_query->getGetPostParamValue("request", true) === null) {
                $proxy_query->addQueryParameter("request", "GetCapabilities");
            }
            if ($proxy_query->getGetPostParamValue("service", true) === null) {
                $proxy_query->addQueryParameter("service", "WMS");
            }
            $wmssource_req->setOriginUrl($proxy_query->getGetUrl());
            $proxy = new CommonProxy($proxy_config, $proxy_query);

            $wmssource = null;
            try {
                $browserResponse = $proxy->handle();
                $content = $browserResponse->getContent();
                $doc = WmsCapabilitiesParser::createDocument($content);
                $wmsParser = WmsCapabilitiesParser::getParser($doc);
                $wmssource = $wmsParser->parse();
            } catch (\Exception $e) {
                $this->get("logger")->debug($e->getMessage());
                $this->get('session')->setFlash('error', $e->getMessage());
                return $this->redirect($this->generateUrl(
                            "mapbender_manager_repository_new", array(), true));
            }

            if (!$wmssource) {
                $this->get("logger")->debug('Could not parse data for url "'
                    . $wmssource_req->getOriginUrl() . '"');
                $this->get('session')->setFlash('error',
                    'Could not parse data for url "'
                    . $wmssource_req->getOriginUrl() . '"');
                return $this->redirect($this->generateUrl(
                            "mapbender_manager_repository_new", array(), true));
            }
            $wmsWithSameTitle = $this->getDoctrine()
                ->getEntityManager()
                ->getRepository("MapbenderWmsBundle:WmsSource")
                ->findByTitle($wmssource->getTitle());

            if (count($wmsWithSameTitle) > 0) {
                $wmssource->setAlias(count($wmsWithSameTitle));
            }

            $wmssource->setOriginUrl($wmssource_req->getOriginUrl());
            $rootlayer = $wmssource->getLayers()->get(0);
            $this->getDoctrine()->getEntityManager()->persist($rootlayer);
            $this->saveLayer($this->getDoctrine()->getEntityManager(),
                $rootlayer);
            $this->getDoctrine()->getEntityManager()->persist($wmssource);
            $this->getDoctrine()->getEntityManager()->flush();

            // ACL
            $aclProvider = $this->get('security.acl.provider');
            $objectIdentity = ObjectIdentity::fromDomainObject($wmssource);
            $acl = $aclProvider->createAcl($objectIdentity);

            $securityContext = $this->get('security.context');
            $user = $securityContext->getToken()->getUser();
            $securityIdentity = UserSecurityIdentity::fromAccount($user);

            $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
            $aclProvider->updateAcl($acl);

            $this->get('session')->setFlash('success',
                "Your WMS has been created");
            return $this->redirect($this->generateUrl(
                        "mapbender_manager_repository_view",
                        array(
                        "sourceId" => $wmssource->getId()), true));
        }

        return array(
            'form' => $form->createView(),
            'form_name' => $form->getName());
    }

    private function saveLayer($em, $layer)
    {
        foreach ($layer->getSublayer() as $sublayer) {
            $em->persist($sublayer);
            $this->saveLayer($em, $sublayer);
        }
    }

    /**
     * Removes a WmsSource
     *
     * @ManagerRoute("/{sourceId}/delete")
     * @Method({"GET"})
     */
    public function deleteAction($sourceId)
    {
        $wmssource = $this->getDoctrine()
            ->getRepository("MapbenderWmsBundle:WmsSource")
            ->find($sourceId);
        $wmsinstances = $this->getDoctrine()
            ->getRepository("MapbenderWmsBundle:WmsInstance")
            ->findBySource($sourceId);
        $em = $this->getDoctrine()->getEntityManager();
        $em->getConnection()->beginTransaction();

        $aclProvider = $this->get('security.acl.provider');
        $oid = ObjectIdentity::fromDomainObject($wmssource);
        $aclProvider->deleteAcl($oid);

        foreach ($wmsinstances as $wmsinstance) {
            $wmsinstance->remove($em);
            $em->flush();
        }
        $wmssource->remove($em);
        $em->flush();
        $em->getConnection()->commit();
        $this->get('session')->setFlash('success', "Your WMS has been deleted");
        return $this->redirect($this->generateUrl("mapbender_manager_repository_index"));
    }

    /**
     * Removes a WmsInstance
     *
     * @ManagerRoute("/{slug}/instance/{instanceId}/delete")
     * @Method({"GET"})
     */
    public function deleteInstanceAction($slug, $instanceId)
    {
        $instance = $this->getDoctrine()
            ->getRepository("MapbenderCoreBundle:SourceInstance")
            ->find($instanceId);
        $em = $this->getDoctrine()->getEntityManager();
        $em->getConnection()->beginTransaction();
        $instance->remove($em);
        $em->flush();
        $em->getConnection()->commit();
        $this->get('session')->setFlash('success',
            'Your source instance has been deleted.');
        return new Response();
    }

    /**
     * Edits, saves the WmsInstance
     *
     * @ManagerRoute("/instance/{slug}/{instanceId}")
     * @Template("MapbenderWmsBundle:Repository:instance.html.twig")
     */
    public function instanceAction($slug, $instanceId)
    {
        $wmsinstance = $this->getDoctrine()
            ->getRepository("MapbenderWmsBundle:WmsInstance")
            ->find($instanceId);

        if ($this->getRequest()->getMethod() == 'POST') { //save
            $wmsinstance_req = new WmsInstance();
            $wmsinstance_req->setSource($wmsinstance->getSource());
            $form_req = $this->createForm(
                new WmsInstanceInstanceLayersType(), $wmsinstance_req);
            $form_req->bindRequest($this->get('request'));
            $form = $this->createForm(
                new WmsInstanceInstanceLayersType(), $wmsinstance);
            $form->bindRequest($this->get('request'));
            $wmsinstance->setTransparency(
                Utils::getBool($wmsinstance_req->getTransparency()));
            $wmsinstance->setVisible(
                Utils::getBool($wmsinstance_req->getVisible()));
            $wmsinstance->setOpacity(
                Utils::getBool($wmsinstance_req->getOpacity()));
            $wmsinstance->setProxy(
                Utils::getBool($wmsinstance_req->getProxy()));
            $wmsinstance->setTiled(
                Utils::getBool($wmsinstance_req->getTiled()));
            foreach ($wmsinstance->getLayers() as $layer) {
                foreach ($wmsinstance_req->getLayers() as $layer_tmp) {
                    if ($layer_tmp->getId() === $layer->getId()) {
                        $layer->setActive(Utils::getBool(
                                $layer_tmp->getActive()));
                        $layer->setSelected(Utils::getBool(
                                $layer_tmp->getSelected()));
                        $layer->setSelectedDefault(Utils::getBool(
                                $layer_tmp->getSelectedDefault()));
                        $layer->setInfo(Utils::getBool(
                                $layer_tmp->getInfo(), true));
                        $layer->setAllowinfo(Utils::getBool(
                                $layer_tmp->getAllowinfo(), true));
                        break;
                    }
                }
            }
            if ($form->isValid()) { //save
                $em = $this->getDoctrine()->getEntityManager();
                $em->getConnection()->beginTransaction();
                $wmsinstance->generateConfiguration();
                $em->persist($wmsinstance);
                $em->flush();

                $em->getConnection()->commit();

                $this->get('session')->setFlash(
                    'success', 'Your Wms Instance has been changed.');
                return $this->redirect($this->generateUrl(
                            'mapbender_manager_application_edit',
                            array("slug" => $slug)) . '#layersets');
            } else { // edit
                return array(
                    "form" => $form->createView(),
                    "slug" => $slug,
                    "instance" => $wmsinstance);
            }
        } else { // edit
            $form = $this->createForm(
                new WmsInstanceInstanceLayersType(), $wmsinstance);
            $fv = $form->createView();
            return array(
                "form" => $form->createView(),
                "slug" => $slug,
                "instance" => $wmsinstance);
        }
    }

    /**
     * Changes the priority of WmsInstanceLayers
     *
     * @ManagerRoute("/{slug}/instance/{instanceId}/priority/{instLayerId}")
     */
    public function instanceLayerPriorityAction($slug, $instanceId, $instLayerId)
    {
        $number = $this->get("request")->get("number");
        $instLay = $this->getDoctrine()
            ->getRepository('MapbenderWmsBundle:WmsInstanceLayer')
            ->findOneById($instLayerId);

        if (!$instLay) {
            return new Response(json_encode(array(
                    'error' => 'The wms instance layer with'
                    . ' the id "' . $instanceId . '" does not exist.',
                    'result' => '')), 200,
                array('Content-Type' => 'application/json'));
        }
        if (intval($number) === $instLay->getPriority()) {
            return new Response(json_encode(array(
                    'error' => '',
                    'result' => 'ok')), 200,
                array('Content-Type' => 'application/json'));
        }
        $em = $this->getDoctrine()->getEntityManager();
        $instLay->setPriority($number);
        $em->persist($instLay);
        $em->flush();
        $query = $em->createQuery(
            "SELECT il FROM MapbenderWmsBundle:WmsInstanceLayer il"
            . " WHERE il.wmsinstance=:wmsi ORDER BY il.priority ASC");
        $query->setParameters(array("wmsi" => $instanceId));
        $instList = $query->getResult();

        $num = 0;
        foreach ($instList as $inst) {
            if ($num === intval($instLay->getPriority())) {
                if ($instLay->getId() === $inst->getId()) {
                    $num++;
                } else {
                    $num++;
                    $inst->setPriority($num);
                    $num++;
                }
            } else {
                if ($instLay->getId() !== $inst->getId()) {
                    $inst->setPriority($num);
                    $num++;
                }
            }
        }
        $em->getConnection()->beginTransaction();
        foreach ($instList as $inst) {
            $em->persist($inst);
        }
        $em->flush();
        $wmsinstance = $this->getDoctrine()
            ->getRepository("MapbenderCoreBundle:SourceInstance")
            ->find($instanceId);
        $wmsinstance->generateConfiguration();
        $em->persist($wmsinstance);
        $em->flush();
        $em->getConnection()->commit();
        return new Response(json_encode(array(
                'error' => '',
                'result' => 'ok')), 200,
            array(
            'Content-Type' => 'application/json'));
    }

    /**
     * Sets enabled/disabled for the WmsInstance
     *
     * @ManagerRoute("/instance/{slug}/enabled/{instanceId}")
     * @Method({ "POST" })
     */
    public function instanceEnabledAction($slug, $instanceId)
    {
        $enabled = $this->get("request")->get("enabled");
        $wmsinstance = $this->getDoctrine()
            ->getRepository("MapbenderWmsBundle:WmsInstance")
            ->find($instanceId);
        if (!$wmsinstance) {
            return new Response(json_encode(array(
                    'error' => 'The wms instance with the id "' . $instanceId . '" does not exist.')),
                200, array('Content-Type' => 'application/json'));
        } else {
            $enabled_before = $wmsinstance->getEnabled();
            $enabled = $enabled === "true" ? true : false;
            $wmsinstance->setEnabled($enabled);
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($wmsinstance);
            $em->flush();
            return new Response(json_encode(array(
                    'success' => array(
                        "id" => $wmsinstance->getId(),
                        "type" => "instance",
                        "enabled" => array(
                            'before' => $enabled_before,
                            'after' => $enabled)))), 200,
                array('Content-Type' => 'application/json'));
        }
    }

}
