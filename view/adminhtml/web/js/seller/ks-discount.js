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
   'underscore',
   'uiRegistry',
   'Magento_Ui/js/form/element/single-checkbox',
   'Magento_Ui/js/modal/modal',
   'ko'
], function (_, uiRegistry, select, modal, ko) {
   'use strict';
   return select.extend({

       initialize: function () {
           this._super();
           this.ksFieldDepend(this.value());
           return this;
       },

       onUpdate: function (ksValue)
       {
          // Get the Field Which we want to hide
          var ks_field_text_hide  =  uiRegistry.get('index = ks_discount_products_text');
          var ks_field_count_hide = uiRegistry.get('index = ks_discount_products_count');

          if (ksValue == 0) {
            ks_field_text_hide.hide();
            ks_field_count_hide.hide();
          } else {
            ks_field_text_hide.show();
            ks_field_count_hide.show();
          }
       },

       ksFieldDepend: function (ksValue)
       {
           setTimeout( function(){
              // Get the Field Which we want to hide
              var ks_field_text_hide  =  uiRegistry.get('index = ks_discount_products_text');
              var ks_field_count_hide = uiRegistry.get('index = ks_discount_products_count');

              if (ksValue == 0) {
                ks_field_text_hide.hide();
                ks_field_count_hide.hide();
              } else {
                ks_field_text_hide.show();
                ks_field_count_hide.show();
              }
          });
       }
   });
});