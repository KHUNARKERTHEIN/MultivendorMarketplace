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
    'jquery',
    'uiRegistry',
    'mage/url'
], function ($ , uiRegistry ,url) {
    'use strict';

      $('body').on('click',"#ks_new_seller",function(e){ 
          e.preventDefault();
          $(".ks-check-customer").html(" ");
          $(".ks-check-customer").removeClass('admin__field-error');
          uiRegistry.get('index = ks_seller_id').clear();
          $("[data-index='ks_owner_fieldset']").css("display","none");

          uiRegistry.get('ks_marketplace_seller_add.ks_marketplace_seller_add.ks_new_customer_fieldset').visible(true);
          $('[data-index="ks_new_customer_fieldset"] .ks-customer-fields').each(function(){
            var ksCustomerField = $(this).data('index');
            uiRegistry.get('index='+ksCustomerField).enable(true);

          });
      });

      $('body').on( 'focus', 'select,:input[name = "seller[ks_store_name]"]', function(){
          $(this).attr( 'autocomplete', 'off' );
      });

      $('body').on('change' ,'.ks-check-store-available input',function(e) { 
          e.stopPropagation();
          if (!this.value) {
            $(".ks-available").removeClass('success').removeClass('admin__field-error').html(" ");

          }
      });

      $('body').on('click',"#ks-check-availability",function(e){ 
          e.preventDefault();
          if(uiRegistry.get('index = ks_store_url').value()) {
              var ksSellerId = uiRegistry.get('index= ks_seller_id').value();

              var xhr = $.ajax({
                  type: "POST",
                  url: url.build("multivendor/seller_ajax/storeurlverify"),
                  async: false,
                  data: {
                      form_key: window.FORM_KEY,
                      ks_seller_store_url: uiRegistry.get('index = ks_store_url').value(),
                      ks_seller_id: ksSellerId
                  },
                  success: function (ksResponse) {
                      console.log(ksResponse.ks_message);
                  },
              }).done(function() {
                  xhr = null;
              });

              var data = JSON.parse(xhr.responseText)
              if (data.ks_message_type == 'success') {
                  $(".ks-available").removeClass('admin__field-error').addClass('success');
                  $(".ks-available").html("<p>"+data.ks_message+"</p>");

              } else if(data.ks_message_type == 'error'){
    
                  $(".ks-available").removeClass('success').addClass('admin__field-error');
                  $(".ks-available").html("<p>"+data.ks_message+"</p>");

              } else {
                  $(".ks-available").removeClass('success').removeClass('admin__field-error');
                  $(".ks-available").html("<p>"+data.ks_message+"</p>");
              }    
          }                      
      });
  });