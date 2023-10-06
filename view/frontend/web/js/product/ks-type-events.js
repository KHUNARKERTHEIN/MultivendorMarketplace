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
], function ($) {
    'use strict';

    return {
        $type: $('input[name="ks_product_type'),

        /**
         * Init
         */
        init: function () { 
            this.type = {
                init: this.$type.val(),
                current: this.$type.val()
            };

            this.bindAll();
        },

        /**
         * Bind all
         */
        bindAll: function () { 
            $(document).on('setTypeProduct', function (event, type) { 
                this.setType(type);
            }.bind(this)); 

            //direct change type input
            this.$type.on('change', function () {
                this.type.current = this.$type.val();
                this._notifyType();
            }.bind(this));
        },

        /**
         * Set type
         * @param {String} type - type product (downloadable, simple, virtual ...)
         * @returns {*}
         */
        setType: function (type) { 
            return this.$type.val(type || this.type.init).trigger('change');
        },

        /**
         * Notify type
         * @private
         */
        _notifyType: function () {
            $(document).trigger('changeTypeProduct', this.type);
        }
    };
});
