<?php
/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves Team
 * @copyright Copyright (c) Ksolves India Limited (https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */

namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\SellerLocator;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * KsLocations Block Class
 */
class KsLocations extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerLocatorHelper
     */
    protected $ksHelperData;

    /**
     * @param \Magento\Backend\Block\Template\Context $ksContext
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerLocatorHelper $ksHelperData,
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $ksContext,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerLocatorHelper $ksHelperData,
        array $data = []
    ) {
        $this->ksHelperData = $ksHelperData;
        parent::__construct($ksContext, $data);
    }

    /**
     * Get Api from the admin
     * @return string
     */
    public function getKsGoogleApiKey()
    {
        return $this->ksHelperData->getKsApiKey();
    }

    /**
     * Function to set autocomplete for location & autofilled Latitude,Longitude in admin configuration
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $ksElement
     * @return string
     */
    protected function _getElementHtml(AbstractElement $ksElement)
    {
        $ksHtml = $ksElement->getElementHtml();
        $ksHtml .= '<script type="text/javascript">
                        require(["jquery","https://maps.googleapis.com/maps/api/js?libraries=places&key='.$this->getKsGoogleApiKey().'"], 
                        function ($)
                        {
                            $(document).ready(function ()
                            { 
                                var ksInputLatitude = document.getElementById( "ks_marketplace_seller_locator_ks_seller_locator_ks_seller_locator_latitude"), 
                                ksInputLongitude = document.getElementById("ks_marketplace_seller_locator_ks_seller_locator_ks_seller_locator_longitude"),
                                ksInputLocation =document.getElementById("ks_marketplace_seller_locator_ks_seller_locator_ks_seller_locator_location");
                                
                                var ksGeocoder = new google.maps.Geocoder();

                                var ksAutocomplete = new google.maps.places.Autocomplete(
                                    (document.getElementById("'.$ksElement->getHtmlId().'")),
                                {
                                    types: ["geocode"],
                                });

                                google.maps.event.addListener(ksAutocomplete, "place_changed", function () {  
                                    var ksPlace = ksAutocomplete.getPlace();
                                    var ksLatitude = ksPlace.geometry.location.lat();
                                    var ksLongitude = ksPlace.geometry.location.lng(); 

                                       
                                    $("#ks_marketplace_seller_locator_ks_seller_locator_ks_seller_locator_latitude").val(ksLatitude);
                                    $("#ks_marketplace_seller_locator_ks_seller_locator_ks_seller_locator_longitude").val(ksLongitude); 
                                });

                                $("#ks_marketplace_seller_locator_ks_seller_locator_ks_seller_locator_location").keyup(function(){
                                    if(this.value == "")
                                    {
                                        $("#ks_marketplace_seller_locator_ks_seller_locator_ks_seller_locator_latitude").val("");
                                        $("#ks_marketplace_seller_locator_ks_seller_locator_ks_seller_locator_longitude").val(""); 
                                    }
                                });
                                    
                                $("#ks_marketplace_seller_locator_ks_seller_locator_ks_seller_locator_latitude,#ks_marketplace_seller_locator_ks_seller_locator_ks_seller_locator_longitude").bind("change keyup input",function() { 
                                        ksSetLocation();
                                }); 
                    
                                function ksSetLocation(){
                                    var ksLatLng = {
                                    lat: parseFloat(ksInputLatitude.value),
                                    lng: parseFloat(ksInputLongitude.value)
                                    };
                           
                                    ksGeocoder.geocode({ location: ksLatLng },function (ksResults, ksStatus) {
                                        if (ksStatus === "OK") {
                                            if (ksResults[0]) {
                                                ksAddress = ksResults[0].formatted_address;
                                                ksInputLocation.value = ksAddress;
                                            } 
                                        }
                                    });
                                }  

                                $("#ks_marketplace_seller_locator_ks_seller_locator_ks_seller_locator_latitude").keyup(function(){
                                    if(this.value == "")
                                    {
                                        $("#ks_marketplace_seller_locator_ks_seller_locator_ks_seller_locator_location").val("");
                                        $("#ks_marketplace_seller_locator_ks_seller_locator_ks_seller_locator_longitude").val(""); 
                                    }
                                });

                                $("#ks_marketplace_seller_locator_ks_seller_locator_ks_seller_locator_longitude").keyup(function(){
                                    if(this.value == "")
                                    {
                                        $("#ks_marketplace_seller_locator_ks_seller_locator_ks_seller_locator_location").val("");
                                        $("#ks_marketplace_seller_locator_ks_seller_locator_ks_seller_locator_latitude").val("");
                                    }
                                }); 
                            });
                        });
                    </script>';
        return $ksHtml;
    }
}
