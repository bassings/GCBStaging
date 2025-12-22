(function($){
	"use strict";
	//Code here

	jQuery(document).ready(function($) {
		
		$(document).on('click','#qcopd_fa_icon > .field-item > input', function(e){

		    e.preventDefault();

			$('#fa-field-modal').show();
			$("#fa-field-modal").attr("data", this.id);

		});
		$(document).on('click','#sld_item_fa', function(e){
			
		    e.preventDefault();

			$('#fa-field-modal').show();
			$("#fa-field-modal").attr("data", this.id);

		});
		$(document).on('click','#qcopd_other_list > .field-item > input, .qcopd-manage-large-list #qcopd_other_list', function(e){
			
		    e.preventDefault();

			$('#sld-fa-field-modal1').show();
			$("#sld-fa-field-modal1").attr("data", this.id);

		});
		
		$(document).on('click','#sld_list_select', function(e){
			e.preventDefault();
			var arr = [];
			$('.sld_list_Checkbox:checked').each(function () {
			   arr.push($(this).val());
			   $(this).prop('checked', false);
			});
			
			var $getid = $("#sld-fa-field-modal1").attr('data');
			$('#'+$getid).val(arr.join(","));
			$('#sld-fa-field-modal1').removeAttr("data");
			$('#sld-fa-field-modal1').hide();
		})
		
		

		$( '.fa-field-modal-close' ).on( 'click', function() {
			$('#fa-field-modal').removeAttr("data");
			$('#sld-fa-field-modal1').removeAttr("data");
			$('#fa-field-modal').hide();
			$('#sld-fa-field-modal1').hide();
			$('#fa-field-modal-tag').remove();

		});

		$( '.fa-field-modal-icon-holder' ).on( 'click', function() {

			var icon = $(this).data('icon');
			var $getid = $("#fa-field-modal").attr('data');
			$('#'+$getid).val(icon);
			$('#fa-field-modal').removeAttr("data");
			$('#fa-field-modal').hide();
		});
		
		$("#id_search").quicksearch("div.fa-field-modal-icons div.fa-field-modal-icon-holder", {
			noResults: '#noresults',
			stripeRows: ['odd', 'even'],
			loader: 'span.loading',
			minValLength: 2
		});
		
		var sorting_langth = $("table.package-listing-sorting tbody").length;

		if(sorting_langth){
			
			$("table.package-listing-sorting tbody").sortable({		
				update: function( event, ui ) {
					sldPackageOrderByID();
				}
			});

		}

		function sldPackageOrderByID() {	
			var item_order = new Array();
			$('table.package-listing-sorting tbody tr').each(function() {
				item_order.push($(this).attr("data-package-id"));
			});

			var order_string = item_order;

			jQuery.ajax({
				type: 'post',
				url: ajaxurl,
				data:{
		            action: 'sld_package_list_item_ordering',
		            order_string: order_string,
					security:sld_ajax_object.ajax_nonce
				},
	            success: function(data){ 

	            	console.log(data);

	        	}
	        });


		}
		



	});

	function showfamodal(data){
		
		document.getElementById('fa-field-modal').style.display = 'block';
		document.getElementById('fa-field-modal').setAttribute("data", data.id);
		//jQuery.('#fa-field-modal').show();
	}


})(jQuery);