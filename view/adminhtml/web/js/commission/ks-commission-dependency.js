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
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/modal/modal'
], function(_, uiRegistry, select, modal) {
    'use strict';
    return select.extend({

        initialize: function() {
            var ksType = this._super().initialValue;
            setTimeout(function() {
                var ksSeller = uiRegistry.get('index = ks_seller_id');
                var ksSellerGroup = uiRegistry.get('index = ks_seller_group');
                if (ksType == 1) {
                    ksSeller.hide();
                    ksSellerGroup.show();
                } else {
                    ksSellerGroup.hide();
                    ksSeller.show();
                }
                return this;
            }, 250);
        },

        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function(value) {
            var ksSeller = uiRegistry.get('index = ks_seller_id');
            var ksSellerGroup = uiRegistry.get('index = ks_seller_group');
            if (value == 1) {
                ksSeller.hide();
                ksSellerGroup.show();
            } else {
                ksSellerGroup.hide();
                ksSeller.show();
            }
            return this._super();
        },
    });
});