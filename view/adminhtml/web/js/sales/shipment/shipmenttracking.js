/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves Team
 * @copyright Copyright (c) Ksolves India Limited (https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */
 
/*jshint browser:true jquery:true*/

define([
    "jquery",
    "mage/url",
    "Magento_Ui/js/modal/confirm",
    "jquery/ui",
], function ($,ksUrl,confirmation) {
 "use strict";
function main(config, element) {
        $(document).ready(function(){
            
            $(document).on('change','#carrier',function() {
                var selectedText = $(this).find("option:selected").text();
                var selectedText = $.trim(selectedText);
                if(selectedText != "Custom Value"){
                    $('#tracking_title').val(selectedText);
                }else{
                    $('#tracking_title').val('');
                }
            });

            $(document).on('click','#ks-add-track',function() {
                event.preventDefault();
                var code  = $('#carrier').find(":selected").val();
                var title = $('#tracking_title').val();
                var number = $('#tracking_number').val();
                var AddTrackAjaxUrl = config.ShipmentAddTrackAjaxUrl;
                $.ajax({
                    url: AddTrackAjaxUrl,
                    type: 'POST',
                    data:{carrier:code,number:number,title:title,sales_shipment_id:config.shipment_id},
                    showLoader: true,
                    success: function(response){
                        if (response["error"]) {
                            alert(response["message"]);                            
                        } else{
                           $("#ks-shipment-track").load(location.href+ " #ks-shipment-track");
                        }       
                    }
                });
            });

            $(document).on('click','.action-delete',function() {
                event.preventDefault();
                var trackId = $(this).attr('track-id');
                var RemoveTrackAjaxUrl = config.ShipmentRemoveTrackAjaxUrl;
                confirmation({
                    content: $.mage.__('Are you sure?'),
                    actions: {
                        confirm: function(){
                            $.ajax({
                                url: RemoveTrackAjaxUrl,
                                type: 'GET',
                                data:{track_id:trackId,sales_shipment_id:config.shipment_id},
                                showLoader: true,
                                success: function(response){
                                    if (response["error"]) {
                                        alert(response["message"]);                            
                                    } else{
                                       $("#ks-shipment-track").load(location.href+ " #ks-shipment-track");
                                    }       
                                }
                            });
                        },
                        cancel: function(){
                      },
                    }
                });
            });

           
        });
    };
return main;

});