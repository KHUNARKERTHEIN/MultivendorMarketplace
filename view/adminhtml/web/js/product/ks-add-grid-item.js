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
    ], 
function ($) {
    
    return {

        /**
         * Show per page number
         * @private
         */
        _ksUpdatePager: function (ksElement) {
            var ksPerPage = ksElement.find('.ks-pager-select');
            var self =this;
            $(ksPerPage).change(function(){
                self._ksPagingNumerUpdate(ksElement);
            })
            
        },

        /**
         * update per page number
         * @private
         */
        _ksPagingNumerUpdate: function (ksElement) {
            var ksPageSize = ksElement.find('.ks-pager-select').val();
            var ksTableRow = ksElement.find(".ks-gridrow").not(".remove-row"); 
            var ksTotalCountPage = Math.ceil(ksTableRow.length / ksPageSize);
            ksElement.find('.ks-pager-text').text("of "+ksTotalCountPage);
            ksElement.find('.ks-current-page').val(1);
            
            this._ksShowPage(1,ksPageSize, ksElement);
            this._ksPagerButtonDisabled(1, ksPageSize, ksElement);

        },

        /**
         * @param {String} url
         */
        _ksChangePager: function (ksElement) {
            var ksTableRow = ksElement.find(".ks-gridrow").not(".remove-row");
            var ksCurrentPage = ksElement.find('.ks-current-page');
            var ksPagerSelect = ksElement.find(".ks-pager-select");
            var self =this;

            ksElement.find(".ks-grid-pager button").click(function() { 
                
                if($(this).hasClass("ks-action-previous")){
                    var ksNum = parseInt(ksCurrentPage.val()) - 1;
                }
                if($(this).hasClass("ks-action-next")){
                    var ksNum = parseInt(ksCurrentPage.val()) + 1;
                }
                
                ksCurrentPage.val(ksNum);
                var ksPageSize = ksPagerSelect.val();

                self._ksPagerButtonDisabled(ksNum, ksPageSize, ksElement);
                self._ksShowPage(ksNum, ksPageSize, ksElement) 
            });
        },

        /**
         * Change paging count
         * @private
         */
        _ksShowPage: function (ksPage, ksPageSize, ksElement) { 
            var ksTableRow = ksElement.find(".ks-gridrow").not(".remove-row");
            ksTableRow.hide();
            ksTableRow.each(function(n) { 
                if (n >= ksPageSize * (ksPage - 1) && n < ksPageSize * ksPage)
                    $(this).show();
            });         
        },

        /**
         * Paging button disable/enable
         * @private
         */
        _ksPagerButtonDisabled:function (ksNum, ksPageSize, ksElement) {
            var ksPrevousButton = ksElement.find('.ks-action-previous');
            var ksNextButton = ksElement.find(".ks-action-next");

            if(ksNum > 1){
               ksPrevousButton.prop('disabled',false);
            }else{
               ksPrevousButton.prop('disabled',true);
            } 

            var ksTableRow = ksElement.find(".ks-gridrow").not(".remove-row");
            var ksTotalCountPage = Math.ceil(ksTableRow.length / ksPageSize);

            if(ksTotalCountPage == ksNum){
                ksNextButton.prop('disabled',true);
            }else{
                ksNextButton.prop('disabled',false);
            }     
            
        },
    };
});
