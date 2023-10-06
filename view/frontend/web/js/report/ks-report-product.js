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
        $.widget('multivendor.ksReportProduct', {
            options: {},
            _create: function () {
                var self = this;

                $(document).ready(function () {
                    var popupOptions = {
                        title: self.options.ksReportHeading,
                        type: 'popup',
                        responsive: true,
                        innerScroll: true,
                        buttons: [{
                            text: 'Submit',
                            class: 'action',
                            click: function(){
                              $("#ks-report-product-form").submit();
                            }
                        }]
                      };
                    var reportPopup = modal(popupOptions, $('#ks-report-product-container'));
                });

                $("#ks-report-product").click(function(e){
                    /*reset form and its validation*/
                    var validator = $("#ks-report-product-form").validate();
                    validator.resetForm();
                    $('#ks-report-seller-form').trigger("reset");
                    if (self.options.ksCustomerId==0 && self.options.ksAllowGuests==0) {
                        alert({
                          title: $.mage.__('Please login to report the product'),
                          actions: {
                            always: function(){}
                          }
                        });

                    } else{
                        $("#ks-report-product-container").modal("openModal");
                    }
                });
                /*add event listener on reason dropdown*/
                $("#ks-report-product-reason").change(function(){
                  $('#ks-report-product-sub-reason').find('option').not(':first').remove();
                  if ($("#ks-report-product-reason").val()) {
                      $.ajax({
                          url: ksUrl.build('multivendor/reportproduct/kssubreasons'),
                          type: 'GET',
                          data:{reason:$("#ks-report-product-reason").val()},
                          showLoader: true,
                          success: function(response){
                              if (response.length>0) {
                                  response.forEach((subReason)=>{
                                      //subReasonOption =  `<option value=${subReason.id}>${subReason.ks_subreason}</option> `
                                      $('#ks-report-product-sub-reason')
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
            }
        });
    return $.multivendor.ksReportProduct;
});