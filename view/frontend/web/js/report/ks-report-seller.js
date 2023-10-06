/**
* Ksolves
*
* @category  Ksolves
* @package   Ksolves_MultivendorMarketplace
* @author    Ksolves Team
* @copyright Copyright (c) Ksolves India Limited (https://www.ksolves.com/)
* @license   https://store.ksolves.com/magento-license
*/
define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'Magento_Ui/js/modal/alert',
    'mage/url',
    'mage/translate',
    'mage/template',
    'jquery/ui',
],function ($,modal,alert,ksUrl,t, mageTemplate) {
    'use strict';
    $.widget('multivendor.ksReportSeller', {
        options: {},
        _create: function () {
            var self = this;
            
            $(document).ready(function () {
                /*add event listener on reason dropdown*/
                $("#ks-report-seller-reason").change(function(){
                    $('#ks-report-seller-sub-reason').find('option').not(':first').remove();
                    if ($("#ks-report-seller-reason").val()) {
                        $.ajax({
                            url: ksUrl.build('multivendor/reportseller/kssubreasons'),
                            type: 'GET',
                            data:{reason:$("#ks-report-seller-reason").val()},
                            showLoader: true,
                            success: function(response){
                                if (response.length>0) {
                                    response.forEach((subReason)=>{
                                        $('#ks-report-seller-sub-reason')
                                        .append($("<option></option>")
                                        .attr("value", subReason.ks_subreason)
                                        .text(subReason.ks_subreason));
                                    }); 
                                }                
                            },
                            error: function (xhr, ajaxOptions, thrownError) {           
                            }
                        });
                    }
                });
                // for seller detail page popup
                $(".ks-report-seller").click(function(e){
                    /*reset form and its validation*/
                    var validator = $("#ks-report-seller-form").validate();
                    validator.resetForm();
                    $('#ks-report-seller-form').trigger("reset");
                    if (self.options.ksCustomerId==0 && self.options.ksAllowGuests==0) {
                        alert({
                            title: $.mage.__('Please login to report the seller'),
                            actions: {
                                always: function(){}
                            }
                        });
                        
                    } else{
                        $("#ks-report-form-popup").toggle();
                    }
                });
                
                // for product detail page
                var popupOptions = {
                    title: self.options.ksReportHeading,
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    buttons: [{
                        text: 'Submit',
                        class: 'action',
                        click: function(){
                            $("#ks-report-seller-form").submit();
                        }
                    }]
                };
                
                var reportPopup = modal(popupOptions, $('#ks-report-seller-container'));
                
                $("#ks-product-report-seller").click(function(e){
                    /*reset form and its validation*/
                    var validator = $("#ks-report-seller-form").validate();
                    validator.resetForm();
                    $('#ks-report-seller-form').trigger("reset");
                    if (self.options.ksCustomerId==0 && self.options.ksAllowGuests==0) {
                        alert({
                            title: $.mage.__('Please login to report the seller'),
                            actions: {
                                always: function(){}
                            }
                        });
                        
                    } else{
                        $("#ks-report-seller-container").modal("openModal");
                    }
                });
                
            });
        }
    });
    return $.multivendor.ksReportSeller;
});