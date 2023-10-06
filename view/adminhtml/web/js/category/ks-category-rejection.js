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
                title: $t('Rejection Reason for Product Category'),
                buttons: [{
                    text: $t('Reset'),
                    class: 'action- scalable primary',
                    click: function (event) {
                        $('#ks_reject_form')[0].reset();
                        }
                    },
                    {
                    text: $.mage.__('Save'),
                    showLoader : true,
                    class: 'action- scalable primary',
                    click: function () { 
                         $.ajax({
                            type: "POST",
                            showLoader : true,
                            url: url.build("multivendor/categoryrequests/reject"),
                            data: {
                                form_key: window.FORM_KEY,
                                ks_id: $("#ks_id").val(),
                                ks_rejection_reason: $('#ks_reject_form textarea').val(),
                                ks_notify_seller: $("#ks_reject_form input[type='checkbox']").val()
                            },
                            success: function (ksResponse) {
                                location.reload();
                            },
                            error: function (xhr, ajaxOptions, thrownError) {
                                console.log("fail");
                            }
                         });
                    }
                }]
            };
            
            $('body').on('click','.ks-category-reject',function(){
                $('#ks_rejection_popup').modal(options).modal('openModal');
                $("#ks_id").val($(this).data('id'));
                $("#ks_reject_form input[type='checkbox']").change(function() {
                    if($(this).is(':checked')){
                        $(this).prop("checked",true);
                        $(this).val(1);
                    } else {
                        $(this).prop("checked",false);
                        $(this).val(0);
                    }
                });
            });

});