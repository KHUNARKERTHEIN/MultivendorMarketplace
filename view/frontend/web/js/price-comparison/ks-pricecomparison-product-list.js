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
        "jquery",
        'Magento_Ui/js/modal/modal',
        "mage/url",
        "mage/translate"
    ], function($, modal, url ,$t) { 

        $(document).ready(function() {
            // function to load price comparison table
            ksPriceComparisonProducts();
    
            // sort price comparison product list table according to price
            var ksValue = 1;
            $('body').on('click',".ks-sort-direction",function(e){ 
                e.preventDefault();
                ksValue *= -1;
                var n = 5;
                ksSortTable(ksValue,n);
            });

            // qty substract button
            $('body').on('click',".ks-subtract-Qty",function(e){ 
                e.preventDefault();
                if (parseInt($(this).next(".ks-qty").val()) !=0) {
                    var value = parseInt($(this).next(".ks-qty").val())- parseInt(1); 
                    $(this).next(".ks-qty").val(value);
                }
            });

            // qty add button
            $('body').on('click',".ks-add-Qty",function(e){ 
                e.preventDefault();
                var value = parseInt($(this).prev(".ks-qty").val())+ parseInt(1); 
                $(this).prev(".ks-qty").val(value);       
            });

            // open view images popup
            $('body').on('click',".ks-price-table-view-img",function(e){
                e.preventDefault();

                var options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    modalClass: 'popup-modal',
                    clickableOverlay: true,
                    modalcloseBtn: true,
                    title: 'Product Images',
                    buttons: []
                };
                
                var popup = modal(options, $('#ks-product-images-slider'));
                $("#ks-product-images-slider").modal(options).modal('openModal');
            });
        });

        // get price comparison product table 
        function ksPriceComparisonProducts () {
            var ksProductData = $('#product_addtocart_form').serialize();
            $.ajax({
            type: "POST",
            url: url.build("multivendor/pricecomparison/pricecomparisonproductlist"),
            data: ksProductData,
            showLoader: true,
            success: function(ksData){
                $('.product.media').after('<div>'+ksData+'</div>')
                console.log(ksData);
            }
          });
        }

        // sort table function
        function ksSortTable(f,n){
            var ksRows = $('#ks_price_comparsion_list tbody  tr').get();

            ksRows.sort(function(a, b) {
                var A = getKsVal(a);
                var B = getKsVal(b);

                if(A < B) {
                    return -1*f;
                }
                if(A > B) {
                    return 1*f;
                }
                return 0;
            });

            // get values
            function getKsVal(ksElm){
                var ksValue = $(ksElm).children('td').eq(n).text().toUpperCase();
                var ksPrice = Number(ksValue.replace(/[^0-9\.-]+/g,""));
                if($.isNumeric(ksPrice)){
                    ksValue = parseInt(ksPrice,10);
                }
                return ksValue;
            }

            $.each(ksRows, function(index, row) {
                $('#ks_price_comparsion_list').children('tbody').append(row);
            });
        }

    });
