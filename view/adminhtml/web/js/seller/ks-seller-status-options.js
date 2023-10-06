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
    'Magento_Ui/js/modal/modal',
], function (_, uiRegistry, select, modal) {
    'use strict';
    return select.extend({

        initialize: function () {
            this._super();

            this.ksDependentField(this.value());

            return this;
        },

        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {
            this.ksDependentField(value);

            return this._super();
        },

        /**
         * logic for show or hide dependent field
         *
         * @param {String} value
         */
        ksDependentField: function (value) {
            setTimeout(function() { 
                var ksRejectionField = uiRegistry.get('index = ks_rejection_reason');

                if (value == 2) {
                    ksRejectionField.show();
                    uiRegistry.get('index = ks_store_status').value(0);
                } else if(value == 0) {
                    ksRejectionField.hide();
                    uiRegistry.get('index = ks_store_status').value(0);
                } else {
                    ksRejectionField.hide();
                }
            }, 1);

            return this._super();
        }
    });
});
