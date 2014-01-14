<?php

/**
 * Mapbender application management
 *
 * @author Christian Wygoda <christian.wygoda@wheregroup.com>
 */
namespace Mapbender\ManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOM\ManagerBundle\Configuration\Route as ManagerRoute;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Mapbender\CoreBundle\Component\Element As ComponentElement;
use Mapbender\CoreBundle\Entity\Element;
use Mapbender\CoreBundle\Form\Type\BaseElementType;
use Doctrine\Common\Collections\ArrayCollection;
use Mapbender\ManagerBundle\Form\Type\YAMLConfigurationType;
use Mapbender\CoreBundle\Validator\Constraints\ContainsElementTarget;
use Mapbender\CoreBundle\Validator\Constraints\ContainsElementTargetValidator;

class ElementController extends Controller
{

    /**
     * Show element class selection
     *
     * @ManagerRoute("/application/{slug}/element/select")
     * @Method({"GET","POST"})
     * @Template
     */
    public function selectAction($slug)
    {
        $trans = $this->container->get('translator');
        $elements = array();
        foreach ($this->get('mapbender')->getElements() as $elementClassName) {
            $title = $trans->trans($elementClassName::getClassTitle());
            $tags = array();
            foreach($elementClassName::getClassTags() as $tag){
                $tags[] = $trans->trans($tag);
            }
            $elements[$title] = array(
                'class' => $elementClassName,
                'title' => $title,
                'description' => $trans->trans($elementClassName::getClassDescription()),
                'tags' => $tags);
        }
        ksort($elements, SORT_LOCALE_STRING);
        return array(
            'elements' => $elements,
            'region' => $this->get('request')->get('region'));
    }

    /**
     * Shows form for creating new element
     *
     * @ManagerRoute("/application/{slug}/element/new")
     * @Method("GET")
     * @Template("MapbenderManagerBundle:Element:edit.html.twig")
     */
    public function newAction($slug)
    {
        $application = $this->get('mapbender')->getApplicationEntity($slug);

        // Get class for element
        $class = $this->getRequest()->get('class');

        if (!class_exists($class)) {
            throw new \RuntimeException('An Element class "' . $class
            . '" does not exist.');
        }

        // Get first region (by default)
        $template = $application->getTemplate();
        $regions = $template::getRegions();
        $region = $this->get('request')->get('region');

        $appl = new \Mapbender\CoreBundle\Component\Application($this->container,
            $application, array());

        $elmComp = new $class($appl, $this->container,
            ComponentElement::getDefaultElement($class, $region));
        $elmComp->getEntity()->setTitle($elmComp->trans($elmComp->getClassTitle()));
        $form = ComponentElement::getElementForm($this->container, $application,
                $elmComp->getEntity());

        return array(
            'form' => $form['form']->createView(),
            'theme' => $form['theme'],
            'assets' => $form['assets']);
    }

    /**
     * Create a new element from POSTed data
     *
     * @ManagerRoute("/application/{slug}/element/new")
     * @Method("POST")
     * @Template("MapbenderManagerBundle:Element:edit.html.twig")
     */
    public function createAction($slug)
    {
        $application = $this->get('mapbender')->getApplicationEntity($slug);

        $data = $this->get('request')->get('form');
        $element = ComponentElement::getDefaultElement($data['class'],
                $data['region']);
        $element->setApplication($application);
        $form = ComponentElement::getElementForm($this->container, $application,
                $element);
        $form['form']->bindRequest($this->get('request'));

        if ($form['form']->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $query = $em->createQuery(
                "SELECT e FROM MapbenderCoreBundle:Element e"
                . " WHERE e.region=:reg AND e.application=:app");
            $query->setParameters(array(
                "reg" => $element->getRegion(),
                "app" => $element->getApplication()->getId()));
            $elements = $query->getResult();
            $element->setWeight(count($elements) + 1);
            $application = $element->getApplication();
            $application->setUpdated(new \DateTime());
            $em->persist($element);
            $em->flush();
            $entity_class = $element->getClass();
            $appl = new \Mapbender\CoreBundle\Component\Application($this->container,
                $application, array());
            $elComp = new $entity_class($appl, $this->container, $element);
            $elComp->postSave();
            $this->get('session')->setFlash('success',
                'Your element has been saved.');

            return new Response('', 201);
        } else {
            return array(
                'form' => $form['form']->createView(),
                'theme' => $form['theme'],
                'assets' => $form['assets']);
        }
    }

