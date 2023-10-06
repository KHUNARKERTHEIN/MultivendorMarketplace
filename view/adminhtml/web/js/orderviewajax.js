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

        $(document).on('click','#comment-invoice',function(e) {
            var dataForm = $('#invoice_history_section');
            e.preventDefault();
            var param = dataForm.serialize();
            var AjaxUrl = config.InvoiceAjaxUrl;
            $.ajax({
                showLoader: true,
                url: AjaxUrl,
                data: param,
                type: "POST",
            }).done(function (data) {
                if(data.success == false){
                    alert(data.message);
                }
                $("#invoice_history_block").load(location.href + " #invoice_history_block");
                return true;
            });
        });
        // CreditMemo Comments
            $(document).on('click','#comment-creditmemo',function(e) {
            var dataForm = $('#creditmemo_history_section');
            e.preventDefault();
            var param = dataForm.serialize();
            var AjaxUrl = config.CreditMemoAjaxUrl;
            $.ajax({
                showLoader: true,
                url: AjaxUrl,
                data: param,
                type: "POST",
            }).done(function (data) {
                if(data.success == false){
                    alert(data.message);
                }
                $("#creditmemo_history_block").load(location.href + " #creditmemo_history_block");
                return true;
            });
        });
    };
    return main;
});