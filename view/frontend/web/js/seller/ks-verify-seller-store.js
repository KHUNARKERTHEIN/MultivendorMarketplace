/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves Team
 * @copyright Copyright (c) Ksolves India Limited (https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */
/*jshint browser:true jquery:true*/

define([
    "jquery",
    'mage/translate',
    'mage/template',
    'Magento_Ui/js/modal/alert',
    "jquery/ui",
    'jquery/validate'
], function ($, $t, mageTemplate, alert, Validation) {
    'use strict';
    $.widget('mage.verifySellerStore', {
        options: {
            ksSellerStoreName: '#ks_seller_store_name',
            ksSellerStoreUrl: '#ks_seller_store_url',
            ksCheckStoreAvailable: '.ks-check-store-available',
            ksPageLoader: '#ks-load',
            ksBecomeSellerClass: '.ks-become-seller',
            ksSellerStoreClass: '.ks-seller-store',
            ksSellerStoreUrlClass: '.ks-seller-store-url'
        },
        _create: function () {
            var self = this;
            // create custom validation method
            $.validator.addMethod('validate-store-url', function (value) {
                if($('.ks_check_store_url').val() !=1) {
                    return false;
                } else {
                    return true;
                }

            }, $.mage.__('Please check Store Url Availability.'));

            // create custom validation method
            $.validator.addMethod('check-store-url', function (value) {
                $('.ks-available').remove();
                if(value.match(/^[A-Z][A-Z0-9-\/-]*$/i)) {
                    return true;
                } else {
                    return false;
                }

            }, $.mage.__('Only alphanumeric characters and dash(-) symbol are allowed.'));

            $(self.options.ksBecomeSellerClass).on('change', function () {
                self.ksCallAppendSellerStoreBlock(this.element, $(this).val());
            });

            $(this.element).delegate(self.options.ksSellerStoreName, 'keyup', function () {
                var ksSellerStoreNameVal = $(self.options.ksSellerStoreName).val();
                var ksSellerStoreUrlVal= ksSellerStoreNameVal.replace(/[^a-z^A-Z^0-9\.\-]/g,'-').toLowerCase();

                $(self.options.ksSellerStoreUrlClass).val(ksSellerStoreUrlVal);
            });

            $(this.element).delegate(self.options.ksSellerStoreUrlClass, 'keyup', function () {
                $(".ks_check_store_url").val(0);
            });

            $(self.options.ksCheckStoreAvailable).on('click', function () {
                self.ksCallAjaxToCheckStore();
            });
        },

        ksCallAppendSellerStoreBlock: function (parentelem, elem) {
            var self = this;
            if (elem==1) {
                $(self.options.ksSellerStoreClass).show();

            } else {
                $(self.options.ksPageLoader).parents(parentelem)
                 .find('button.submit').removeAttr("disabled");
                $(self.options.ksSellerStoreClass).hide();
            }
        },

        ksCallAjaxToCheckStore: function () {
            var self = this;
            $(self.options.button).attr('disabled', 'disabled');

            var ksSellerStoreNameVal = $(self.options.ksSellerStoreName).val();

            if (!$(self.options.ksSellerStoreUrl).val()) {
                alert({
                    content: $t('Please Enter Store URL')
                });
            } else {
                // check characters in url 
                if(!$(self.options.ksSellerStoreUrl).val().match(/^[A-Z][A-Z0-9-\/-]*$/i)) {
                    $('.ks-available').remove();
                    $('#ks_seller_store_url-error').html('');
                    $(self.options.ksSellerStoreClass).append(
                        $('<div/>').addClass('ks-available message error')
                        .text('Only alphanumeric characters and dash(-) symbol are allowed.')
                    );
                    return false;
                }

                $(self.options.ksPageLoader).removeClass('no-display');

                $.ajax({
                    type: "POST",
                    url: self.options.ksAjaxVerifyStoreUrl,
                    data: {
                        ks_seller_store_url: $(self.options.ksSellerStoreUrl).val()
                    },
                    success: function (ks_response) {

                        $(self.options.ksPageLoader).addClass('no-display');
                        $('.ks-available').remove();
                        $('#ks_seller_store_url-error').css("display","none");
                        $(".ks_check_store_url").val(1);

                        if (ks_response===0) {

                            $(self.options.button).removeAttr("disabled");

                            $(self.options.ksSellerStoreClass).append(
                                $('<div/>').addClass('ks-available message success')
                                .text(self.options.ksSuccessMessage)
                            );

                        } else {
                            $(self.options.button).attr('disabled', 'disabled');
                            $(self.options.ksSellerStoreClass).append(
                                $('<div/>').addClass('ks-available message error')
                                .text(self.options.ksErrorMessage)
                            );
                        }
                    },
                    error: function (ks_response) {
                        alert({
                            content: $t('There was error during verifying seller store data')
                        });
                    }
                });
            }
        }
    });
    return $.mage.verifySellerStore;
});