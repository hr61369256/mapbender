<?php
namespace MB\WMSBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use MB\CoreBundle\Entity\Keyword;
use MB\WMSBundle\Entity\GroupLayer;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
*/
class WMSService extends GroupLayer {

    /**
    * @ORM\Column(type="string", nullable="true")
    */
    protected $version = "";

    /**
    * @ORM\Column(type="string",nullable="true")
    */
    protected $onlineResource;

    /**
    * @ORM\Column(type="string",nullable="true")
    */
    protected $ContactPerson;
    
    /**
    * @ORM\Column(type="string",nullable="true")
    */
    protected $ContactPosition;

    /**
    * @ORM\Column(type="string",nullable="true")
    */
    protected $ContactOrganization;
    
    /**
    * @ORM\Column(type="string",nullable="true")
    */
    protected $ContactVoiceTelephone;
    
    /**
    * @ORM\Column(type="string",nullable="true")
    */
    protected $ContactFacsimileTelephone;
    
    /**
    * @ORM\Column(type="string",nullable="true")
    */
    protected $ContactElectronicMailAddress;
    
    /**
    * @ORM\Column(type="string",nullable="true")
    */
    protected $ContactAddress;
    
    /**
    * @ORM\Column(type="string",nullable="true")
    */
    protected $ContactAddressType;
    
    /**
    * @ORM\Column(type="string",nullable="true")
    */
    protected $ContactAddressCity;
    
    /**
    * @ORM\Column(type="string",nullable="true")
    */
    protected $ContactAddressStateOrProvince;
    
    /**
    * @ORM\Column(type="string",nullable="true")
    */
    protected $ContactAddressPostCode;
    
    /**
    * @ORM\Column(type="string",nullable="true")
    */
    protected $ContactAddressCountry;

    /**
    * @ORM\Column(type="string", nullable="true")
    */
    protected $fees = "";
    
    /**
    * @ORM\Column(type="string",nullable="true")
    */
    protected $accessConstraints = "";
    
    /**
    * @ORM\Column(type="string",nullable="true")
    * FIXME: change to array type
    */
    protected $exceptionFormats = "";
    
    /**
    * @ORM\Column(type="boolean")
    */
    protected $symbolSupportSLD = false;
    
    /**
    * @ORM\Column(type="boolean")
    */
    protected $symbolUserLayer = false;
    
    /**
    * @ORM\Column(type="boolean")
    */
    protected $symbolUserStyle = false;
    
    /**
    * @ORM\Column(type="boolean")
    */
    protected $symbolRemoteWFS = false;

    /**
    * @ORM\Column(type="string", nullable="true")
    */
    protected $requestGetCapabilitiesGET = "";
    /**
    * @ORM\Column(type="string", nullable="true")
    */
    protected $requestGetCapabilitiesPOST = "";

    /**
    * @ORM\Column(type="string", nullable="true")
    */
    protected $requestGetCapabilitiesFormats = "";
    
    /**
    * @ORM\Column(type="string", nullable="true")
    */
    protected $requestGetMapGET = "";
    /**
    * @ORM\Column(type="string", nullable="true")
    */
    protected $requestGetMapPOST = "";

    /**
    * @ORM\Column(type="string", nullable="true")
    */
    protected $requestGetMapFormats = "";

    /**
    * @ORM\Column(type="string", nullable="true")
    */
    protected $requestGetFeatureInfoGET = "";
    /**
    * @ORM\Column(type="string", nullable="true")
    */
    protected $requestGetFeatureInfoPOST = "";

    /**
    * @ORM\Column(type="string", nullable="true")
    */
    protected $requestGetFeatureInfoFormats = "";

    /**
    * @ORM\Column(type="string", nullable="true")
    */
    protected $requestDescribeLayerGET = "";
    /**
    * @ORM\Column(type="string", nullable="true")
    */
    protected $requestDescribeLayerPOST = "";

    /**
    * @ORM\Column(type="string", nullable="true")
    */
    protected $requestDescribeLayerFormats = "";

    /**
    * @ORM\Column(type="string", nullable="true")
    */
    protected $requestGetLegendGraphicGET = "";
    /**
    * @ORM\Column(type="string", nullable="true")
    */
    protected $requestGetLegendGraphicPOST = "";

    /**
    * @ORM\Column(type="string", nullable="true")
    */
    protected $requestGetLegendGraphicFormats = "";

    /**
    * @ORM\Column(type="string", nullable="true")
    */
    protected $requestGetStylesGET = "";
    /**
    * @ORM\Column(type="string", nullable="true")
    */
    protected $requestGetStylesPOST = "";

