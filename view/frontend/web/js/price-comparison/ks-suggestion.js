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
        "mage/url",
        "mage/translate"
    ], function($, url ,$t) { 
      $(document).ready(function(){
      $("#ks-query").keyup(function(){
        var ks_value = $(this).val();
        if (ks_value.length <= 2) {
          $('.ks_suggestion_list').hide();
        }
        if (ks_value.length > 2) {
          $.ajax({
            type: "POST",
            url: url.build("multivendor/pricecomparison/searchproduct"),
            data:'keyword='+ks_value,
            success: function(ksData){
              $('.ks_suggestion_list').show();
              ks_list = ksData.ksproduct;
              var ks= "";
              ks_list.forEach(function (ksname) {
                ks+='<li onClick="ksSelectValue('+"'"+ksname+"'"+');">'+ksname+'</li>';
              });
              $('.ks_suggestion_list').html(ks);
            }
          });
        }
      });
    });
  });

function ksSelectValue(ksVal) {
  require([
    'jquery'
    ], function ($) {
      $("#ks-query").val(ksVal);
      $(".ks-search-icon").click();
    $(".ks_suggestion_list").hide();
  });
}