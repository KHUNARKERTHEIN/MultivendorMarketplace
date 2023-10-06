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
   'underscore',
   'uiRegistry',
   'Magento_Ui/js/form/element/single-checkbox',
   'Magento_Ui/js/modal/modal',
   'ko'
], function ($,_, uiRegistry, select, modal, ko) {
   'use strict';
   return select.extend({

       initialize: function () {
           this._super();
           return this;
       },

       onUpdate: function (ksValue)
       {
          // Get the Field Which we want to hide
          var ks_field_banner = $('.ks-admin-banner');

          if (ksValue == 0) {
            ks_field_banner.hide();
          } else {
            ks_field_banner.show();
          }
       },
   });
});