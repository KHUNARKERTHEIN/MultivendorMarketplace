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
    "Magento_Ui/js/modal/modal",
    "mage/url",
    "mage/translate",
    "uiRegistry",
    "ko",
    "Magento_Ui/js/modal/confirm"
    ], function($, modal, url ,$t, uiRegistry, ko, confirmation) {

        // Seller Id
        var ksSellerId = 0;
        // Variable for the Assign Slider
        var ksAssignOptions = {
            type: 'slide',
            responsive: true,
            innerScroll: true,
            modalClass: "ks_assign_product_grid",
            title: $t('Assign Product'),
            closed: function(e) {
                ko.cleanNode($(".ks-assign-product-list")[0]);
                $(".ks-assign-product-list").html(""); 
            },
            closeText: $.mage.__('Close'),
            buttons: [{
                text: $t('Cancel'),
                class: 'action-secondary ks_close_first_slider',
                click: function (event) {
                    this.closeModal();
                }
            },],
        };
        // Event When Outer Assign Product Button is clicked
        $('body').on('click','.ks_admin_assign_product',function() {
            var ksElement = $('.ks-seller-assign-tab .admin__control-text');
            if (ksElement.attr('name') == 'ks_assign_admin_product_tab[ks_seller_id]') {
                ksSellerId = ksElement.val();
            }
            $('.ks-assign-product-list').modal(ksAssignOptions).modal('openModal');
            ksOpenSlider();
        });
        // Function for Adding comments in the Sliders
        function ksWaitForClassToLoad(ksTargetPath, callBack){
            window.setTimeout(function() {
                if($(ksTargetPath).length){
                    callBack(ksTargetPath, $(ksTargetPath));
                } else {
                    ksWaitForClassToLoad(ksTargetPath, callBack);
                }
            },500)
        }

        var ksChildProduct = [];
        // Variable for Assign Product Details Slider
        var ksOptions = {
            type: 'slide',
            responsive: true,
            innerScroll: true,
            modalClass: "ks_assign_product_grid",
            title: $.mage.__("Assign Product"),
            closed: function() {
                ko.cleanNode($(".ks_product_to_be_assigned")[0]);
                $(".ks_product_to_be_assigned").html("");
                ksOpenSlider();
            },
            buttons: [{
                text: $t('Cancel'),
                class: 'action-secondary',
                click: function (event) {
                    $("#ks_assign_product_details").modal("closeModal");
                }
            },
            {
                text: $.mage.__('Confirm'),
                class: 'action-primary',
                click: function () {
                    // Get the id of product that are not associated
                    var ksNotLink = $('.ks_delinked_products');
                    if (ksNotLink) {
                        ksNotLink.each(function(){
                            ksChildProduct.push($(this).data('id'));
                        });
                    }
                    $.ajax({
                        type: "POST",
                        url: url.build("multivendor/assignproduct/assign"),
                        showLoader : true,
                        data: {
                            form_key: window.FORM_KEY,
                            entity_id: $(".ks_assign_product_id").val(),
                            seller_id: $('.ks_assign_product_seller_id').val(),
                            child_ids: ksChildProduct
                        },
                        success: function (ksResponse) { 
                            location.reload();
                        }
                    });
                }
            }]
        };
        // Event When Inner Assign button is clicked
        $('body').on('click','.ks_assign_product',function(){
            $.ajax({
                type: "POST",
                url: url.build("multivendor/assignproduct/assignproductdetails"),
                showLoader: true,
                data: {
                    form_key: window.FORM_KEY,
                    ks_assign_product_id: $(this).data('id'),
                    ks_seller_id: $(this).data('seller_id'),
                },
                success: function (ksResponse) { 
                    uiRegistry.get(function(component) {
                        if (component.name != undefined) {
                            uiRegistry.remove(component.name);
                        }
                    });
                    ko.cleanNode($('.ks_product_to_be_assigned')[0]);
                    $(".ks_product_to_be_assigned").html(ksResponse);
                    $(".ks_product_to_be_assigned").trigger('contentUpdated');
                    $(".ks_product_to_be_assigned").applyBindings();
                }
            });
            modal(ksOptions, $('#ks_assign_product_details'));
            $("#ks_assign_product_details").modal("openModal");
            $('.ks_assign_product_seller_id').val($(this).data('seller_id'));
            $('.ks_assign_product_id').val($(this).data('id'));

            //Adding Notes in the Grid
            var ksNote = "#ks_assign_product_details .fieldset-wrapper-title";
            $(ksNote).ready(function(){
                let ksNoteText = "<strong class='ks-assign-second-slider'>"+$t('These products will be delinked from the assigned product')+"</strong>";
                let ksRelatedNote = "<div class='ks-related-products'>"+$t('Related products are shown to customers in addition to the item the customer is looking at.')+"</div>";
                let ksUpSellNote = "<div class='ks-related-products'>"+$t('An up-sell item is offered to the customer as a pricier or higher-quality alternative to the product the customer is looking at.')+"</div>";
                let ksCrossNote = "<div class='ks-related-products'>"+$t("These 'impulse-buy' products appear next to the shopping cart as cross-sells to the items already in the shopping cart.")+"</div>";
                ksWaitForClassToLoad(ksNote, function() {
                    $("#ks_assign_product_details > form > div > div > div.entry-edit.form-inline > div:nth-child(4) > div.fieldset-wrapper-title").append(ksNoteText);
                    $("#ks_assign_product_details > form > div > div > div.entry-edit.form-inline > div:nth-child(3) > div.fieldset-wrapper-title").append(ksNoteText);
                    $("#ks_assign_product_details > form > div > div > div.entry-edit.form-inline > div:nth-child(4) > div.admin__fieldset-wrapper-content._hide > fieldset > div:nth-child(1) > div.fieldset-wrapper-title").append(ksRelatedNote);
                    $("#ks_assign_product_details > form > div > div > div.entry-edit.form-inline > div:nth-child(4) > div.admin__fieldset-wrapper-content._hide > fieldset > div:nth-child(2) > div.fieldset-wrapper-title").append(ksUpSellNote);
                    $("#ks_assign_product_details > form > div > div > div.entry-edit.form-inline > div:nth-child(4) > div.admin__fieldset-wrapper-content._hide > fieldset > div:nth-child(3) > div.fieldset-wrapper-title").append(ksCrossNote);
                });
            });
        });

        // To get the Delink data of configurable, bundle and grouped product
        $('body').on('click','.ks_link_delink_button',function(){
            if ($(this).html() == 'Delink') {
                $(this).html('Link');
                ksChildProduct.push($(this).data('id'));
            } else {
                $(this).html('Delink');
                var ksIn = ksChildProduct.indexOf($(this).data('id'));
                if (ksIn >= 0) {
                    ksChildProduct.splice(ksIn, 1);
                }
            }
        });

        // Confirmation Pop up for Remove Product
        $('body').on('click','.ks-assign-product-remove',function(e){
            e.preventDefault();
            var ksUrl = e.currentTarget.href;
            confirmation({
                title: $.mage.__("Remove Product"),
                content: $.mage.__('Are you sure you want to remove this product from seller?<br>Note - All the associated, related, upsell and cross-sell products will be delinked from the removal product.'),
                actions: {
                  confirm: function(){
                        $("body").trigger('processStart');
                        window.location.href = ksUrl;
                    },
                    cancel: function(){
                    },
                }
            });
        });

        function ksOpenSlider() 
        {
            $.ajax({
                type: "POST",
                url: url.build("multivendor/assignproduct/assignproductlist"),
                showLoader: true,
                data: {
                    form_key: window.FORM_KEY,
                    ks_seller_id: ksSellerId
                },
                success: function(ksResponse) {

                    uiRegistry.get(function(component) {

                        if (component.name != undefined) {
                            if (component.name.indexOf('ks_marketplace_admin_product_listing') != -1) {
                                uiRegistry.remove(component.name);
                            }
                        }
                    });

                    ko.cleanNode($('.ks-assign-product-list')[0]);
                    $('.ks-assign-product-list').html(ksResponse);
                    $(".ks-assign-product-list").trigger('contentUpdated');
                    $('.ks-assign-product-list').applyBindings();
                    var ksPath = '#ks-assign-product-list > div > div.admin__data-grid-header';
                    var ksAssignNote = "<div class='ks_assign_product_grid_notes'>"+$t('Note - This assign product list is based on product type and attribute set allowed Seller-wise and in the Marketplace.')+"</div>"
                    // Invoking the Wait function
                    ksWaitForClassToLoad(ksPath, function() {
                        $('.ks_assign_product_grid_notes').remove();
                        $(ksPath).
                            after(ksAssignNote);
                    });
                }
            });
        }
    });