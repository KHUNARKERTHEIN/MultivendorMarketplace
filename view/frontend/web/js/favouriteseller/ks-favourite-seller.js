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
 	"mage/translate",
 	"Magento_Ui/js/modal/alert",
 	"Magento_Ui/js/modal/confirm"
 	], function($, url ,$t ,alert ,confirmation) {
 		$(document).ready(function () {
 			
 			/* Check all checkboxes on clicking mass select checkbox and change color of Delete button */
 			$("#ks-select-all").change(function () {
 				$(".ks-select").prop('checked', $(this).prop('checked'));
 				if($('.ks-select:checked').length > 0) {
 					$('.ks-fav-delete').addClass('ks-action-save');
 				} else {
 					$('.ks-fav-delete').removeClass('ks-action-save');
 				}
 			});

 			/* If any individual checkbox is not selected uncheck the mass select checkbox */
 			$(".ks-select").change(function(){
 				if (!$(this).prop("checked")){
 					$("#ks-select-all").prop("checked",false);
 				} 
 			});

 			/* If all individual checkboxes are selected then check mass select checkbox and change color of Delete button */   
 			$(".ks-select").change(function(){
 				if ($('.ks-select:checked').length == $('.ks-select').length) {
 					$("#ks-select-all").prop("checked",true);
 				}
 				if($('.ks-select:checked').length > 0) {
 					$('.ks-fav-delete').addClass('ks-action-save');
 				} else {
 					$('.ks-fav-delete').removeClass('ks-action-save');
 				}
 			});

 			/* script use for collapse and de-collapse whole table */
	        $(".ks-action-eye").click(function () {
	            if($(this).hasClass('ks-fav-open-eye')){
	                $('.ks-fav-table-data-hide').hide();
	                $('.ks-action-eye').addClass('ks-fav-close-eye');
	                $('.ks-action-eye').removeClass('ks-fav-open-eye');
	            } else {
	                $('.ks-fav-table-data-hide').show();
	                $('.ks-action-eye').removeClass('ks-fav-close-eye');
	                $('.ks-action-eye').addClass('ks-fav-open-eye');
	            }
	        });

	        /* script use for individual collapse and de-collapse row */
	        $(document).ready(function() {
	            $('.ks-individual-collapse').change(function(){
	                $(this).prev().toggleClass('ks-down-arrow');
	                $(this).parents().next('.ks-fav-table-data-hide').toggle();
	            });
	        });

	        /* Open confirmation popup on mass delete */
 			$('body').on('click','.ks-fav-delete',function(e){
 				var ks_selected = [];
 				$('.ks-individual input:checked').each(function() {
 					ks_selected.push($(this).data('id'));
 				});

 				if(ks_selected.length == 0) {
 					alert({
				        title: $.mage.__('Attention'),
				        content: $.mage.__("You haven't selected any items!"),
				        buttons: [{
						    text: $.mage.__('OK'),
						    class: 'action-primary action-accept',
						    click: function () {
						        this.closeModal(true);
						    }
						}]
				    });
 				} else {
 					e.preventDefault();
 					var ks_msg = '';
 					if(ks_selected.length == 1) {
						ks_msg = $.mage.__("Are you sure you want to delete the selected favourite sellers? (" + ks_selected.length +" record)");
					} else {
						ks_msg = $.mage.__("Are you sure you want to delete the selected favourite sellers? (" + ks_selected.length +" records)");
					}
		            confirmation({
		                title: $.mage.__('Delete Favourite Seller'),
		                content: $.mage.__(ks_msg),
		                actions: {
		                    confirm: function(){
		                    	$.ajax({
				 					type: "POST",
				 					showLoader: true, //use for display loader
				 					url: url.build("multivendor/favouriteseller/bulkdelete"),
				 					data: {
				 						masssellerids : ks_selected
				 					},
				 					success: function (ksResponse) {
				 						location.reload();
				 					}
				 				});
		                    },
		                    cancel: function(){
		                    },
		                }
		            });
 				}
 			});

 			$(".ks-action-del").on('click', function(e){
	            e.preventDefault();
	            var url = e.currentTarget.href;
	            confirmation({
	                title: $.mage.__('Delete Favourite Seller'),
	                content: $.mage.__('Are you sure you want to delete this favourite seller?'),
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

 			/* Save or Remove new product alert preference */
 			$('body').on('change','.ks-product',function(){
		      var ksUrl = url.build('multivendor/favouriteseller/newProductAlert');
		      var ksOperation = "remove";
		      if(this.checked) {
		      	ksOperation = "save";
		      }
		      $.ajax({
		          type: "POST",
		          url: ksUrl,
		          showLoader : true,
		          data: {
		              id: $(this).data('value'),
		              action : ksOperation,
		          },
		          success: function (ksResponse) { 
		              location.reload();
		          }
		      });
		    });

 			/* Save or Remove price change alert preference */
		    $('body').on('change','.ks-price',function(){
		      var ksUrl = url.build('multivendor/favouriteseller/priceChangeAlert');
		      var ksOperation = "remove";
		      if(this.checked) {
		        ksOperation = "save";
		      }
		      $.ajax({
		          type: "POST",
		          url: ksUrl,
		          showLoader : true,
		          data: {
		              id : $(this).data('value'),
		              action : ksOperation, 
		          },
		          success: function (ksResponse) { 
		              location.reload();
		          }
		      });
		    });

 		});
 	});