    /**
    * @ORM\Column(type="string", nullable="true")
    */
    protected $requestGetStylesFormats = "";

    /**
    * @ORM\Column(type="string", nullable="true")
    */
    protected $requestPutStylesGET = "";
    /**
    * @ORM\Column(type="string", nullable="true")
    */
    protected $requestPutStylesPOST = "";

    /**
    * @ORM\Column(type="string", nullable="true")
    */
    protected $requestPutStylesFormats = "";


    public function __construct() {
        # calling super  - how to avoid ?
        return parent::__construct();
    }

    public function setVersion($version){
        $this->version = $version;
    }
    
    public function getVersion(){
        return $this->version;
    }
    
    public function setFees($fees){
        $this->fees = $fees;
    }
    
    public function getFees(){
        return $this->fees;
    }
    
    public function setAccessConstraints($accessConstraints){
        $this->accessConstraints = $accessConstraints;
    }
    
    public function getAccessConstraints(){
        return $this->accessConstraints;
    }

    public function setExceptionFormats($exceptionFormats){
        $this->exceptionFormats = $exceptionFormats;
    }
    public function getExceptionFormats(){
        return $this->exceptionFormats;
    }
    
    /**
     * returns the default (first) exceptionFormats that a wms supports 
    */
    public function getDefaultExceptionFormats(){
        $formats = explode(',',$this->exceptionFormats);
        return $formats[0];
    }
    
   /**
    *
    */ 
    public function getRootLayer(){
        return $this->getLayer()->get(0);
    }


    /**
     * Set onlineResource
     *
     * @param string $onlineResource
     */
    public function setOnlineResource($onlineResource)
    {
        $this->onlineResource = $onlineResource;
    }

    /**
     * Get onlineResource
     *
     * @return string 
     */
    public function getOnlineResource()
    {
        return $this->onlineResource;
    }

    /**
     * Set ContactPerson
     *
     * @param string $contactPerson
     */
    public function setContactPerson($contactPerson)
    {
        $this->ContactPerson = $contactPerson;
    }

    /**
     * Get ContactPerson
     *
     * @return string 
     */
    public function getContactPerson()
    {
        return $this->ContactPerson;
    }

    /**
     * Set ContactOrganization
     *
     * @param string $contactOrganization
     */
    public function setContactOrganization($contactOrganization)
    {
        $this->ContactOrganization = $contactOrganization;
    }

    /**
     * Get ContactOrganization
     *
     * @return string 
     */
    public function getContactOrganization()
    {
        return $this->ContactOrganization;
    }
    
    /**
     * Set ContactPosition
     *
     * @param string $contactPosition
     */
    public function setContactPosition($contactPosition)
    {
        $this->ContactPosition = $contactPosition;
    }

    /**
     * Get ContactPosition
     *
     * @return string 
     */
    public function getContactPosition()
    {
        return $this->ContactPosition;
    }

    /**
     * Set ContactVoiceTelephone
     *
     * @param string $contactVoiceTelephone
     */
    public function setContactVoiceTelephone($contactVoiceTelephone)
    {
        $this->ContactVoiceTelephone = $contactVoiceTelephone;
    }

    /**
     * Get ContactVoiceTelephone
     *
     * @return string 
     */
    public function getContactVoiceTelephone()
    {
        return $this->ContactVoiceTelephone;
    }

    /**
     * Set ContactFacsimileTelephone
     *
     * @param string $contactFacsimileTelephone
     */
    public function setContactFacsimileTelephone($contactFacsimileTelephone)
    {
        $this->ContactFacsimileTelephone = $contactFacsimileTelephone;
    }

    /**
     * Get ContactFacsimileTelephone
     *
     * @return string 
     */
    public function getContactFacsimileTelephone()
    {
        return $this->ContactFacsimileTelephone;
    }

    /**
     * Set ContactElectronicMailAddress
     *
     * @param string $contactElectronicMailAddress
     */
    public function setContactElectronicMailAddress($contactElectronicMailAddress)
    {
        $this->ContactElectronicMailAddress = $contactElectronicMailAddress;
    }

    /**
     * Get ContactElectronicMailAddress
     *
     * @return string 
     */
    public function getContactElectronicMailAddress()
    {
        return $this->ContactElectronicMailAddress;
    }

    /**
     * Set ContactAddress
     *
     * @param string $contactAddress
     */
    public function setContactAddress($contactAddress)
    {
        $this->ContactAddress = $contactAddress;
    }

    /**
     * Get ContactAddress
     *
     * @return string 
     */
    public function getContactAddress()
    {
        return $this->ContactAddress;
    }