    /**
     * @ManagerRoute("/application/{slug}/element/{id}", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template
     */
    public function editAction($slug, $id)
    {
        $application = $this->get('mapbender')->getApplicationEntity($slug);

        $element = $this->getDoctrine()
            ->getRepository('MapbenderCoreBundle:Element')
            ->findOneById($id);

        if (!$element) {
            throw $this->createNotFoundException('The element with the id "'
                . $id . '" does not exist.');
        }
        $form = ComponentElement::getElementForm($this->container, $application,
                $element);

        return array(
            'form' => $form['form']->createView(),
            'theme' => $form['theme'],
            'assets' => $form['assets']);
    }

    /**
     * Updates element by POSTed data
     *
     * @ManagerRoute("/application/{slug}/element/{id}", requirements = {"id" = "\d+" })
     * @Method("POST")
     * @Template("MapbenderManagerBundle:Element:edit.html.twig")
     */
    public function updateAction($slug, $id)
    {
        $application = $this->get('mapbender')->getApplicationEntity($slug);

        $element = $this->getDoctrine()
            ->getRepository('MapbenderCoreBundle:Element')
            ->findOneById($id);

        if (!$element) {
            throw $this->createNotFoundException('The element with the id "'
                . $id . '" does not exist.');
        }
        $form = ComponentElement::getElementForm($this->container, $application,
                $element);
//        $form = $this->getElementForm($application, $element);
        $form['form']->bindRequest($this->get('request'));

        if ($form['form']->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $application = $element->getApplication();
            $application->setUpdated(new \DateTime());
            $em->persist($element);
            $em->flush();

            $entity_class = $element->getClass();
            $appl = new \Mapbender\CoreBundle\Component\Application($this->container,
                $application, array());
            $elComp = new $entity_class($appl, $this->container, $element);
            $elComp->postSave();
            $this->get('session')->setFlash('success',
                'Your element has been saved.');

            return new Response('', 205);
        } else {
            return array(
                'form' => $form['type']->getForm()->createView(),
                'theme' => $form['theme'],
                'assets' => $form['assets']);
        }
    }

    /**
     * Shows delete confirmation page
     *
     * @ManagerRoute("application/{slug}/element/{id}/delete", requirements = {
     *     "id" = "\d+" })
     * @Method("GET")
     * @Template("MapbenderManagerBundle:Element:delete.html.twig")
     */
    public function confirmDeleteAction($slug, $id)
    {
        $application = $this->get('mapbender')->getApplicationEntity($slug);

        $element = $this->getDoctrine()
            ->getRepository('MapbenderCoreBundle:Element')
            ->findOneById($id);

        if (!$element) {
            throw $this->createNotFoundException('The element with the id "'
                . $id . '" does not exist.');
        }

        return array(
            'element' => $element,
            'form' => $this->createDeleteForm($id)->createView());
    }

    /**
     * Delete element
     *
     * @ManagerRoute("application/{slug}/element/{id}/delete")
     * @Method("POST")
     */
    public function deleteAction($slug, $id)
    {
        $application = $this->get('mapbender')->getApplicationEntity($slug);

        $element = $this->getDoctrine()
            ->getRepository('MapbenderCoreBundle:Element')
            ->findOneById($id);

        if (!$element) {
            throw $this->createNotFoundException('The element with the id "'
                . $id . '" does not exist.');
        }

        $em = $this->getDoctrine()->getEntityManager();
        $query = $em->createQuery(
            "SELECT e FROM MapbenderCoreBundle:Element e"
            . " WHERE e.region=:reg AND e.application=:app"
            . " AND e.weight>=:min ORDER BY e.weight ASC");
        $query->setParameters(array(
            "reg" => $element->getRegion(),
            "app" => $element->getApplication()->getId(),
            "min" => $element->getWeight()));
        $elements = $query->getResult();
        foreach ($elements as $elm) {
            if ($elm->getId() !== $element->getId()) {
                $elm->setWeight($elm->getWeight() - 1);
            }
        }
        foreach ($elements as $elm) {
            $em->persist($elm);
        }
        $em->remove($element);
        $em->flush();

        $this->get('session')->setFlash('success',
            'Your element has been removed.');

        return new Response();
    }

