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
    'Ksolves_MultivendorMarketplace/js/product/ks-type-events',
    'Ksolves_MultivendorMarketplace/js/product/ks-price-handler',
    'Ksolves_MultivendorMarketplace/js/product/ks-qty-handler',
    'Ksolves_MultivendorMarketplace/js/product/variations/options/ks-price-type-handler',
    'collapsible',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'domReady!'
], function ($, productType, ksprice, ksQty, ksPriceTypeHandler) {
    'use strict';

    return {
        $block: null,
        hasVariations: null,
        configurationSectionMessageHandler: (function () {
             var ksTitle = $('[data-role="product-create-configuration-info"]'),
                ksButtons = $('[data-action="product-create-configuration-buttons"]'),
                ksNewText = 'Configurations cannot be created for a standard product with downloadable files.' +
                 ' To create configurations, first remove all downloadable files.',
                ksOldText = ksTitle.text(),
                ksVariationElement = $('[data-role="product-variations-matrix"]');

            return function (change) {
                if (change) {
                    ksTitle.text(ksNewText);
                    ksButtons.hide();
                    ksVariationElement.hide();
                } else {
                    ksTitle.text(ksOldText);
                    ksButtons.show();
                    ksVariationElement.show();
                }
            };
        }()),

        /**
         * Show
         */
        show: function () {
            this.configurationSectionMessageHandler(false);
        },

        /**
         * Hide
         */
        hide: function () {
            this.configurationSectionMessageHandler(true);
        },

        /**
         * Bind all
         */
        bindAll: function () {
            $(document).on('changeConfigurableTypeProduct', function (event, isConfigurable) {
                $(document).trigger('setTypeProduct', isConfigurable ?
                    'configurable' :
                    productType.type.init === 'configurable' ? 'simple' : productType.type.init
                );
            });
            $(document).on('changeTypeProduct', this._initType.bind(this));
        },

        /**
         * Init type
         * @private
         */
        _initType: function () {
            if (['simple', 'virtual', 'configurable'].indexOf(productType.type.current) < 0) {
                this.hide();
            } else {
                this.show();
            }

            if (productType.type.current == 'configurable') {
                $('.ks-sources-container').closest('.ks-form-collapsible-block').hide();
            } else {
                $('.ks-sources-container').closest('.ks-form-collapsible-block').show();
            }

            ksprice.init();
            ksQty.init();
            ksPriceTypeHandler.init();
        },

        /**
         * Constructor component
         * @param {Object} data - this backend data
         */
        'Ksolves_MultivendorMarketplace/js/product/ks-configurable-type-handler': function (data) {
            this.$block = $(data.blockId + ' input[name="attributes[]"]');
            this.hasVariations = data.hasVariations;
            $(document).trigger('setTypeProduct', 'simple');

            this.bindAll();
            this._initType();
        }
    };
});
