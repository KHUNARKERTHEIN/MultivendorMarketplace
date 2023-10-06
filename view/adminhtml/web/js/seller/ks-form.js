/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves Team
 * @copyright Copyright (c) Ksolves India Limited (https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */

/**
 * @api
 */
define([
    'jquery',
    'uiRegistry',
    'Magento_Ui/js/form/form',
    'mage/url',
], function ($, uiRegistry, form, url) {
    'use strict';

    return form.extend({

        /**
         * Validate and save form.
         *
         * @param {String} redirect
         * @param {Object} data
         */
        save: function (redirect, data) { 
            
            var ksExistCustomer = uiRegistry.get('index= ks_seller_id').value();
            
            if(ksExistCustomer=="" && $('[data-index="ks_new_customer_fieldset"]').css('display') == 'none'){
                $(".ks-check-customer").addClass('admin__field-error');
                $(".ks-check-customer").html("<p>Please select customer or create new one!.</p>");
                return false;
            }

            this.validate();
            return this._super();
        },

        /**
         * Reset form.
         */
        reset: function () {
            $("[data-index='ks_owner_fieldset']").css("display","none");
            $(".ks-available, .ks-check-customer").html(" ");
            $(".ks-check-customer, .ks-available").removeClass('admin__field-error');
            return this._super();
        }
    });
});