    /**
     * Set ContactAddressType
     *
     * @param string $contactAddressType
     */
    public function setContactAddressType($contactAddressType)
    {
        $this->ContactAddressType = $contactAddressType;
    }

    /**
     * Get ContactAddressType
     *
     * @return string 
     */
    public function getContactAddressType()
    {
        return $this->ContactAddressType;
    }

    /**
     * Set ContactAddressCity
     *
     * @param string $contactAddressCity
     */
    public function setContactAddressCity($contactAddressCity)
    {
        $this->ContactAddressCity = $contactAddressCity;
    }

    /**
     * Get ContactAddressCity
     *
     * @return string 
     */
    public function getContactAddressCity()
    {
        return $this->ContactAddressCity;
    }

    /**
     * Set ContactAddressStateOrProvince
     *
     * @param string $contactAddressStateOrProvince
     */
    public function setContactAddressStateOrProvince($contactAddressStateOrProvince)
    {
        $this->ContactAddressStateOrProvince = $contactAddressStateOrProvince;
    }

    /**
     * Get ContactAddressStateOrProvince
     *
     * @return string 
     */
    public function getContactAddressStateOrProvince()
    {
        return $this->ContactAddressStateOrProvince;
    }

    /**
     * Set ContactAddressPostCode
     *
     * @param string $contactAddressPostCode
     */
    public function setContactAddressPostCode($contactAddressPostCode)
    {
        $this->ContactAddressPostCode = $contactAddressPostCode;
    }

    /**
     * Get ContactAddressPostCode
     *
     * @return string 
     */
    public function getContactAddressPostCode()
    {
        return $this->ContactAddressPostCode;
    }

    /**
     * Set ContactAddressCountry
     *
     * @param string $contactAddressCountry
     */
    public function setContactAddressCountry($contactAddressCountry)
    {
        $this->ContactAddressCountry = $contactAddressCountry;
    }

    /**
     * Get ContactAddressCountry
     *
     * @return string 
     */
    public function getContactAddressCountry()
    {
        return $this->ContactAddressCountry;
    }
    
    public function setRequestGetCapabilitiesGET($requestGetCapabilitiesGET){
        $this->requestGetCapabilitiesGET = $requestGetCapabilitiesGET;
    }
    
    public function getRequestGetCapabilitiesGET(){
        return $this->requestGetCapabilitiesGET;
    }

    public function setRequestGetCapabilitiesPOST($requestGetCapabilitiesPOST){
        $this->requestGetCapabilitiesPOST = $requestGetCapabilitiesPOST;
    }
    
    public function getRequestGetCapabilitiesPOST(){
        return $this->requestGetCapabilitiesPOST;
    }
    
    public function setRequestGetCapabilitiesFormats($formats){
        $this->requestGetCapabilitiesFormats = $formats;
    }
    
    public function getRequestGetCapabilitiesFormats(){
        return $this->requestGetCapabilitiesFormats;
    }

    /**
     * returns the default (first) format that a wms supports for getCapabilities requests
    */
    public function getDefaultRequestGetCapabilitiesFormat(){
        $formats = explode(',',$this->requestGetCapabilitiesFormats);
        return $formats[0];
    }




    public function setRequestGetMapGET($requestGetMapGET){
        $this->requestGetMapGET = $requestGetMapGET;
    }
    
    public function getRequestGetMapGET(){
        return $this->requestGetMapGET;
    }
    
    public function setRequestGetMapPOST($requestGetMapPOST){
        $this->requestGetMapPOST = $requestGetMapPOST;
    }
    
    public function getRequestGetMapPOST(){
        return $this->requestGetMapPOST;
    }
    
    public function setRequestGetMapFormats($formats){
        $this->requestGetMapFormats = $formats;
    }
    
    public function getRequestGetMapFormats(){
        return $this->requestGetMapFormats;
    }
    
    /**
     * returns the default (first) format that a wms supports for getMap requests
    */
    public function getDefaultRequestGetMapFormat(){
        $formats = explode(',',$this->requestGetMapFormats);
        return $formats[0];
    }



    public function setRequestGetFeatureInfoGET($requestGetFeatureInfoGET){
        $this->requestGetFeatureInfoGET = $requestGetFeatureInfoGET;
    }
    
    public function getRequestGetFeatureInfoGET(){
        return $this->requestGetFeatureInfoGET;
    }
    
    public function setRequestGetFeatureInfoPOST($requestGetFeatureInfoPOST){
        $this->requestGetFeatureInfoPOST = $requestGetFeatureInfoPOST;
    }
    
    public function getRequestGetFeatureInfoPOST(){
        return $this->requestGetFeatureInfoPOST;
    }
    
