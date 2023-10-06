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
        "mage/translate"
    ], function($, modal, url ,$t) { 

            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                modalClass: 'ks-reject-modal',
                title: $t("Rejection reason for Product Attribute"),
                buttons: [{
                    text: $t('Reset'),
                    class: 'action- scalable primary',
                    click: function (event) {
                        $('#ks_reject_form')[0].reset();
                    }
                },
                {
                    text: $.mage.__('Save'),
                    class: 'action- scalable primary',
                    click: function () {
                         $.ajax({
                            type: "POST",
                            url: url.build("multivendor/productattribute/reject"),
                            showLoader : true,
                            data: {
                                form_key: window.FORM_KEY,
                                attribute_id: $("#ks_id").val(),
                                ks_notify : $('.ks_notify_seller').is(':checked'),
                                ks_rejection_reason: $('#ks_reject_form textarea').val()
                            },
                            success: function (ksResponse) { 
                                location.reload();
                            }
                         });
                    }
                }]
            };
            $('body').on('click','.ks-attribute-reject',function(){
                $('#ks_rejection_popup').modal(options).modal('openModal');
                $("#ks_id").val($(this).data('id'));
            });

            $('body').on('click','.ks-reload',function(){
                $("body").trigger('processStart');
            });
    });