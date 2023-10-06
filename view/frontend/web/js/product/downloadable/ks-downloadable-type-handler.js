/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves Team
 * @copyright Copyright (c) Ksolves India Limited (https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */
/*jshint browser:true jquery:true expr:true*/
define([
    'jquery',
    'Ksolves_MultivendorMarketplace/js/product/downloadable/ks-weight-handler',
    'Ksolves_MultivendorMarketplace/js/product/ks-type-events'
], function ($, weight, productType) {
    'use strict';

    return {
        $checkbox: $('[data-action=change-type-product-downloadable]'),
        $items: $('#product_info_tabs_downloadable_items'),
        isDownloadable: false,

        /**
         * Show
         */
        show: function () {
            this.$checkbox.prop('checked', true);
            this.$items.show();
        },

        /**
         * Hide
         */
        hide: function () {
            this.$checkbox.prop('checked', false);
            this.$items.hide();
        },

        /**
         * Constructor component
         * @param {Object} data - this backend data
         */
        'Ksolves_MultivendorMarketplace/js/product/downloadable/ks-downloadable-type-handler': function (data) {
            this.isDownloadable = data.isDownloadable;
            this.bindAll();
            this._initType();
        },

        /**
         * Bind all
         */
        bindAll: function () {
            this.$checkbox.on('change', function (event) {
                $(document).trigger('setTypeProduct', $(event.target).prop('checked') ?
                    'downloadable' :
                    productType.type.init === 'downloadable' ? 'virtual' : productType.type.init
                );
            });

            $(document).on('changeTypeProduct', this._initType.bind(this));
        },

        /**
         * Init type
         * @private
         */
        _initType: function () {
            if (productType.type.current === 'downloadable') {
                weight.change(false);
                weight.$weightSwitcher().one('change', function () {
                    $(document).trigger(
                        'setTypeProduct',
                        productType.type.init === 'downloadable' ? 'virtual' : productType.type.init
                    );
                });
                this.show();
            } else {
                this.hide();
            }
        }
    };
});
