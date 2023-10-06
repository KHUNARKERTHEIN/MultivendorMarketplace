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
    'Ksolves_MultivendorMarketplace/js/product/ks-type-events',
    'Ksolves_MultivendorMarketplace/js/product/ks-qty-handler',
    'Ksolves_MultivendorMarketplace/js/product/ks-price-handler',
    'mage/translate',
    'Magento_Ui/js/modal/confirm',
    'mage/url',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/modal',
    'mage/calendar',
    'mage/adminhtml/wysiwyg/tiny_mce/setup'
    ], function ($ , ksProductType, ksQty, ksPrice, $t,$confirm, url, alert, modal) {
    'use strict';
    var ksSelf;
    /**
    */
    $.widget('mage.KsProductForm', {
        /**
         * * Fired when widget initialization start
         * @private
         */
        _create: function () {
            ksProductType.init();
            ksQty.init();
            ksPrice.init();
            this._ksAddCalender();
            this._ksTypeSku();
            this._ksTypeMeta();
            this._ksTypeQty();
            this._ksDisableWeight();
            this._ksEnableUrlKey();
            this._ksUseDefaultCheckbox();
            this._ksSetButtonType();
            this._ksPreventButton();
            this._ksDateTimePicker();
            this._ksReadonlyUrlKeyField();
            this._ksModelCondition();
            ksSelf = this;
            if (ksProductType.type.init == 'simple') {
                $('#is-downloaodable').prop('disabled',true);
                $(".ks-downloadable-simple-product-message").show();
            }

        },

        /**
         * * add calender to datetime
         * @private
         */
        _ksAddCalender: function () {
            $(".ks-datepicker").calendar({
                changeMonth: true,
                changeYear: true,
                showButtonPanel: true,
                currentText: $t('Go Today'),
                closeText: $t('Close'),
                showWeek: true
            });
            $('.ks-datepicker').attr('autocomplete','off');
        },

        /**
         * * add datetime
         * @private
         */
        _ksDateTimePicker: function () {
            $(".ks-datetimepicker").datetimepicker({
                dateFormat: 'mm/dd/yy',
                timeFormat: 'HH:mm:ss',
                changeMonth: true,
                changeYear: true,
                showsTime: true
            });
            $('.ks-datetimepicker').attr('autocomplete','off');
        },

        /**
         * * disable weight field
         * @private
         */
        _ksDisableWeight: function () {
            $('select[name="product[product_has_weight]"]').change(function () {
                if(this.value==0) {
                    $('input[name="product[weight]"]').prop('disabled',true);
                    $('#is-downloaodable').prop('disabled',false);
                    $(".ks-downloadable-simple-product-message").hide();
                } else {
                    $('input[name="product[weight]"]').prop('disabled',false);
                    $('#is-downloaodable').prop('disabled',true);
                    $(".ks-downloadable-simple-product-message").show();
                }
            });
        },

        /**
         * * Enable url key
         * @private
         */
        _ksEnableUrlKey: function () {
            $('input[name="product[url_key]').on('keyup', function() {
                $(".ks-url-create input[type='checkbox']").prop('disabled',false);
                $(".ks-url-create").removeClass('ks-field-disabled');
            });
        },



        /**
         * * set disable field use default checkbox click
         * @private
         */
        _ksUseDefaultCheckbox: function () {
            $('.ks-use-default-control:checked').each(function () {
                $(this).closest('.ks-use-default').find('input,textarea,select').not('.ks-use-default-control').prop('disabled',true);
                $(this).closest('.ks-use-default').find('.ks-dollor-sign').addClass("disabled");
            });

            $(".ks-use-default .ks-wysiwyg").each(function(){
                if($(this).closest('.ks-use-default').find('.ks-use-default-control').is(":checked")){
                    var ksWysiwygEditor = $(this).attr("id");
                    tinyMCE.get(ksWysiwygEditor).getBody().setAttribute('contenteditable', false);
                }

            });

            $('.ks-use-default-control').click(function () {
                if ($(this).prop('checked') === true) {
                    $(this).closest('.ks-use-default').find('input,textarea,select').not('.ks-use-default-control').prop('disabled',true);
                    $(this).closest('.ks-use-default').find('.ks-dollor-sign').addClass("disabled");
                    var ksWysiwygEditor = $(this).closest('.ks-use-default').find(".ks-wysiwyg").attr("id");
                    if(ksWysiwygEditor){
                        tinyMCE.get(ksWysiwygEditor).getBody().setAttribute('contenteditable', false);
                    }
                } else {
                    $(this).closest('.ks-use-default').find('input,textarea,select').not('.ks-use-default-control').prop('disabled',false);
                    $(this).closest('.ks-use-default').find('.ks-dollor-sign').removeClass("disabled");
                    var ksWysiwygEditor = $(this).closest('.ks-use-default').find(".ks-wysiwyg").attr("id");
                    if(ksWysiwygEditor){
                        tinyMCE.get(ksWysiwygEditor).getBody().setAttribute('contenteditable', true);
                    }

                }
            });

        },

        /**
         * * change sku on type name
         * @private
         */
        _ksTypeSku: function () {
            var ksSkuVal = $('input[name="product[sku]"').val();

            $('input[name="product[sku]"').on('change', function() {
                ksSkuVal = $('input[name="product[sku]"').val();
            });

            $('input[name="product[name]"').on('change keyup', function() {
                if(ksSkuVal==""){
                    $('input[name="product[sku]"').val($(this).val());
                }
            });
        },

        /**
         * * change meta words on type name
         * @private
         */
        _ksTypeMeta: function () {
            var ksMetaTitleVal = $('input[name="product[meta_title]"').val();
            var ksMetaKeywordVal = $('textarea[name="product[meta_keyword]"').val();
            var ksMetaDescriptionVal = $('textarea[name="product[meta_description]"').val();

            $('input[name="product[meta_title]"').on('change', function() {
                ksMetaTitleVal = $('input[name="product[meta_title]"').val();
            });

            $('textarea[name="product[meta_keyword]"').on('change', function() {
                ksMetaKeywordVal = $('textarea[name="product[meta_keyword]"').val();
            });

            $('textarea[name="product[meta_description]"').on('change', function() {
                ksMetaDescriptionVal = $('textarea[name="product[meta_description]"').val();
            });

            $('input[name="product[name]"').on('change keyup', function() { 
                var ksName = $(this).val();
                var ksProductId = $("input[name='id']").val();
                if(ksMetaTitleVal=="" && !ksProductId){
                    $('input[name="product[meta_title]"').val(ksName);
                }
                if(ksMetaKeywordVal=="" && !ksProductId){
                    $('textarea[name="product[meta_keyword]"').val(ksName);
                }
                if(ksMetaDescriptionVal=="" && !ksProductId){
                    $('textarea[name="product[meta_description]"').val(ksName);
                }
            });
        },

        /**
         * * change qty on update stock qty
         * @private
         */
        _ksTypeQty: function () {

            $('.ks-qty').on('change', function() {
                $('#inventory_qty').val($(this).val());
            });

            $('#inventory_qty').on('change', function() {
                $('.ks-qty').val($(this).val());
            });
        },

        /**
         * * change qty on update stock qty
         * @private
         */
        _ksSetButtonType: function () {
            $('#ks_product_form .ks-item, #save_and_submit,#save-button').click(function () {
                $('#back').val($(this).data('type'));
                if($("#ks_product_form").validation('isValid')){
                    $confirm({
                        title: 'Confirm',
                        content: 'Are you sure you want to proceed?',
                        actions: {
                            confirm: function(){
                                $(':disabled').removeAttr('disabled');
                                if (ksSelf._ksCheckNewAttribute()) {
                                    ksSelf._ksOpenModal();
                                } else {
                                    $('#ks_product_form').submit();
                                }
                            },
                            cancel: function(){},
                            always: function(){}
                        }
                    });
                }else{
                    $("input.mage-error").each(function () {
                        $(this).closest(".ks-fieldset-wrapper-content").show();
                    });
                    $("input.mage-error:visible:first").focus();
                }
            });
        },

        /**
         * Model Creation
         * @return void
         */
        _ksOpenModal: function () {
            var ksSetOptions = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                modalClass:"ks-choose-attribute-set confirm",
                title: 'Choose Affected Attribute Set',
                buttons: [
                {
                    text: $.mage.__('Confirm'),
                    class: 'action-primary',
                    click: function () {
                        if ($('#ks_attribute_handler').validation('isValid')) {
                            var ksChoose = $('input[name="configurableAffectedAttributeSet"]:checked').val();
                            if (ksChoose == 'new') {
                                ksSelf._ksCreateNewAttributeSet();
                            }
                            else if (ksChoose == 'existing') {
                                var ksExistingSetId = $('.ks-existing-attribute-set-select :selected').val();
                                $('input[name="product[attribute_set_id]"').val(ksExistingSetId);
                                $('#ks_product_form').submit();
                            } else {
                                $('#ks_product_form').submit();
                            }
                        }
                    }
                }]
            };
            modal(ksSetOptions, $(".ks_configurable_product_attribute_set_pop_up"));
            $(".ks_configurable_product_attribute_set_pop_up").modal("openModal");
        },

        /**
         * Model Condition
         * @return void
         */
        _ksModelCondition: function () {
            $('body').on('click', 'input[name=configurableAffectedAttributeSet]', function(e){
                if ($(this).val() == 'new') {
                    $('.ks-new-attribute-set').show();
                    $('.ks-existing-attribute-set').hide();
                }
                else if ($(this).val() == 'existing') {
                    $('.ks-existing-attribute-set').show();
                    $('.ks-existing-attribute-set-select option[value="'+$('input[name="product[attribute_set_id]"').val()
                        +'"]').prop("selected", true);
                    $('.ks-new-attribute-set').hide();
                } else {
                    $('.ks-new-attribute-set').hide();
                    $('.ks-existing-attribute-set').hide();
                }
            });
        },

        /**
         * Check new attribute 
         * @returns {Boolean}
         */
        _ksCheckNewAttribute: function() {
            var ksCheck = false;
            let ksFormData = $('form').serializeArray();
            let ksSetId = ksFormData.filter(ks => ks.name === "product[attribute_set_id]");
            if (ksFormData.some(ks => ks.name === 'attributes[]')) {
                $.ajax({
                    type: "POST",
                    url: url.build("multivendor/product_ajax/kscheckattributeset"),
                    showLoader: true,
                    data: {
                        "set_id": ksSetId[0]['value']
                    },
                    success: function (ksResponse) {
                        if (ksResponse.success) {
                            if (!(ksResponse.seller_attribute_set)) {
                                $('.ks_current_attribute_set').hide();
                                $('#affectedAttributeSetNew').click();
                            } else {
                                $('.ks_current_attribute_set').show();
                            }
                        }
                        if (ksResponse.error) {
                            alert({
                                content: $t('Something went wrong.')
                             });
                        }
                    }
                });
                let ksAttributesValues = ksFormData.filter(ks => ks.name === 'attributes[]');
                let ksAttributecode = [];
                ksAttributesValues.forEach(function(ksIndex){
                    ksAttributecode.push(ksFormData.filter(ks => ks.name === 'product[configurable_attributes_data]['+ksIndex['value']+'][code]'));
                });
                ksAttributecode.forEach(function(ksElements, index){
                    if (!(ksFormData.some(ks => ks.name === 'product['+ksElements[0]['value']+']'))) {
                        ksCheck = true;
                    }
                });
            }
            return ksCheck;
        },

        /**
         * Handles new attribute set creation
         * @returns {Boolean}
         */
        _ksCreateNewAttributeSet: function () {
            let ksFormData = $('form').serializeArray();
            let ksSetId = ksFormData.filter(ks => ks.name === "product[attribute_set_id]");
            $.ajax({
                type: 'POST',
                url: url.build("multivendor/productattribute_set/save"),
                data: {
                    gotoEdit: 1,
                    'attribute_set_name': $('#ks-new-attribute-set-name').val(),
                    'skeleton_set': ksSetId[0]['value'],
                    'return_session_messages_only': 1
                },
                dataType: 'json',
                showLoader: true,
            }).done(function (data) {
                if (!data.error) {
                    $('input[name="product[attribute_set_id]"').val(data.id);
                    $('#ks_product_form').submit();
                } else {
                    var ksError = Error(data.messages);
                    ksError = ksError.message;
                    ksError.replace( /\//g,"");
                    ksError.replace(/"/g, '');
                    $(ksError).insertBefore('.ks_current_attribute_set');
                }
                return false;
            }).fail(function (xhr) {
                if (xhr.statusText === 'abort') {
                    return;
                }
                alert({
                    content: $t('Something went wrong.')
                });
            });

            return false;
        },

        /**
         * * prevent submit action from ther button
         * @private
         */
        _ksPreventButton: function () {
            $('body').on('click', '#ks_product_form .admin__data-grid-header button', function(e){
                e.preventDefault();
            });
            $('body').on('keypress', '#ks_product_form :input', function(e){
                if(e.keyCode == 13){
                    e.preventDefault();
                    $("#ks_product_form .admin__data-grid-header [data-action='grid-filter-apply']").trigger('click');
                }
            });
        },

        /**
         * * make url_key field readonly in price comparison product form
         * @private
         */
        _ksReadonlyUrlKeyField: function () {
            $('.multivendor-pricecomparison-addproduct input[name="product[url_key]"]').prop('readonly', true);
            $('.multivendor-pricecomparison-editproduct input[name="product[url_key]"]').prop('readonly', true);
        },

    });

return $.mage.KsProductForm;
});
