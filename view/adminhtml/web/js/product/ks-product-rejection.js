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
        title: $t("Rejection reason for the Seller's Product"),
        buttons: [{
            text: $t('Reset'),
            class: 'action- scalable primary',
            click: function (event) {
                $('#ks_product_reject_form')[0].reset();
            }
        },
            {
                text: $.mage.__('Save'),
                class: 'action- scalable primary',
                click: function () {
                    $.ajax({
                        type: "POST",
                        showLoader: true,
                        url: url.build("multivendor/product_ajax/productreject"),
                        data: {
                            form_key: window.FORM_KEY,
                            ks_id: $("#ks_id").val(),
                            ks_rejection_reason: $('#ks_product_reject_form textarea').val(),
                            ks_notify_seller: $('.ks_rejection_email').is(':checked')
                        },
                        success: function (ksResponse) {
                            location.reload();
                        }
                    });
                }
            }]
    };
    $('body').on('click','.ks-product-reject',function(){
        $('#ks_product_rejection_popup').modal(options).modal('openModal');
        $("#ks_id").val($(this).data('id'));
    });

    $('body').on('click','.ks-reload',function(){
        $("body").trigger('processStart');
    });

});
