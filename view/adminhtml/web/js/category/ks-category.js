
/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves Team
 * @copyright Copyright (c) Ksolves India Limited (https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */
require([
   'jquery'
   ], function ($) { 
      $(document).ready(function(){
         $(document).ajaxSuccess(function() {
            if($('#ks_is_requests_allowed').is(":checked")) {
               $('.ks-auto-approval-button').show();
            } else {
               $('.ks-auto-approval-button').hide();
            }
         });
         $('body').on('change','#ks_is_requests_allowed',function(){
            if($(this).is(":checked")) {
               $('.ks-auto-approval-button').show();
            } else {
               $('.ks-auto-approval-button').hide();
            }
         });
      });
      //add condition to add loader
      $('body').on('click','.ks-reload',function(){
         $("body").trigger('processStart');
      });
});
