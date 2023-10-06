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
    'uiRegistry',
    'uiElement',
    'Magento_Ui/js/form/element/abstract',
    'Magento_Catalog/js/price-utils',
    "mage/url",
], function($, uiRegistry, uiElement, Abstract, priceUtils, url) {

    'use strict';

    return Abstract.extend({
        onUpdate: function() {
            this.bubble('update', this.hasChanged());
            this.validate();
            if (this.validate().valid == true) {
                if ( typeof uiRegistry.get("index = ks_subtotal").value() == 'string' && isNaN(uiRegistry.get("index = ks_subtotal").value().charAt(0))) {
                    var ksPrice = Number(uiRegistry.get("index = ks_price").value().replace(/[^0-9.-]+/g,""));
                    var ksSubtotal = Number(uiRegistry.get("index = ks_subtotal").value().replace(/[^0-9.-]+/g,""));
                    var ksDiscount = Number(uiRegistry.get("index = ks_discount").value().replace(/[^0-9.-]+/g,""));
                } else {
                    var ksPrice = uiRegistry.get("index = ks_price").value();
                    var ksSubtotal = uiRegistry.get("index = ks_subtotal").value();
                    var ksDiscount = uiRegistry.get("index = ks_discount").value();
                }

                var ksQuantity = this.value();
                var ksInitialQty = ksSubtotal / ksPrice;
                var ksPriceFormat = {
                    decimalSymbol: '.',
                    groupLength: 3,
                    groupSymbol: ",",
                    integerRequired: false,
                    pattern: window.currency.concat("%s"),
                    precision: 2,
                    requiredPrecision: 2
                };
                var ksTaxRate = uiRegistry.get('index=ks_tax').value();
                if (!isNaN(ksInitialQty)) {
                    uiRegistry.get("index = ks_subtotal", function(input) {
                        var ksUnitSubtotal = Number(input.value().replace(/[^0-9.-]+/g,"")) / ksInitialQty;
                        var ksSub = priceUtils.formatPrice(ksUnitSubtotal * ksQuantity, ksPriceFormat); 
                        input.value(ksSub);
                    }.bind(this));
                    uiRegistry.get("index = ks_discount", function(input) {
                        var ksUnitDiscount = Number(input.value().replace(/[^0-9.-]+/g,"") / ksInitialQty);
                        var ksDis = priceUtils.formatPrice(ksUnitDiscount * ksQuantity, ksPriceFormat);
                        input.value(ksDis);
                    }.bind(this));
                    uiRegistry.get("index = ks_grandtotal", function(input) {
                        var ksUnitDiscount = ksDiscount / ksInitialQty;
                        var ksTax = (ksPrice - ksUnitDiscount) * ksTaxRate / 100;
                        var ksGrTotal = (ksPrice - ksUnitDiscount + ksTax) * ksQuantity;
                        var ksGr = priceUtils.formatPrice(parseFloat(ksGrTotal.toPrecision(3)), ksPriceFormat);
                        input.value(ksGr);
                    }.bind(this));
                } 
            }
        }
    });
});
