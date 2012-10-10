<?php
namespace Mapbender\WmsBundle\Entity;


/**
 * Attribution class.
 *
 * @author Paul Schmidt <paul.schmidt@wheregroup.com>
 */
class Attribution {
    /**
     * ORM\Column(type="string", nullable=false)
     */
    protected $title;
    /**
     * ORM\Column(type="string", nullable=false)
     */
    protected $onlineResource;
    /**
     * ORM\Column(type="string", nullable=false)
     */
    protected $logoUrl;
    /**
     * ORM\Column(type="string", nullable=false)
     */
    protected $logoFormat;
    /**
     * ORM\Column(type="integer", nullable=false)
     */
    protected $logoWidth;
    /**
     * ORM\Column(type="integer", nullable=false)
     */
    protected $logoHeight;
    
    
    
    
    /**
     * Creates an Attribution object from parameters
     * @param array $parameters
     */
    public static function create($parameters){
        $attr = new Attribution();
        if(isset($parameters["title"])){
            $attr->title = $parameters["title"];
        }
        if(isset($parameters["onlineResource"])){
            $attr->onlineResource = $parameters["onlineResource"];
        }
        if(isset($parameters["logoUrl"])){
            $attr->logoUrl = $parameters["logoUrl"];
        }
        if(isset($parameters["logoFormat"])){
            $attr->logoFormat = $parameters["logoFormat"];
        }
        if(isset($parameters["logoWidth"])){
            $attr->logoWidth = $parameters["logoWidth"];
        }
        if(isset($parameters["logoHeight"])){
            $attr->logoHeight = $parameters["logoHeight"];
        }
        return $attr;
    }

    /**
     * Gets title
     * 
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }
    
    /**
     * Sets title
     * @param string $value 
     */
    public function setTitle($value) {
        $this->title = $value;
    }

    /**
     * Gets onlineResource
     * 
     * @return string
     */
    public function getOnlineResource() {
        return $this->onlineResource;
    }
    
    /**
     * Sets onlineResource
     * @param string $value 
     */
    public function setOnlineResource($value) {
        $this->onlineResource = $value;
    }

    /**
     * Gets logoUrl
     * 
     * @return string
     */
    public function getLogoUrl() {
        return $this->logoUrl;
    }
    /**
     * Sets logoUrl
     * @param string $value 
     */
    public function setLogoUrl($value) {
        $this->logoUrl = $value;
    }

    /**
     * Gets logoFormat
     * 
     * @return string
     */
    public function getLogoFormat() {
        return $this->logoFormat;
    }
    /**
     * Sets logoFormat
     * @param string $value 
     */
    public function setLogoFormat($value) {
        $this->logoFormat = $value;
    }

    /**
     * Gets logoWidth
     * 
     * @return integer
     */
    public function getLogoWidth() {
        return $this->logoWidth;
    }
    /**
     * Sets logoWidth
     * @param integer $value 
     */
    public function setLogoWidth($value) {
        $this->logoWidth = $value;
    }

    /**
     * Gets logoHeight
     * 
     * @return integer
     */
    public function getLogoHeight() {
        return $this->logoHeight;
    }
    /**
     * Sets logoHeight
     * @param integer $value 
     */
    public function setLogoHeight($value) {
        $this->logoHeight = $value;
    }
    
    /**
     * Gets object as array
     * 
     * @return array
     */
    public function toArray() {
        return array (
            "title" => $this->title,
            "onlineResource" => $this->onlineResource,
            "logoUrl" => $this->logoUrl,
            "logoFormat" => $this->logoFormat,
            "logoWidth" => $this->logoWidth,
            "logoHeight" => $this->logoHeight
            );
    }
}