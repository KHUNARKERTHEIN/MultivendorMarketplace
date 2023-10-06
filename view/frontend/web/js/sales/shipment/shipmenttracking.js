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
    "jquery/ui"
    ], function ($,ksUrl,confirmation) {
     "use strict";
     function main(config, element) {

       $(document).on('click','#ks-add-track',function() {
             event.preventDefault();
             var code  = $('#carrier').val();
             var title = $('#tracking_title').val();
             var number = $('#tracking_number').val();
             $.ajax({
                url: ksUrl.build('multivendor/order_shipment/addtrack'),
                type: 'POST',
                data:{carrier:code,number:number,title:title,sales_shipment_id:config.shipment_id},
                showLoader: true,
            }).done(function(response){
                if (response["error"]) {
                    alert(response["message"]);                            
                } else{
                    $("#ks-tracking-section").load(location.href+ " #ks-shipment-track");
                }       
            });
        });
        $(".ks_tracking_info").on('click', function(e){
            e.preventDefault();
            var url = e.currentTarget.href;
            confirmation({
                  content: $.mage.__('Are you sure you want to send a Shipment email to customer?'),
                  actions: {
                      confirm: function(){
                        window.location.href = url;
                    },
                    cancel: function(){
                  },
                }
            });
        });

       $(document).on('click','.action-delete',function() {
        event.preventDefault();
        var trackId = $(this).attr('track-id');
        confirmation({
            content: $.mage.__('Are you sure?'),
            actions: {
                confirm: function(){
                    $.ajax({
                        url: ksUrl.build('multivendor/order_shipment/removetrack'),
                        type: 'GET',
                        data:{track_id:trackId,sales_shipment_id:config.shipment_id},
                        showLoader: true,
                        success: function(response){
                            if (response["error"]) {
                                alert(response["message"]);                            
                            } else{
                                $("#ks-tracking-section").load(location.href+ " #ks-shipment-track");
                                return true;
                            }       
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            $("#ks-tracking-section").load(location.href+ " #ks-shipment-track");
                        }
                    });
                },
                cancel: function(){
              },
            }
        });
    });
   };
   return main;

});
 