    /**
     * Delete element
     *
     * @ManagerRoute("application/element/{id}/weight")
     * @Method("POST")
     */
    public function weightAction($id)
    {
        $element = $this->getDoctrine()
            ->getRepository('MapbenderCoreBundle:Element')
            ->findOneById($id);

        if (!$element) {
            throw $this->createNotFoundException('The element with the id "'
                . $id . '" does not exist.');
        }
        $number = $this->get("request")->get("number");
        $newregion = $this->get("request")->get("region");
        if (intval($number) === $element->getWeight() && $element->getRegion() ===
            $newregion) {
            return new Response(json_encode(array(
                    'error' => '',
                    'result' => 'ok')), 200,
                array('Content-Type' => 'application/json'));
        }
        if ($element->getRegion() === $newregion) {
            $em = $this->getDoctrine()->getEntityManager();
            $element->setWeight($number);
            $em->persist($element);
            $em->flush();
            $query = $em->createQuery(
                "SELECT e FROM MapbenderCoreBundle:Element e"
                . " WHERE e.region=:reg AND e.application=:app"
                . " ORDER BY e.weight ASC");
            $query->setParameters(array(
                "reg" => $newregion,
                "app" => $element->getApplication()->getId()));
            $elements = $query->getResult();

            $num = 0;
            foreach ($elements as $elm) {
                if ($num === intval($element->getWeight())) {
                    if ($element->getId() === $elm->getId()) {
                        $num++;
                    } else {
                        $num++;
                        $elm->setWeight($num);
                        $num++;
                    }
                } else {
                    if ($element->getId() !== $elm->getId()) {
                        $elm->setWeight($num);
                        $num++;
                    }
                }
            }
            foreach ($elements as $elm) {
                $em->persist($elm);
            }
            $application = $element->getApplication();
            $application->setUpdated(new \DateTime());
            $em->persist($application);
            $em->flush();
        } else {
            // handle old region
            $em = $this->getDoctrine()->getEntityManager();
            $query = $em->createQuery(
                "SELECT e FROM MapbenderCoreBundle:Element e"
                . " WHERE e.region=:reg AND e.application=:app"
                . " AND e.weight>=:min ORDER BY e.weight ASC");
            $query->setParameters(array(
                "reg" => $element->getRegion(),
                "app" => $element->getApplication()->getId(),
                "min" => $element->getWeight()));
            $elements = $query->getResult();
            foreach ($elements as $elm) {
                if ($elm->getId() !== $element->getId()) {
                    $elm->setWeight($elm->getWeight() - 1);
                }
            }
            foreach ($elements as $elm) {
                $em->persist($elm);
            }
            $em->flush();
            // handle new region
            $query = $em->createQuery(
                "SELECT e FROM MapbenderCoreBundle:Element e"
                . " WHERE e.region=:reg AND e.application=:app"
                . " AND e.weight>=:min ORDER BY e.weight ASC");
            $query->setParameters(array(
                "reg" => $newregion,
                "app" => $element->getApplication()->getId(),
                "min" => $number));
            $elements = $query->getResult();
            foreach ($elements as $elm) {
                if ($elm->getId() !== $element->getId()) {
                    $elm->setWeight($elm->getWeight() + 1);
                }
            }
            foreach ($elements as $elm) {
                $em->persist($elm);
            }
            $em->flush();
            $element->setWeight($number);
            $element->setRegion($newregion);
            $em->persist($element);
            $application = $element->getApplication();
            $application->setUpdated(new \DateTime());
            $em->persist($application);
            $em->flush();
        }
        return new Response(json_encode(array(
                'error' => '',
                'result' => 'ok')), 200,
            array(
            'Content-Type' => 'application/json'));
    }

    /**
     * Delete element
     *
     * @ManagerRoute("application/element/{id}/enable")
     * @Method("POST")
     */
    public function enableAction($id)
    {
        $element = $this->getDoctrine()
            ->getRepository('MapbenderCoreBundle:Element')
            ->findOneById($id);

        $enabled = $this->get("request")->get("enabled");
        if (!$element) {
            return new Response(json_encode(array(
                    'error' => 'An element with the id "' . $id . '" does not exist.')),
                200, array('Content-Type' => 'application/json'));
        } else {
            $enabled_before = $element->getEnabled();
            $enabled = $enabled === "true" ? true : false;
            $element->setEnabled($enabled);
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($element);
            $em->flush();
            return new Response(json_encode(array(
                    'success' => array(
                        "id" => $element->getId(),
                        "type" => "element",
                        "enabled" => array(
                            'before' => $enabled_before,
                            'after' => $enabled)))), 200,
                array('Content-Type' => 'application/json'));
        }
    }

    /**
     * Creates the form for the delete confirmation page
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
                ->add('id', 'hidden')
                ->getForm();
    }

}
