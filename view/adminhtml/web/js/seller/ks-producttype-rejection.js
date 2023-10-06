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
    "Magento_Ui/js/modal/confirm",
    'Magento_Ui/js/modal/alert'
    ], function($, modal, url ,$t, registry, confirmation, uiAlert) { 

        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            modalClass: 'ks-reject-modal',
            title: $t('Rejection reason for the Product Type'),
            buttons: [
            {
                text: $t('Reset'),
                class: 'action- scalable primary',
                click: function (event) {
                    $('#ks_producttype_reject_form')[0].reset();
                }
            },
            {
                text: $.mage.__('Save'),
                class: 'action- scalable primary',
                showLoader : true,
                click: function () {
                    $.ajax({
                        type: "POST",
                        showLoader : true,
                        url: url.build("multivendor/producttype/reject"),
                        data: {
                            form_key: window.FORM_KEY,
                            ks_id: $("#ks_producttype_id").val(),
                            ks_notify : $('.ks_product_type_rejection_email').is(':checked'),
                            ks_rejection_reason: $('#ks_producttype_reject_form textarea').val()
                        },
                        success: function (ksResponse) { 
                            location.reload();   
                        }
                    });
                }
            }]
        };
        $('body').on('click','.ks-producttype-reject',function(){
            $('#ks_producttype_rejection_popup').modal(options).modal('openModal');
            $("#ks_producttype_id").val($(this).data('id'));
        });
});