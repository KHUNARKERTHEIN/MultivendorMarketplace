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
], function ($) {

    /**
     * remove product matrix row
     */
    $('body').on('click',".ks-remove-item-action",function(e){ 
          $(this).closest("tr").remove();
    });

    $('body').on('click',".ks-action-select",function(e){ 
          $(this).closest("tr").find('.ks-dropdown-link').toggleClass('_active');
    });

    /**
     * change status of product
     */
    $('body').on('click',".ks-change-status-item-action",function(e){ 

        if ($(this).attr("data-item-status") == 1) {
            $(this).closest("tr").find('.product-status').val(2);
            $(this).closest("tr").find('.col-status span').text('Disabled');
            $(this).text('Enable');
            $(this).attr('data-item-status', '2');
        } else {
            $(this).closest("tr").find('.product-status').val(1);
            $(this).closest("tr").find('.col-status span').text('Enabled');
            $(this).text('Disable');
            $(this).attr('data-item-status', '1');
        }

        $(this).closest("tr").find('.ks-dropdown-link').toggleClass('_active');
    });
 
    /**
     * Show per page number
     * @private
     */
    ksUpdatePager = function () {
          var ksPerPage = $('.ks-pager-select');

          $(ksPerPage).change(function(){
              ksPagingNumerUpdate();
          })
          
      }

      /**
       * update per page number
       * @private
       */
      ksPagingNumerUpdate = function () {
          var ksPageSize = $('.ks-pager-select').val();
          var ksTableRow = $("tbody tr");
          var ksTotalCountPage = Math.ceil(ksTableRow.length / ksPageSize);
          $('.ks-pager-text').text("of "+ksTotalCountPage);
          $('.ks-current-page').val(1);
          
          ksShowPage(1,ksPageSize);
          ksPagerButtonDisabled(1, ksPageSize);

      }

      /**
       * @param {String} url
       */
      ksChangePager= function () {
          var ksTableRow = $("tbody tr");
          var ksCurrentPage = $('.ks-current-page');
          var ksPagerSelect = $(".ks-pager-select");

          $(".ks-grid-pager button").click(function() { 
              
              if($(this).hasClass("ks-action-previous")){
                  var ksNum = parseInt(ksCurrentPage.val()) - 1;
              }
              if($(this).hasClass("ks-action-next")){
                  var ksNum = parseInt(ksCurrentPage.val()) + 1;
              }
              
              ksCurrentPage.val(ksNum);
              var ksPageSize = ksPagerSelect.val();

              ksPagerButtonDisabled(ksNum, ksPageSize);
              ksShowPage(ksNum, ksPageSize) 
          });
      }

      /**
       * Change paging count
       * @private
       */
      ksShowPage= function (ksPage, ksPageSize) { 
          var ksTableRow = $("tbody tr");
          ksTableRow.hide();
          ksTableRow.each(function(n) { 
              if (n >= ksPageSize * (ksPage - 1) && n < ksPageSize * ksPage)
                  $(this).show();
          });         
      }

    /**
     * Paging button disable/enable
     * @private
     */
    ksPagerButtonDisabled = function (ksNum, ksPageSize) {
        var ksPrevousButton = $('.ks-action-previous');
        var ksNextButton = $(".ks-action-next");

        if(ksNum > 1){
           ksPrevousButton.prop('disabled',false);
        }else{
           ksPrevousButton.prop('disabled',true);
        } 

        var ksTableRow = $("tbody tr");
        var ksTotalCountPage = Math.ceil(ksTableRow.length / ksPageSize);

        if(ksTotalCountPage == ksNum){
            ksNextButton.prop('disabled',true);
        }else{
            ksNextButton.prop('disabled',false);
        }   
    }

    ksUpdatePager();
    ksPagingNumerUpdate();
    ksChangePager();
});