    public function setRequestGetFeatureInfoFormats($formats){
        $this->requestGetFeatureInfoFormats = $formats;
    }
    
    public function getRequestGetFeatureInfoFormats(){
        return $this->requestGetFeatureInfoFormats;
    }
    
    /**
     * returns the default (first) format that a wms supports for getFeatureInfo requests
    */
    public function getDefaultRequestGetFeatureInfoFormat(){
        $formats = explode(',',$this->requestGetFeatureInfoFormats);
        return $formats[0];
    }



    public function setRequestDescribeLayerGET($requestDescribeLayerGET){
        $this->requestDescribeLayerGET = $requestDescribeLayerGET;
    }
    
    public function getRequestDescribeLayerGET(){
        return $this->requestDescribeLayerGET;
    }
    
    public function setRequestDescribeLayerPOST($requestDescribeLayerPOST){
        $this->requestDescribeLayerPOST = $requestDescribeLayerPOST;
    }
    
    public function getRequestDescribeLayerPOST(){
        return $this->requestDescribeLayerPOST;
    }
    
    public function setRequestDescribeLayerFormats($formats){
        $this->requestDescribeLayerFormats = $formats;
    }
    
    public function getRequestDescribeLayerFormats(){
        return $this->requestDescribeLayerFormats;
    }
    
    /**
     * returns the default (first) format that a wms supports for getDescribeLayer requests
    */
    public function getDefaultRequestDescribeLayerFormat(){
        $formats = explode(',',$this->requestDescribeLayerFormats);
        return $formats[0];
    }



    public function setRequestGetLegendGraphicGET($requestGetLegendGraphicGET){
        $this->requestGetLegendGraphicGET = $requestGetLegendGraphicGET;
    }
    
    public function getRequestGetLegendGraphicGET(){
        return $this->requestGetLegendGraphicGET;
    }
    
    public function setRequestGetLegendGraphicPOST($requestGetLegendGraphicPOST){
        $this->requestGetLegendGraphicPOST = $requestGetLegendGraphicPOST;
    }
    
    public function getRequestGetLegendGraphicPOST(){
        return $this->requestGetLegendGraphicPOST;
    }
    
    public function setRequestGetLegendGraphicFormats($formats){
        $this->requestGetLegendGraphicFormats = $formats;
    }
    
    public function getRequestGetLegendGraphicFormats(){
    
        return $this->requestGetLegendGraphicFormats;
    }
    
    /**
     * returns the default (first) format that a wms supports for getLegendGraphic requests
    */
    public function getDefaultRequestGetLegendGraphicFormat(){
        $formats = explode(',',$this->requestGetLegendGraphicFormats);
        return $formats[0];
    }



    public function setRequestGetStylesGET($requestGetStylesGET){
        $this->requestGetStylesGET = $requestGetStylesGET;
    }
    
    public function getRequestGetStylesGET(){
        return $this->requestGetStylesGET;
    }
    
    public function setRequestGetStylesPOST($requestGetStylesPOST){
        $this->requestGetStylesPOST = $requestGetStylesPOST;
    }
    
    public function getRequestGetStylesPOST(){
        return $this->requestGetStylesPOST;
    }
    
    public function setRequestGetStylesFormats($formats){
        $this->requestGetStylesFormats = $formats;
    }
    
    public function getRequestGetStylesFormats(){
        return $this->requestGetStylesFormats;
    }
    
    /**
     * returns the default (first) format that a wms supports for getStyles requests
    */
    public function getDefaultRequestGetStylesFormat(){
        $formats = explode(',',$this->requestGetStylesFormats);
        return $formats[0];
    }



    public function setRequestPutStylesGET($requestPutStylesGET){
        $this->requestPutStylesGET = $requestPutStylesGET;
    }
    
    public function getRequestPutStylesGET(){
        return $this->requestPutStylesGET;
    }
    
    public function setRequestPutStylesPOST($requestPutStylesPOST){
        $this->requestPutStylesPOST = $requestPutStylesPOST;
    }
    
    public function getRequestPutStylesPOST(){
        return $this->requestPutStylesPOST;
    }
    
    public function setRequestPutStylesFormats($formats){
        $this->requestPutStylesFormats = $formats;
    }
    
    public function getRequestPutStylesFormats(){
        return $this->requestPutStylesFormats;
    }
    
    /**
     * returns the default (first) format that a wms supports for putStyles requests
    */
    public function getDefaultRequestPutStylesFormat(){
        $formats = explode(',',$this->requestPutStylesFormats);
        return $formats[0];
    }


}
