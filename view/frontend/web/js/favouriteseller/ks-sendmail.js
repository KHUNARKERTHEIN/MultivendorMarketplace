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
  "Magento_Ui/js/modal/alert",
  "mage/translate",
  "Magento_Ui/js/modal/confirm",
  "mage/adminhtml/events",
  "mage/adminhtml/wysiwyg/tiny_mce/setup"
  ], function($, modal, url ,alert, $t, confirmation) {

    // Multiple email popup form
    var options = {
      type: 'popup',
      responsive: true,
      innerScroll: true,
      modalClass: 'ks-email-modal',
      title: $t("Send message to followers"),
      buttons: [{
        text: $.mage.__('Send Email'),
        class: 'ks-action-btn action-primary ks-send-mail-btn',
        click: function() {
          var ks_div_value = $('#ks-multiple-email-div').text();
          var ksContent = tinymce.get("ks_message").getContent();
          
          if(ksContent == ""){
              $('#ks_mul_msg_error').show();
          }

          if(!$('#ks_subject').val()){
              $('#ks_mul_subject_error').show();
          }

          if(ks_div_value) {
            if($('#ks_subject').val() && ksContent != "") {
              $('body').trigger('processStart');
              $("#ks-favourite-seller-form").submit();
            } 
          } else {
            alert({
                title: $.mage.__('Attention'),
                content: $.mage.__("Please select atleast one follower !"),
                buttons: [{
                text: $.mage.__('OK'),
                class: 'action-primary action-accept',
                click: function () {
                    this.closeModal(true);
                }
              }]
            });
          }
        }
      }]
    };

    // Single line email popup form
    var ksOptions = {
      type: 'popup',
      responsive: true,
      innerScroll: true,
      modalClass: 'ks-email-modal',
      title: $t("Send message to follower"),
      buttons: [{
        text: $.mage.__('Send Email'),
        class: 'ks-action-btn action-primary ks-send-single-mail-btn',
        click: function() {
          var ksContent = tinymce.get("ks_single_message").getContent();
          if(ksContent == ""){
              $('#ks_single_msg_error').show();
          }

          if(!$('#ks_single_subject').val()){
              $('#ks_single_subject_error').show();
          }

          if($('#ks_single_subject').val() && ksContent != "") {
            $('body').trigger('processStart');
            $("#ks-favourite-seller-single-mail-form").submit();
          }
        }
      }]
    };

    $('.ks-multiplesendmail').prop('disabled', true);
    
    $('body').on('click','.action-menu-item',function() {
      if($(this).text() == "Select All") {
        $('.ks-multiplesendmail').prop('disabled', false);
      } else if($(this).text() == "Deselect All") {
        $('.ks-multiplesendmail').prop('disabled', true);
      }
    });
    
    $('body').on('change','.admin__control-checkbox',function() {
      var ks_selected = [];
      $('.admin__data-grid-wrap input:checked').each(function() {
        var ksId = $(this).attr('value');
        if(!isNaN(ksId)){
          ks_selected.push(ksId);
        }
      });
      if (ks_selected.length > 0) {
        $('.ks-multiplesendmail').prop('disabled', false);
      } else {
        $('.ks-multiplesendmail').prop('disabled', true);
      }
    });

    // Single Line model popup
    $('body').on('click','.ks-fav-sendmail',function() {
      wysiwygMessage = new wysiwygSetup("ks_single_message", {
        "width": "100%",
        "height": "200px",
        "plugins": [{
            "name": "image"
        }],
        "tinymce": {
            "toolbar": "formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table charmap",
            "plugins": "advlist autolink lists link charmap media noneditable table contextmenu paste code help table",
        }
      });
      wysiwygMessage.setup("exact");
      $('#ks_favourite_seller_single_mail_popup').modal(ksOptions).modal('openModal');
      $('.ks_fav_single_email').val($(this).attr('id'));
      $('#ks_single_subject').val('');
      $('#ks_single_message').val('');

    });
    
    // For Multiple Checkbox popup
    $('body').on('click','.ks-multiplesendmail',function() {
      wysiwygMessage = new wysiwygSetup("ks_message", {
        "width": "100%",
        "height": "200px",
        "plugins": [{
            "name": "image"
        }],
        "tinymce": {
            "toolbar": "formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table charmap",
            "plugins": "advlist autolink lists link charmap media noneditable table contextmenu paste code help table",
        }
      });
      wysiwygMessage.setup("exact");
      $('#ks_favourite_seller_mail_popup').modal(options).modal('openModal');
      var ks_selected = [];
      $('.ks_fav_email').val('');
      $('#ks_subject').val('');
      $('#ks_message').val('');
      $('#ks-multiple-email-div').empty('');
      $('.admin__data-grid-wrap input:checked').each(function() {
        ks_selected.push($(this).attr('value'));
      });

      var ksHtml = '';
      ks_selected.forEach(function(ks_value) {
        $('.ks-fav-sendmail').each(function() {
          if($(this).data('id') == ks_value) {
            ksHtml += '<div class="ks-customer-email-tag"><span class="ks_customer_email">'+$(this).attr('id')+'</span><span class="ks-email-close" ks-email="'+$(this).attr('id')+'"></span></div>';
            if ($('.ks_fav_email').val()) {
              $('.ks_fav_email').val( $('.ks_fav_email').val() + ',' + ($(this).attr('id')));
            } else {
              $('.ks_fav_email').val($(this).attr('id'));  
            }
          }
        });
      }); 
      $('.ks_div_email').prepend(ksHtml);
    });

    /* Remove email */
    $('body').on('click','.ks-email-close',function() {
      var ks_emails = $('#ks-multiple-email-input').val().split(',');
      var ks_index = ks_emails.indexOf($(this).attr('ks-email'));
      if (ks_index >= 0) {
        ks_emails.splice( ks_index, 1 );
      }
      $('#ks-multiple-email-input').val(ks_emails.join(","));
      $(this).closest(".ks-customer-email-tag").remove();
    });

    /* Enable or Disable Price Change Alert */
    $('body').on('change','.ks-price',function(){
      var ksUrl = url.build('multivendor/favouriteseller_seller/priceChangeAlert');
      var ksOperation = "disable";
      if(this.checked) {
        ksOperation = "enable";
      }
      $.ajax({
          type: "POST",
          url: ksUrl,
          showLoader : true,
          data: {
              id : $(this).data('id'),
              action : ksOperation,
          },
          success: function (ksResponse) { 
              location.reload();
          }
      });
    });  

    /* Enable or Disable New Product Alert */
    $('body').on('change','.ks-product',function(){
      var ksUrl = url.build('multivendor/favouriteseller_seller/newProductAlert');
      var ksOperation = "disable";
      if(this.checked) {
        ksOperation = "enable";
      }
      $.ajax({
          type: "POST",
          url: ksUrl,
          showLoader : true,
          data: {
              id : $(this).data('id'),
              action : ksOperation,
          },
          success: function (ksResponse) { 
              location.reload();
          }
      });
    });

    /* Delete confirmation popup */
    $('body').on('click','.ks-delete',function(e){
      e.preventDefault();
      var url = e.currentTarget.href;
      confirmation({
          title: $.mage.__('Delete Follower'),
          content: $.mage.__('Are you sure you want to delete this follower?'),
          actions: {
            confirm: function(){
                $('body').trigger('processStart');
                window.location.href = url;
            },
            cancel: function(){
            },
          }
      });
    });
  });
