/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves Team
 * @copyright Copyright (c) Ksolves India Limited (https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */
require([
        'jquery',
        'uiRegistry',
        'chartJs',
        'Magento_Catalog/js/price-utils'
    ],
    function($, uiRegistry, Chart, priceUtils) {
        'use strict';

        $('body').on('change', ' .ks_calculator_discount :input[type=radio], .ks_commission_value_calculator :input[type=radio], .ks_calculator_tax :input[type=radio]', function(e) {
            var ksPriceFormat = {
                decimalSymbol: '.',
                groupLength: 3,
                groupSymbol: ",",
                integerRequired: false,
                pattern: window.currency.concat("%s"),
                precision: 2,
                requiredPrecision: 2
            };
            var ksTaxtype = $(".ks_calculator_tax input[type='radio']:checked").val();
            var ksDiscountType = $(".ks_calculator_discount input[type='radio']:checked").val();
            var ksCommissionType = $(".ks_commission_value_calculator input[type='radio']:checked").val();
            var ksId = this.id;
            if ($("." + ksId + " input[type='radio']:checked").val() == 'fixed') {
                $("#ks-doller-button." + ksId).addClass('ks-selected-radio');
                $("#ks-percent-button." + ksId).removeClass('ks-selected-radio');
            } else {
                $("#ks-doller-button." + this.parentElement.classList[0]).removeClass('ks-selected-radio');
                $("#ks-percent-button." + this.parentElement.classList[0]).addClass('ks-selected-radio');
            }
            if (ksId == 'ks_calculator_discount' || ksId == 'Discount') {
                uiRegistry.get("index = ks_calculator_discount", function(input) {
                    if ($(".ks_calculator_discount input[type='radio']:checked").val() == 'percent') {
                        input.validation['validate-digits-range'] = '0-100';

                    } else {
                        input.validation['validate-digits-range'] = '';
                    }
                }.bind(this));
            } else if (ksId == 'ks_commission_value_calculator' || ksId == 'Commission Value') {
                uiRegistry.get("index = ks_commission_value_calculator", function(input) {
                    if ($(".ks_commission_value_calculator input[type='radio']:checked").val() == 'percent') {
                        input.validation['validate-digits-range'] = '0-100';
                    } else {
                        input.validation['validate-digits-range'] = '';
                    }
                }.bind(this));
            } else if (ksId == 'ks_calculator_tax'  || ksId == 'Tax') {
                uiRegistry.get("index = ks_calculator_tax", function(input) {
                    if ($(".ks_calculator_tax input[type='radio']:checked").val() == 'percent') {
                        input.validation['validate-digits-range'] = '0-100';
                    } else {
                        input.validation['validate-digits-range'] = '';
                    }
                }.bind(this));
            }

        });
        $('body').on('keypress change', ' .ks_calculator_tax :input[type=radio], .ks_calculator_discount :input[type=radio], .ks_commission_value_calculator :input[type=radio], input[name="ks_calculator_tax"], select[name="ks_calculation_baseon_calculator"], input[name="ks_calculator_discount"], input[name="ks_product_price"]', function(e) {
            var ksPriceFormat = {
                decimalSymbol: '.',
                groupLength: 3,
                groupSymbol: ",",
                integerRequired: false,
                pattern: window.currency.concat("%s"),
                precision: 2,
                requiredPrecision: 2
            };
            var ksPrice = uiRegistry.get('index=ks_product_price');
            var ksDiscount = uiRegistry.get('index=ks_calculator_discount');
            var ksTax = uiRegistry.get('index=ks_calculator_tax');
            var ksAppliedField = uiRegistry.get('index=ks_applied_price');
            var ksCommissionValue = uiRegistry.get('index=ks_commission_value_calculator');
            var ksCalculationBaseOn = uiRegistry.get('index=ks_calculation_baseon_calculator');
            var ksElement;
            var ksTaxtype = $(".ks_calculator_tax input[type='radio']:checked").val();
            var ksDiscountType = $(".ks_calculator_discount input[type='radio']:checked").val();
            var ksCommissionType = $(".ks_commission_value_calculator input[type='radio']:checked").val();
            var ksCommissionCost = uiRegistry.get('index=ks_commission_cost');
            var ksSellerEarning = uiRegistry.get('index=ks_seller_earning');
            var ksGrandTotal = uiRegistry.get("index = ks_calculator_grandtotal");

            if (this.name == 'ks_product_price') { 
                ksElement = ksPrice;
                if(ksPrice.value()==""){
                    ksTax.value("");
                    ksDiscount.value("");
                    ksCommissionValue.value("");
                    ksCommissionCost.value("");
                    ksSellerEarning.value("");
                    ksGrandTotal.value("");
                }
            } else if (this.name == 'ks_calculator_tax') {
                ksElement = ksTax;
            } else {
                ksElement = ksDiscount;
            }
            var ksTotal = 0,
                ksApplied = 0;
            if (ksElement.validate().valid == true) {
                uiRegistry.get("index = ks_calculator_grandtotal", function(input) {
                    if (ksPrice.value() != '') {
                        ksApplied = ksApplied + parseFloat(ksPrice.value());
                        ksTotal = ksTotal + parseFloat(ksPrice.value());
                    }
                    if (ksTax.value() != '') {
                        if (ksTaxtype == 'percent') {
                            if (ksCalculationBaseOn.value() == 1 || ksCalculationBaseOn.value() == 3) {
                                ksApplied = ksApplied + (parseFloat(ksPrice.value()) * parseFloat(ksTax.value())) / 100;
                            }
                            ksTotal = ksTotal + (parseFloat(ksPrice.value()) * parseFloat(ksTax.value())) / 100;
                        } else {
                            if (ksCalculationBaseOn.value() == 1 || ksCalculationBaseOn.value() == 3) {
                                ksApplied = ksApplied + parseFloat(ksTax.value());
                            }
                            ksTotal = ksTotal + parseFloat(ksTax.value());
                        }

                    }
                    if (ksDiscount.value() != '') {
                        if (ksDiscountType == 'percent') {
                            if (ksCalculationBaseOn.value() == 4 || ksCalculationBaseOn.value() == 3) {
                                ksApplied = ksApplied - (ksApplied * parseFloat(ksDiscount.value())) / 100;
                            }
                            ksTotal = ksTotal - (ksTotal * parseFloat(ksDiscount.value())) / 100;
                        } else {
                            if (ksCalculationBaseOn.value() == 4 || ksCalculationBaseOn.value() == 3) {
                                ksApplied = ksApplied - parseFloat(ksDiscount.value());
                            }
                            ksTotal = ksTotal - parseFloat(ksDiscount.value());
                        }
                    }
                    if( ksTotal < 0 ){
                        ksTotal = 0;
                    }
                    input.value(priceUtils.formatPrice(parseFloat(ksTotal.toFixed(3)), ksPriceFormat));
                }.bind(this));
                ksAppliedField.value(priceUtils.formatPrice(parseFloat(ksApplied.toFixed(3)), ksPriceFormat));
                if(ksCommissionValue.value() != ''){    
                    if (ksCommissionType == 'percent') {
                        var ksComCost = (Number(ksAppliedField.value().replace(/[^0-9.-]+/g,"")) * ksCommissionValue.value()) / 100;
                        ksCommissionCost.value(priceUtils.formatPrice(parseFloat(ksComCost.toFixed(3)), ksPriceFormat));
                    } else {
                        var ksComCost = parseFloat(ksCommissionValue.value());
                        ksCommissionCost.value(priceUtils.formatPrice(parseFloat(ksComCost.toFixed(3)), ksPriceFormat));
                    }
                    
                    var ksEarn = parseFloat(Number(ksGrandTotal.value().replace(/[^0-9.-]+/g,"")) - (Number(ksCommissionCost.value().replace(/[^0-9.-]+/g,""))));
                    if(ksEarn < 0){
                        ksEarn = 0;
                    }
                    ksSellerEarning.value(priceUtils.formatPrice(parseFloat(ksEarn.toFixed(3)), ksPriceFormat));
                }

                window.config.data.datasets.forEach(function(dataset) {
                    dataset.data = [Number(ksSellerEarning.value().replace(/[^0-9.-]+/g,"")), Number(ksCommissionCost.value().replace(/[^0-9.-]+/g,""))]
                });
                window.myPie.update();
            }
        });

        $('body').on('keypress change', 'input[name="ks_commission_value_calculator"]', function(e) {
            var ksPriceFormat = {
                decimalSymbol: '.',
                groupLength: 3,
                groupSymbol: ",",
                integerRequired: false,
                pattern: window.currency.concat("%s"),
                precision: 2,
                requiredPrecision: 2
            };
            var ksAppliedField = uiRegistry.get('index=ks_applied_price');
            var ksCommissionCost = uiRegistry.get('index=ks_commission_cost');
            var ksSellerEarning = uiRegistry.get('index=ks_seller_earning');
            var ksCommissionValue = uiRegistry.get('index=ks_commission_value_calculator');
            var ksCommissionType = $(".ks_commission_value_calculator input[type='radio']:checked").val();
            var ksGrandTotal = uiRegistry.get("index = ks_calculator_grandtotal");
            if(ksCommissionValue.value() != ''){
                if (ksCommissionType == 'percent') {
                    var ksComCost = (Number(ksAppliedField.value().replace(/[^0-9.-]+/g,"")) * ksCommissionValue.value()) / 100;
                    ksCommissionCost.value(priceUtils.formatPrice(parseFloat(ksComCost.toFixed(3)), ksPriceFormat));
                } else {
                    var ksComCost = parseFloat(ksCommissionValue.value());
                    ksCommissionCost.value(priceUtils.formatPrice(parseFloat(ksComCost.toFixed(3)), ksPriceFormat));
                }
                var ksEarn = parseFloat(Number(ksGrandTotal.value().replace(/[^0-9.-]+/g,"")) - Number(ksCommissionCost.value().replace(/[^0-9.-]+/g,"")));
                if(ksEarn < 0){
                    ksEarn = 0;
                }
                ksSellerEarning.value(priceUtils.formatPrice(parseFloat(ksEarn.toFixed(3)), ksPriceFormat));
            }
            else{
                ksCommissionCost.value("");
                ksSellerEarning.value("");
            }
            window.config.data.datasets.forEach(function(dataset) {
                dataset.data = [Number(ksSellerEarning.value().replace(/[^0-9.-]+/g,"")), Number(ksCommissionCost.value().replace(/[^0-9.-]+/g,""))]
            });
            window.myPie.update();
        });
        setTimeout(function() {
            $("#ks-doller-button.ks_calculator_tax").addClass('ks-selected-radio');
            $("#ks-doller-button.ks_calculator_discount").addClass('ks-selected-radio');
            $("#ks-doller-button.ks_commission_value_calculator").addClass('ks-selected-radio');
        }, 2500);
    });
