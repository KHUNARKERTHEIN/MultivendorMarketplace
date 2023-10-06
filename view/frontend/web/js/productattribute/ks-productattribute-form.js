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
  'mage/translate',
  'Magento_Ui/js/modal/confirm',
  'mage/validation',
  'mage/calendar',
  'mage/validation'
  ], 
  function ($, $t, confirmation) {

    $(document).ready(function() {
        $("#ksDefaultOpen").click();
        $(".ks-input-type-select").on('change', function() {
            var ksCase = this.value;
            ksSwitch(ksCase);

        });
        // Show Hide Search
        $(".ks-is_searchable").on('change', function() {
            if (this.value == 1) {
                $('.ks-field-search_weight').show();
            } else {
                $('.ks-field-search_weight').hide();
            }
        });
        
        $('.ks-is_filterable').prop('disabled', true);
        $('.ks-manage-options-panel').hide();
        $('.ks-visual-swatch').hide();
        $('.ks-text-swatch').hide();
        $('.ks-is_filterable_in_search').prop('disabled', true);
        if ($('.ks-input-type-select :selected').val()) {
            var ksSelected = $('.ks-input-type-select :selected').val();
            ksSwitch(ksSelected);
        }

        if ($('.ks-is_searchable :selected').val() == 1) {
            $('.ks-field-search_weight').show();
        }
        // If the Code is available Disable Some of the Fields
        if ($('.ks-attibute-id').val()) {
            $("#ks-attribute_code").prop('disabled', true);
            var ksSelected = $('.ks-input-type-select :selected').val();
            if (ksSelected == 'textarea' || ksSelected == 'texteditor') {
                if (ksSelected == 'textarea') {
                    $('.ks-input-type-select').find('option').remove().end()
                    .append('<option value="textarea" selected>Text Area</option><option value="texteditor">Text Editor</option>');
                } else {
                    $('.ks-input-type-select').find('option').remove().end()
                    .append('<option value="textarea">Text Area</option><option value="texteditor" selected>Text Editor</option>');
                }
            } else if (ksSelected == 'select' || ksSelected == 'swatch_text' || ksSelected == 'swatch_visual') {
                if (ksSelected == 'select') {
                    $('.ks-input-type-select').find('option').remove().end()
                    .append('<option value="select" selected>Dropdown</option><option value="swatch_visual">Visual Swatch</option><option value="swatch_text">Text Swatch</option>');
                } else if(ksSelected == 'swatch_text') {
                    $('.ks-input-type-select').find('option').remove().end()
                    .append('<option value="select">Dropdown</option><option value="swatch_visual">Visual Swatch</option><option value="swatch_text" selected>Text Swatch</option>');
                } else {
                    $('.ks-input-type-select').find('option').remove().end()
                    .append('<option value="select">Dropdown</option><option value="swatch_visual" selected>Visual Swatch</option><option value="swatch_text">Text Swatch</option>');
                }
            }
            else {
                $('.ks-input-type-select').prop('disabled', true);
            }
            // Check note
            $(".ks-input-type-select").on('change', function() {
                var ksValue = $('.ks-input-type-select :selected').val();
                if (ksValue != ksSelected) {
                    $('.ks-attribute_label-change-note').show();
                } else {
                    $('.ks-attribute_label-change-note').hide();
                }
            });
        }

        // Remove Disable Feature When Form is Submiited
        $('form').submit(function(e) {
            $("#ksDefaultOpen").click();
            $(':disabled').each(function(e) {
                $(this).removeAttr('disabled');
            });
            if (!$('#ks-attribute_label').val()) {
                e.preventDefault();
            }
        });
        // Making all field disable when Admin Attribute
        if ($('.ks-admin-attribute').val() == "0") {
            $("form :input").prop("disabled", true);
            $('.ks-back').prop("disabled", false);
        }

        $('#ks-default_value_date').calendar({
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            currentText: $t('Go Today'),
            closeText: $t('Close'),
            showWeek: true
        });

        $('#ks-default_value_datetime').calendar({
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            currentText: $t('Go Today'),
            closeText: $t('Close'),
            showWeek: true,
            showsTime: true
        });

        $(".ks-seller-attribute-delete").on('click', function(e){
            e.preventDefault();
            var url = e.currentTarget.href;
            confirmation({
                content: $.mage.__('Are you sure you want to do this?'),
                actions: {
                  confirm: function(){
                    window.location.href = url;
                    },
                    cancel: function(){
                    },
                }
            });
        });
    });

    // Function for Hiding & Showing some of the fields
    function ksSwitch(ksCase) {
        switch (ksCase) {
            case 'text' :
                $('.ks-text-editor-note').hide();
                $('.ks-store-front').show();
                $('.ks-field-default_value_text').show();
                $('.ks-field-default_value_date').hide();
                $('.ks-field-default_value_textarea').hide();
                $('.ks-field-default_value_datetime').hide();
                $('.ks-field-default_value_yesno').hide();
                $('.ks-field-is_required').show();
                $('.ks-field-is_global').show();
                $('.ks-field-is_unique').show();
                $('.ks-field-frontend_class').show();
                $('.ks-text-swatch').hide();
                $('.ks-field-update_product_preview_image').hide();
                $('.ks-field-use_product_image_for_swatch').hide();
                $('.ks-visual-swatch').hide();
                $('.ks-manage-options-panel').hide();
                $('.ks-is_filterable').prop('disabled', true);
                $('.ks-frontend_class').prop('disabled', false);
                $('.ks-is_filterable_in_search').prop('disabled', true);
                break;
            case 'textarea':
                $('.ks-field-default_value_text').hide();
                $('.ks-field-default_value_textarea').show();
                $('.ks-field-default_value_date').hide();
                $('.ks-field-default_value_datetime').hide();
                $('.ks-text-editor-note').hide();
                $('.ks-store-front').show();
                $('.ks-field-is_required').show();
                $('.ks-field-is_global').show();
                $('.ks-field-is_unique').show();
                $('.ks-visual-swatch').hide();
                $('.ks-field-frontend_class').show();
                $('.ks-field-update_product_preview_image').hide();
                $('.ks-field-use_product_image_for_swatch').hide();
                $('.ks-manage-options-panel').hide();
                $('.ks-text-swatch').hide();
                $('.ks-frontend_class').prop('disabled', true);
                $('.ks-is_filterable').prop('disabled', true);
                $('.ks-is_filterable_in_search').prop('disabled', true);
                break;
            case 'texteditor':
                $('.ks-field-default_value_text').hide();
                $('.ks-field-default_value_textarea').show();
                $('.ks-field-default_value_date').hide();
                $('.ks-field-default_value_datetime').hide();
                $('.ks-text-editor-note').show();
                $('.ks-store-front').show();
                $('.ks-visual-swatch').hide();
                $('.ks-field-is_required').show();
                $('.ks-field-is_global').show();
                $('.ks-field-is_unique').show();
                $('.ks-field-frontend_class').show();
                $('.ks-text-swatch').hide();
                $('.ks-field-update_product_preview_image').hide();
                $('.ks-field-use_product_image_for_swatch').hide();
                $('.ks-manage-options-panel').hide();
                $('.ks-is_filterable').prop('disabled', true);
                $('.ks-frontend_class').prop('disabled', true);
                $('.ks-is_filterable_in_search').prop('disabled', true);
                break;
            case 'date':
                $('.ks-field-default_value_text').hide();
                $('.ks-field-default_value_textarea').hide();
                $('.ks-field-default_value_date').show();
                $('.ks-field-default_value_datetime').hide();
                $('.ks-field-default_value_yesno').hide();
                $('.ks-text-editor-note').hide();
                $('.ks-visual-swatch').hide();
                $('.ks-store-front').show();
                $('.ks-field-is_required').show();
                $('.ks-field-is_global').show();
                $('.ks-field-is_unique').show();
                $('.ks-field-frontend_class').show();
                $('.ks-field-update_product_preview_image').hide();
                $('.ks-field-use_product_image_for_swatch').hide();
                $('.ks-manage-options-panel').hide();
                $('.ks-text-swatch').hide();
                $('.ks-frontend_class').prop('disabled', true);
                $('.ks-is_filterable').prop('disabled', true);
                $('.ks-is_filterable_in_search').prop('disabled', true);
                break;
            case 'datetime':
                $('.ks-field-default_value_text').hide();
                $('.ks-field-default_value_textarea').hide();
                $('.ks-field-default_value_date').hide();
                $('.ks-field-default_value_datetime').show();
                $('.ks-field-default_value_yesno').hide();
                $('.ks-text-editor-note').hide();
                $('.ks-store-front').show();
                $('.ks-field-is_required').show();
                $('.ks-visual-swatch').hide();
                $('.ks-field-is_global').show();
                $('.ks-field-is_unique').show();
                $('.ks-text-swatch').hide();
                $('.ks-manage-options-panel').hide();
                $('.ks-field-frontend_class').show();
                $('.ks-field-update_product_preview_image').hide();
                $('.ks-field-use_product_image_for_swatch').hide();
                $('.ks-frontend_class').prop('disabled', true);
                $('.ks-is_filterable').prop('disabled', true);
                $('.ks-is_filterable_in_search').prop('disabled', true);
                break;
            case 'boolean' :
                $('.ks-text-editor-note').hide();
                $('.ks-field-default_value_text').hide();
                $('.ks-field-default_value_textarea').hide();
                $('.ks-field-default_value_date').hide();
                $('.ks-field-default_value_datetime').hide();
                $('.ks-field-default_value_yesno').show();
                $('.ks-store-front').show();
                $('.ks-field-is_required').show();
                $('.ks-visual-swatch').hide();
                $('.ks-field-is_global').show();
                $('.ks-field-is_unique').show();
                $('.ks-text-swatch').hide();
                $('.ks-field-frontend_class').show();
                $('.ks-manage-options-panel').hide();
                $('.ks-field-update_product_preview_image').hide();
                $('.ks-field-use_product_image_for_swatch').hide();
                $('.ks-frontend_class').prop('disabled', true);                
                $('.ks-is_filterable').prop('disabled', false);
                $('.ks-is_filterable_in_search').prop('disabled', false);
                break;
            case 'multiselect':
                $('.ks-field-default_value_text').hide();
                $('.ks-field-default_value_textarea').hide();
                $('.ks-field-default_value_date').hide();
                $('.ks-field-default_value_datetime').hide();
                $('.ks-field-default_value_yesno').hide();
                $('.ks-text-editor-note').hide();
                $('.ks-store-front').show();
                $('.ks-visual-swatch').hide();
                $('.ks-field-update_product_preview_image').hide();
                $('.ks-field-use_product_image_for_swatch').hide();                
                $('.ks-field-is_required').show();
                $('.ks-field-is_global').show();
                $('.ks-text-swatch').hide();
                $('.ks-field-is_unique').show();
                $('.ks-field-frontend_class').show();
                $('.ks-manage-options-panel').show();
                $('.ks-frontend_class').prop('disabled', true);
                $('.ks-is_filterable').prop('disabled', false);
                $('.ks-is_filterable_in_search').prop('disabled', false);
                $('.input-radio').each(function() {
                    $(this).prop('type', 'checkbox');
                });
                break;
            case 'select':
                $('.ks-field-default_value_text').hide();
                $('.ks-field-default_value_textarea').hide();
                $('.ks-field-default_value_date').hide();
                $('.ks-field-default_value_datetime').hide();
                $('.ks-field-default_value_yesno').hide();
                $('.ks-text-editor-note').hide();
                $('.ks-store-front').show();
                $('.ks-visual-swatch').hide();
                $('.ks-text-swatch').hide();
                $('.ks-manage-options-panel').show();
                $('.ks-field-update_product_preview_image').hide();
                $('.ks-field-use_product_image_for_swatch').hide();
                $('.ks-field-is_required').show();
                $('.ks-field-is_global').show();
                $('.ks-field-is_unique').show();
                $('.ks-field-frontend_class').show();
                $('.ks-frontend_class').prop('disabled', true);
                $('.ks-is_filterable').prop('disabled', false);
                $('.ks-is_filterable_in_search').prop('disabled', false);
                $('.input-radio').each(function() {
                    $(this).prop('type', 'radio');
                });
            break;
                case 'price':
                $('.ks-field-default_value_text').hide();
                $('.ks-field-default_value_textarea').hide();
                $('.ks-field-default_value_date').hide();
                $('.ks-field-default_value_datetime').hide();
                $('.ks-field-is_required').show();
                $('.ks-field-is_global').show();
                $('.ks-store-front').show();
                $('.ks-field-is_unique').show();
                $('.ks-visual-swatch').hide();
                $('.ks-text-swatch').hide();
                $('.ks-manage-options-panel').hide();
                $('.ks-field-update_product_preview_image').hide();
                $('.ks-field-use_product_image_for_swatch').hide();
                $('.ks-field-frontend_class').show();
                $('.ks-frontend_class').prop('disabled', true);
                $('.ks-is_filterable').prop('disabled', false);
                $('.ks-is_filterable_in_search').prop('disabled', false);
                break;
            case 'media_image':
                $('.ks-store-front').hide();
                $('.ks-text-editor-note').hide();
                $('.ks-text-editor-note').hide();
                $('.ks-field-default_value_text').hide();
                $('.ks-field-default_value_yesno').hide();
                $('.ks-field-default_value_textarea').hide();
                $('.ks-visual-swatch').hide();
                $('.ks-field-is_required').hide();
                $('.ks-field-is_global').show();
                $('.ks-field-is_unique').hide();
                $('.ks-text-swatch').hide();
                $('.ks-manage-options-panel').hide();
                $('.ks-frontend_class').prop('disabled', true);
                $('.ks-field-update_product_preview_image').hide();
                $('.ks-field-use_product_image_for_swatch').hide();
                $('.ks-field-frontend_class').hide();
                break;
            case 'swatch_visual':
                $('.ks-store-front').hide();
                $('.ks-text-editor-note').hide();
                $('.ks-text-editor-note').hide();
                $('.ks-field-default_value_date').hide();
                $('.ks-field-default_value_text').hide();
                $('.ks-field-default_value_datetime').hide();
                $('.ks-field-default_value_textarea').hide();
                $('.ks-field-update_product_preview_image').show();
                $('.ks-store-front').show();
                $('.ks-field-use_product_image_for_swatch').show();
                $('.ks-field-is_required').show();
                $('.ks-field-is_global').show();
                $('.ks-text-swatch').hide();
                $('.ks-visual-swatch').show();
                $('.ks-manage-options-panel').hide();
                $('.ks-field-is_unique').show();
                $('.ks-frontend_class').prop('disabled', true);
                $('.ks-field-frontend_class').show();
                break;
            case 'swatch_text':
                $('.ks-store-front').hide();
                $('.ks-text-editor-note').hide();
                $('.ks-text-editor-note').hide();
                $('.ks-field-default_value_date').hide();
                $('.ks-field-default_value_text').hide();
                $('.ks-visual-swatch').hide();
                $('.ks-store-front').show();
                $('.ks-field-update_product_preview_image').show();
                $('.ks-field-use_product_image_for_swatch').hide();
                $('.ks-field-default_value_datetime').hide();
                $('.ks-field-default_value_textarea').hide();
                $('.ks-field-is_required').show();
                $('.ks-text-swatch').show();
                $('.ks-manage-options-panel').hide();
                $('.ks-field-is_global').hide();
                $('.ks-field-is_unique').hide();
                $('.ks-frontend_class').prop('disabled', true);
                $('.ks-field-frontend_class').hide();
                break;
            case 'weee':
                $('.ks-store-front').hide();
                $('.ks-text-editor-note').hide();
                $('.ks-text-editor-note').hide();
                $('.ks-field-default_value_date').hide();
                $('.ks-field-default_value_text').hide();
                $('.ks-field-default_value_datetime').hide();
                $('.ks-field-default_value_textarea').hide();
                $('.ks-visual-swatch').hide();
                $('.ks-field-is_required').hide();
                $('.ks-manage-options-panel').hide();
                $('.ks-field-is_global').hide();
                $('.ks-field-is_unique').hide();
                $('.ks-text-swatch').hide();
                $('.ks-frontend_class').prop('disabled', true);
                $('.ks-field-update_product_preview_image').hide();
                $('.ks-field-use_product_image_for_swatch').hide();
                $('.ks-field-frontend_class').hide();
                break;
            default:
                $('.ks-text-editor-note').hide();
                $('.ks-store-front').show();
                $('.ks-field-default_value_text').show();
                $('.ks-field-default_value_date').hide();
                $('.ks-field-default_value_datetime').hide();
                $('.ks-field-default_value_textarea').hide();
                $('.ks-visual-swatch').show();
                $('.ks-field-is_required').show();
                $('.ks-field-is_global').show();
                $('.ks-field-is_unique').show();
                $('.ks-text-swatch').hide();
                $('.ks-field-frontend_class').show();
                $('.ks-field-update_product_preview_image').hide();
                $('.ks-frontend_class').prop('disabled', true);
                $('.ks-field-use_product_image_for_swatch').hide();
                $('.ks-manage-options-panel').hide();
                break;
        }
        // If It is admin attribute
        if ($('.ks-admin-attribute').val() == "0") {
            $('.ks-is_filterable_in_search').prop('disabled', true);
            $('.ks-is_filterable').prop('disabled', true);
            $('.ks-frontend_class').prop('disabled', true); 
        }
    }
});

function ksOpenTab(ksEvent, ksIdName) {
    var ksIndex, ksTabContent, ksTabLinks;
    ksTabContent = document.getElementsByClassName("ks-tabcontent");
    for (ksIndex = 0; ksIndex < ksTabContent.length; ksIndex++) {
        ksTabContent[ksIndex].style.display = "none";
    }
    ksTabLinks = document.getElementsByClassName("ks-tablinks");
    for (ksIndex = 0; ksIndex < ksTabLinks.length; ksIndex++) {
        ksTabLinks[ksIndex].className = ksTabLinks[ksIndex].className.replace(" active", "");
    }
    document.getElementById(ksIdName).style.display = "block";
    ksEvent.target.className += " active";      
}