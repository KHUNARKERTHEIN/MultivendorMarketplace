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
    "jquery/ui",
], function ($,ksUrl) {
 "use strict";
function main(config, element) {
        $(document).ready(function(){
            // Shipment Comments
            $(document).on('click','#comment-shipment',function(e) {
            var dataForm = $('#shipment_history_section');
            e.preventDefault();
            var param = dataForm.serialize();
            var AjaxUrl = config.ShipmentAjaxUrl;
            $.ajax({
                showLoader: true,
                url: AjaxUrl,
                data: param,
                type: "POST",
            }).done(function (data) {
                if(data.success == false){
                    alert(data.message);
                }
                $("#shipment_history_block").load(location.href + " #shipment_history_block");
                return true;
            });
        });
        });
    };
return main;

});