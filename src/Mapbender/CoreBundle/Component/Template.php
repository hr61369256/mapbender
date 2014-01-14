<?php

namespace Mapbender\CoreBundle\Component;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for all application templates.
 *
 * @author Christian Wygoda
 */
abstract class Template
{

    protected $container;
    protected $application;

    public function __construct(ContainerInterface $container, Application $application)
    {
        $this->container = $container;
        $this->application = $application;
    }

    /**
     * Get the template title.
     *
     * This title is mainly used in the backend manager when creating a new
     * application.
     *
     * @return string
     */
    static public function getTitle()
    {
        throw new \RuntimeException('getTitle must be implemented in subclasses');
    }

    /**
     * Get the element assets.
     *
     * Returns an array of references to asset files of the given type.
     * References can either be filenames/path which are searched for in the
     * Resources/public directory of the element's bundle or assetic references
     * indicating the bundle to search in:
     *
     * array(
     *   'foo.css'),
     *   '@MapbenderCoreBundle/Resources/public/foo.css'));
     *
     * @param string $type Asset type to list, can be 'css' or 'js'
     * @return array
     */
    public function getAssets($type)
    {
        if($type !== 'css' && $type !== 'js' && $type !== 'trans')
        {
            throw new \RuntimeException('The asset type \'' . $type .
                    '\' is not supported.');
        }

        return array();
    }

    /**
     * Get the template regions available in the Twig template.
     *
     * @return array
     */
    static public function getRegions()
    {
        throw new \RuntimeException('getTitle must be implemented in subclasses');
    }

    /**
     * Render the application
     *
     * @param string $format Output format, defaults to HTML
     * @param boolean $html Whether to render the HTML itself
     * @param boolean $css  Whether to include the CSS links
     * @param boolean $js   Whether to include the JavaScript
     * @return string $html The rendered HTML
     */
    abstract public function render($format = 'html', $html = true, $css = true, $js = true);
    
    /**
     * Get the available regions properties.
     *
     * @return array
     */
    public static function getRegionsProperties()
    {
        return array();
    }
    
}

