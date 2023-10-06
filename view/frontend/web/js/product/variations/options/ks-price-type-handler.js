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
    'Ksolves_MultivendorMarketplace/js/product/ks-type-events'
], function ($, productType) {
    'use strict';

    return {
        isConfigurable: false,
        isPercentPriceTypeExist: function () {
            var productOptionsContainer = $('#ks_product_options_container_top');

            return !!productOptionsContainer.length;
        },

        /**
         * init
         */
        init: function () {
            $(document).on('changeTypeProduct', this._initType.bind(this));

            this._initType();
        },

        /**
         * initType
         */
        _initType: function () {
            this.isConfigurable = productType.type.current === 'configurable';
            if (this.isPercentPriceTypeExist()) {
                this.percentPriceTypeHandler();
            }
        },

        /**
         * hide prooduct percent
         */
        percentPriceTypeHandler: function () {
            var priceType = $('[data-attr="price-type"]'),
                optionPercentPriceType = priceType.find('option[value="percent"]'),
                priceSymbol = $('.product-option-price').data("price-sysmbol"),
                ksMessage = $("#ks-configurable-product-warning");

            if (this.isConfigurable) {
                ksMessage.show();
                optionPercentPriceType.hide();
                optionPercentPriceType.parent().val() === 'percent' ? optionPercentPriceType.parent().val('fixed') : '';
                $('.ks-dollor-sign span').text(priceSymbol);
            } else {
                optionPercentPriceType.show();
                ksMessage.hide();
            }
        }
    };
});

