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
        'mage/url',
        'Magento_Catalog/js/price-utils'
    ],
    function($, uiRegistry, Chart,  url, priceUtils) {
        'use strict';
        
        $('body').on('change', ' .ks-tax :input[type=radio],  input[name="ksTax"], input[name="ksPrice"]', function(e) {
          var ksPriceFormat = {
              decimalSymbol: '.',
              groupLength: 3,
              groupSymbol: ",",
              integerRequired: false,
              pattern: window.currency.concat("%s"),
              precision: 2,
              requiredPrecision: 2
          };
          var ksPrice = document.getElementsByClassName('ksPrice')[0].value;
          var ksTaxType, ksGrandTotalValue;
          var ksUrl = window.location.href;
          if($('.ks-tax :input[type=radio]:unchecked').val() == 'percent') {
            $("#ks-doller-button.ks-tax").addClass('ks-selected-radio');
            $("#ks-percent-button.ks-tax").removeClass('ks-selected-radio');
          } else {
            $("#ks-doller-button.ks-tax").removeClass('ks-selected-radio');
            $("#ks-percent-button.ks-tax").addClass('ks-selected-radio');
          }
          if (ksUrl.includes('edit')) {
            var ksProductId = $("input[name='id']").val();
          } else {
            var ksProductId = null;
          }
          var ksTax = document.getElementsByClassName('ksTax')[0].value;
          if(ksTax) {
            if (document.getElementsByClassName('ks_tax')[0].checked == true) {
              ksTaxType = 'fixed';
              ksGrandTotalValue = parseFloat(ksPrice) + parseFloat(ksTax);
            } else {
              ksGrandTotalValue =  parseFloat(ksPrice) + (parseFloat(ksPrice)*parseFloat(ksTax))/100;
              ksTaxType = 'percent';
            } 
          } else {
            ksGrandTotalValue = parseFloat(ksPrice);
          }
          
          var ksCommissionValue = document.getElementsByClassName('ks_commission_value')[0].value;
          var ksCalculationBaseOn = document.getElementsByClassName('ks_calculation_base_on')[0].value;
          document.getElementsByClassName('ksGrandTotal')[0].value = priceUtils.formatPrice(parseFloat(ksGrandTotalValue.toFixed(2)), ksPriceFormat);
          if (ksTaxType == 'percent' && ksTax > 100) {
            ksTax = 100;
          }
          $.ajax({
                type: "POST",
                url: url.build("multivendor/product_ajax/KsEarningCalculator"),
                data: {
                    form_key: window.FORM_KEY,
                    ks_calculation_baseon: ksCalculationBaseOn,
                    ksProductId: ksProductId,
                    ksPrice: ksPrice,
                    ksTax: ksTax,
                    ksTaxType: ksTaxType,
                    ksProductForm: $('#ks_product_form').serializeArray()
                },
                success: function(ksResponse) {
                  var ksCommissionCost;
                  document.getElementsByClassName('ksAppliedPrice')[0].value = priceUtils.formatPrice(ksResponse.appliedprice.toFixed(2), ksPriceFormat);
                  if($('.ksCommissionValue :input[type=radio]:unchecked').val() == 'percent') {
                    ksCommissionCost = (ksCommissionValue);
                  } else {
                    ksCommissionCost = (ksResponse.appliedprice*ksCommissionValue)/100;
                  }
                  
                  document.getElementsByClassName('ks_commission_cost')[0].value = priceUtils.formatPrice(parseFloat(parseFloat(ksCommissionCost).toFixed(2)), ksPriceFormat);
                  document.getElementsByClassName('ks_earning')[0].value = priceUtils.formatPrice(parseFloat((ksGrandTotalValue - ksCommissionCost).toFixed(2)), ksPriceFormat);
                  window.config.data.datasets.forEach(function(dataset) {
                    dataset.data = [Number(document.getElementsByClassName('ks_earning')[0].value.replace(/[^0-9.-]+/g,"")), ksCommissionCost]
                  });
                  window.myPie.update();
                  $('.ks_cost').val(Number($('.ks_commission_cost').val().replace(/[^0-9.-]+/g,"")));
                  $('.ks_your_earning').val(Number($('.ks_earning').val().replace(/[^0-9.-]+/g,"")));
                }
        });
    });
});
