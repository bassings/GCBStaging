(function($){
	"use strict";
	//Code here


jQuery(document).ready(function($){

	
	$('#sld_reset_upvote').on('click', function(e){
		e.preventDefault();
		$( "input[name*='qcopd_upvote_count']" ).each(function(){
			$(this).val(0);
		})
		$('#sld_show_msg').html('Upvote has been reset successfully. please click Update button.');
	})
	
	$('#sld_reset_all_upvotes').on('click', function(e){		
		e.preventDefault();
		$.post(ajaxurl, {
			action: 'show_qcsld_upvote_reset', 
			},
			function(data) {

				$('#wpwrap').append(data);

			}
		);
		
	})
	$(document).on( 'click', '.modal-content .close', function(){
		if( !isGutenbergActive() ){
        	$(this).parent().parent().remove();
		}
    })
	$(document).on( 'change', '#sld_list', function(){
        var currentVal = $(this).val();
		if(currentVal!=='all'){
			$.post(ajaxurl, {
				action: 'show_qcsld_list_items',
				listid: currentVal,
				security:sld_ajax_object.ajax_nonce
			},
			function(data) {

				$('.sld_reset_child_item').append(data);
				
			});
		}else{
			$('.sld_reset_child_item').html('');
		}
    })
	
	$(document).on('click','#sld_reset_votes', function(e){
		e.preventDefault();
		
		var list = $('#sld_list').val();
		var item = $('#sld_list_item').val();
		if(typeof(item)=='undefined'){
			item = '';
		}
		$.post(ajaxurl, {
			action: 'qcopd_reset_all_upvotes',
			list: list,
			item: item,
			security:sld_ajax_object.ajax_nonce
		},
		function(data) {

			$('.sld_reset_child_item').append(data);
			
		});
		
		
	})
	
	$('#tab_frontend').on('click',function(e){
		e.preventDefault();
		$('#sld_page_check').html('<p class="sld_page_loading">Loading...</p>');
		var datarr = ['sld_login', 'sld_registration', 'sld_dashboard', 'sld_restore'];
		for(var i=0;i<4;i++){
			
			$.post(ajaxurl, {
				action: 'qcopd_search_sld_page', 
				shortcode: datarr[i],
				security:sld_ajax_object.ajax_nonce
			}, function(data) {
				$('#sld_page_check .sld_page_loading').hide();
				
				if(data!=='' && !data.match(/not/g)){
					$('#sld_page_check').append('<p style="color:green">'+data+'</p>');
				}else{
					$('#sld_page_check').append('<p style="color:red">'+data+'</p>');
				}
				
			});
			
			
		}
		//
	})

	$('#sld_flash_button').on('click',function(e){
		e.preventDefault();
		$('#sld_flash_msg').html('<p class="sld_page_loading">Loading...</p>');

		$.post(ajaxurl, {
			action: 'qcopd_flash_rewrite_rules', 
			security:sld_ajax_object.ajax_nonce
		},function(data) {

			$('#sld_flash_msg').html('<p class="sld_page_loading" style="color:green;">Rewrite has been Flushed Successfully!</p>');

		});
	})
	
	$(document).on('click', '.sld_collapse', function(e){
		e.preventDefault();
		
		var elem = $(this);
		elem.closest('.field-item').addClass('sld_section_collapse');
		elem.removeClass('sld_collapse').addClass('sld_expend');
		elem.text('Expand');
		
	})
	$(document).on('click', '.sld_expend', function(e){
		e.preventDefault();
		
		var elem = $(this);
		elem.closest('.field-item').removeClass('sld_section_collapse');
		elem.removeClass('sld_expend').addClass('sld_collapse');
		elem.text('Collapse');
		
	})
	
	$(document).on('click','.sld_ctm_btn1', function(e){
		e.preventDefault();
		var elem = $(this);
		$( ".sld_collapse" ).each(function( index ) {
		  $( this ).click();
		});
		
		elem.removeClass('sld_ctm_btn1').addClass('sld_ctm_btn1_e');
		elem.text('Expand All');
		
		
	})
	$(document).on('click','.sld_ctm_btn1_e', function(e){
		e.preventDefault();
		var elem = $(this);
		$( ".sld_expend" ).each(function( index ) {
		  $( this ).click();
		});
		
		elem.removeClass('sld_ctm_btn1_e').addClass('sld_ctm_btn1');
		elem.text('Collapse All');
		
		
	})
	$(document).on('click','.sld_ctm_btn2', function(e){
		e.preventDefault();
		 $('html, body').animate({
			scrollTop: $("#text-ad-block").offset().top - 100
		}, 2000);
	})
	
	$(document).on('click', '#qcopd_tags input', function(e){
		var elem = $(this);
		var elemid = this.id;
		$.post(ajaxurl, {
			action: 'qcopd_tag_pd_page', 
			security:sld_ajax_object.ajax_nonce
		},
			
		function(data) {
			
			$('#wpwrap').append(data);
			$('#sldtagvalue').val(elem.val());
			$('#sld-tags').attr('data', elemid);
			
			//console.log($(data).find('.fa-field-modal-title').text());

			$('#sld-tags').tagInput();
			$('.labelinput').focus();
			$.post(ajaxurl, {
				action: 'qcopd_search_pd_tags', 
				security:sld_ajax_object.ajax_nonce
			},
			
			function(data) {
				console.log(data);
				
				$('.labelinput').autocomplete({
					  source: data.split(','),
					  
				});					
				
			});
				

			
		});
		
	})
	
	$(document).on( "autocompleteselect",'.labelinput', function( event, ui ) {
		//event.preventDefault();
		if( event.keyCode == 13 ){
			event.preventDefault();
		}
	});
	
	$(document).on('click','.closelabel',function(e){
		e.preventDefault();
	})
	
	$( document ).on( 'click','.fa-field-modal-close', function() {
		
		$('#fa-field-modal-tag').remove();

	});
	$(document).on('click','#sld_tag_select', function(){
		$('#'+$('#sld-tags').attr('data')).val($('#sldtagvalue').val());
		$('#fa-field-modal-tag').remove();
	})
	
	$(document).on('click', "input[name*='[qcopd_image_from_link]']", function(e){
		var objc = $(this);
		
		
		if($(this).is(':checked')) {
			if(objc.closest('.cmb_metabox').find('#qcopd_item_link input').val()!='' && objc.closest('.cmb_metabox').find('#qcopd_item_img input').val()==''){
				var html = "<div id='sld_ajax_preloader'><div class='sld_ajax_loader'></div></div>";
				$('#wpwrap').append(html);
				$.post(ajaxurl, {
					action: 'qcopd_img_download', 
					url: objc.closest('.cmb_metabox').find('#qcopd_item_link input').val(),
					security:sld_ajax_object.ajax_nonce
				},
				function(data) {
					data = JSON.parse(data);
					console.log(data);
					$('#sld_ajax_preloader').remove();

					
					if(data.imgurl.match(/.jpg/g) || data.imgurl!==null){
						objc.closest('.cmb_metabox').find('#qcopd_item_img input').val(data.attachmentid);
						objc.closest('.cmb_metabox').find('#qcopd_item_img .cmb-file-holder').show();
						objc.closest('.cmb_metabox').find('#qcopd_item_img .cmb-remove-file').removeClass('hidden').show();
						objc.closest('.cmb_metabox').find('#qcopd_item_img .cmb-file-upload').addClass('hidden').hide();
						objc.closest('.cmb_metabox').find('#qcopd_item_img .cmb-file-holder').append('<img src="'+data.imgurl+'" width="150" height="150" />');
					}else{
						alert('Could not generate image. Please check URL and try again.');
					}
					
					
				});
				
			}
			
			// alert(objc.closest('.cmb_metabox').find('#qcpd_item_link input').val() + objc.closest('.cmb_metabox').find('#qcpd_item_img input').val());
		}
	})
	
	
	$(document).on('click', "input[name*='[qcopd_generate_title]']", function(e){
		var objc = $(this);
		if($(this).is(':checked')) {
			
			if(objc.closest('.cmb_metabox').find('#qcopd_item_link input').val()!=''){
				var html = "<div id='sld_ajax_preloader'><div class='sld_ajax_loader'></div></div>";
				$('#wpwrap').append(html);
				$.post(ajaxurl, {
					action: 'qcopd_generate_text', 
					url: objc.closest('.cmb_metabox').find('#qcopd_item_link input').val(),
					security:sld_ajax_object.ajax_nonce
				},
				function(data) {
					
					$('#sld_ajax_preloader').remove();
					data = JSON.parse(data);
					objc.closest('.cmb_metabox').find('#qcopd_item_title input').val(data.title)
					objc.closest('.cmb_metabox').find('#qcopd_item_subtitle input').val(data.description)
					objc.prop('checked', false);
				});
				
			}else{
				alert('Item link field cannot left empty!');
			}
			
		}
		
	})
	
	$('#sld_shortcode_generator_meta').on('click', function(e){
		 $('#sld_shortcode_generator_meta').prop('disabled', true);
		$.post(
			ajaxurl,
			{
				action : 'show_qcsld_shortcodes'
				
			},
			function(data){
				 $('#sld_shortcode_generator_meta').prop('disabled', false);
				$('#wpwrap').append(data);
			}
		)
	})
	
	
	// service image 
	$(document).on('click','.sld_item_image', function(e){
		
        e.preventDefault();
        var $container = $(this);

        var image = wp.media({
            title: 'Upload Item Image',
            // mutiple: true if you want to upload multiple files at once
            multiple: false,
            button: {text: 'Insert'}
        }).open()
        .on('select', function (e) {
			
            var uploaded_image = image.state().get('selection').toJSON();
			
			$('#item_image').val(uploaded_image[0].id);
			$('.sld_item_image_preview img').attr('src', uploaded_image[0].url);
			$('.sld_item_image_preview .sld_large_list_item_remove_image').show();
        });

    });
	
	$(document).on('click', "#sld_generate", function(e){
		var objc = $(this);
		
			if($('#sld_item_link').val()!=''){
				var html = "<div id='sld_ajax_preloader'><div class='sld_ajax_loader'></div></div>";
				$('body').append(html);
				$.post(ajaxurl, {
					action: 'qcopd_generate_text', 
					url: $('#sld_item_link').val(),
					security:sld_ajax_object.ajax_nonce
				},
				function(data) {
					
					$('#sld_ajax_preloader').remove();
					data = JSON.parse(data);
					$('#sld_item_title').val(data.title)
					$('#sld_item_subtitle').val(data.description)
					
				});
				
			}else{
				alert('Item link field cannot left empty!');
			}

		
	})
	
	$(document).on('click', "#sld_generate_image", function(e){
		var objc = $(this);
		
		
		if($(this).is(':checked')) {
			if($('#sld_item_link').val()!='' && $('#item_image').val()==''){
				
				$.post(ajaxurl, {
					action: 'qcopd_img_download', 
					url: $('#sld_item_link').val(),
					security:sld_ajax_object.ajax_nonce

				},
				function(data) {
					
					var data = $.parseJSON(data);
					
					if(data!=''){
						$('#item_image').val(data.attachmentid);
						$('.sld_item_image_preview img').attr('src', data.imgurl);
					}
					
				});
				
			}
			
			//alert(objc.closest('.cmb_metabox').find('#qcpd_item_link input').val() + objc.closest('.cmb_metabox').find('#qcpd_item_img input').val());
		}
	})
	
		$(document).on('click', '#sld_item_tags', function(e){
			var elem = $(this);
			var elemid = this.id;
			$.post(ajaxurl, {
				action: 'qcopd_tag_pd_page', 
				security:sld_ajax_object.ajax_nonce
			},
				
			function(data) {
				
				$('#wpwrap').append(data);
				$('#sldtagvalue').val(elem.val());
				$('#sld-tags').attr('data', elemid);
				
				//console.log($(data).find('.fa-field-modal-title').text());

				$('#sld-tags').tagInput();
				$('.labelinput').focus();
				$.post(ajaxurl, {
					action: 'qcopd_search_pd_tags', 
					security:sld_ajax_object.ajax_nonce
				},
				
				function(data) {
					console.log(data);
					
					$('.labelinput').autocomplete({
						  source: data.split(','),
						  
					});					
					
				});
					
					

				
			});
			
		})
	
	var selector = '';

	
	$(document).on('click','#qcsld_add_shortcode_widget', function(e){
		var widget = $('#sld_widget_area').val();
		var shortcodedata = '';
		if(widget=='tabstyle'){
			shortcodedata = '[qcopd-directory-widget-tab-style]';
		}else if(widget=='latest'){
			shortcodedata = '[qcopd-directory-latest]';
		}else if(widget=='popular'){
			shortcodedata = '[qcopd-directory-popular]';
		}else if(widget=='random'){
			shortcodedata = '[qcopd-directory-random]';
		}
		/*tinyMCE.activeEditor.selection.setContent(shortcodedata);
		 $('#sm-modal').remove();
		*/
		$('.sld_shortcode_generator_area').hide();
		$('.sld_shortcode_container').show();
		$('#sld_shortcode_container').val(shortcodedata);
		$('#sld_shortcode_container').select();
		document.execCommand('copy');
	})
	
	
	$(document).on( 'click', '.sld_copy_close', function(){
		if( !isGutenbergActive() ){
        	$(this).parent().parent().parent().parent().parent().remove();
		}
    });

	jQuery(document).on('click','.sld_copy_close', function(e){
		e.preventDefault();
		jQuery('.currently_editing').next('#sld_insert_shortcode').trigger('click');
		jQuery(document).find( '.sld_modal .modal-content .close').trigger('click');
		//const shortdata = jQuery(this).attr('short-data');
		//setAttributes( { shortcode: shortdata } );
	});

	jQuery(document).on( 'click', '.sld_modal .modal-content .close', function(){
		jQuery('.currently_editing').removeClass('currently_editing');
		jQuery('#sld_shortcode_generator_meta').prop('disabled', false);
		jQuery(this).parent().parent().remove();
	});
	
    $(document).on( 'click', '.modal-content .close', function(){
        $(this).parent().parent().remove();
    }).on( 'click', '#qcsld_add_shortcode',function(){
	
      var mode = $('#sld_mode').val();
      var column = $('#sld_column').val();
      var style = $('#sld_style').val();
      var upvote = $('.sld_upvote:checked').val();
      var search = $('.sld_search:checked').val();
      
	  
      var count = $('.sld_item_count:checked').val();
      var item_details_page = $('.item_details_page:checked').val();
      var orderby = $('#sld_orderby').val();
      var filterorderby = $('#sld_filter_orderby').val();
	  
      var item_orderby = $('#sld_item_orderby').val();
      var item_order   = $('#sld_item_order').val();
      var order = $('#sld_order').val();
      var filterorder = $('#sld_filter_order').val();
	  
	  var listId = $('#sld_list_id').val();
	  var catSlug = $('#sld_list_cat_id').val();

	  var list_title_font_size = $('#sld_list_title_font_size').val();
	  var list_title_line_height = $('#sld_list_title_line_height').val();

	  var title_font_size = $('#sld_title_font_size').val();
	  var subtitle_font_size = $('#sld_subtitle_font_size').val();
	  var title_line_height = $('#sld_title_line_height').val();
	  var subtitle_line_height = $('#sld_subtitle_line_height').val();

	  // var paginate = $('.sld_enable_pagination:checked').val();
	  var paginate = $('#qcopd_enable_pagination_option').val();
	  var tooltip = $('.sld_enable_tooltip:checked').val();

	  var per_page = $('#sld_items_per_page').val();

	  var filter_area = $('#sld_filter_area').val();
	  var topspacing = $('#sld_topspacing').val();

	  var sld_category_orderby = $('#sld_category_orderby').val();
	  var sld_hide_list_title = $('.sld_hide_list_title:checked').val();
	  var sld_show_username = $('.sld_show_username:checked').val();
	  var subcategories_as_dropdown = $('.sld_show_subcats_as_dropdown:checked').val();

	  var sld_category_order = $('#sld_category_order').val();
	  
	  
	  var infinityscroll = $('#infinityscroll:checked').val();
	  var favorite = $('#sld_favorite').val();
		
		var sld_main_click = $('.sld_main_click_pop:checked').val();
		var sld_video_click = $('.sld_video_main_click_pop:checked').val();
		var sld_left_filter = $('.sld_left_filter:checked').val();
		var sld_tag_filter = $('.sld_tag_filter:checked').val();
		
	  if( style == '' )
	  {
		alert("Please select a valid template style.");
		return;
	  }

	  if(mode=='categorytab'){
	  	var shortcode = 'sld-tab';
	  }else{
          var shortcode = 'qcopd-directory';
	  }
	  
	  var shortcodedata = '['+shortcode;
		  		  
		  if( mode !== 'category' ){
			  shortcodedata +=' mode="'+mode+'"';
		  }
		  
		  if( mode == 'one' && listId != "" ){
			  shortcodedata +=' list_id="'+listId+'"';
		  }
		  
		  if( mode == 'category' && catSlug != "" ){
			  shortcodedata +=' category="'+catSlug+'"';
		  }
		  
		  if( style !== '' ){

			  shortcodedata +=' style="'+style+'"';

		  }
		  
		  var style = $('#sld_style').val();
		

		  
		  if( column !== '' ){
			  shortcodedata +=' column="'+column+'"';
		  }
		  

		  
		  if( typeof(upvote) != 'undefined' ){
			  shortcodedata +=' upvote="'+upvote+'"';
		  }else{
			  shortcodedata +=' upvote="off"';
		  }
		  
		  if( typeof(search)!= 'undefined' ){
			  shortcodedata +=' search="'+search+'"';
		  }else{
			  shortcodedata +=' search="false"';
		  }
		  
		 
		  
		  if( typeof(count)!= 'undefined' ){
			  shortcodedata +=' item_count="'+count+'"';
		  }else{
			  shortcodedata +=' item_count="false"';
		  }

		  if( typeof(sld_hide_list_title)!= 'undefined' && sld_hide_list_title == 'true'){
			  shortcodedata +=' hide_list_title="'+sld_hide_list_title+'"';
		  }else{
			  shortcodedata +=' hide_list_title="false"';
		  }

		  if( typeof(sld_show_username)!= 'undefined' && sld_show_username == 'true'){
			  shortcodedata +=' display_username="'+sld_show_username+'"';
		  }else{
			  shortcodedata +=' display_username="false"';
		  }



		  
		  
		  if( typeof(item_details_page)!= 'undefined' ){
			  shortcodedata +=' item_details_page="'+item_details_page+'"';
		  }else{
			  shortcodedata +=' item_details_page="off"';
		  }
		  
		  if( orderby !== '' ){
			  shortcodedata +=' orderby="'+orderby+'"';
		  }else{
			  shortcodedata +=' orderby="date"';
		  }
		  
		  if( typeof(filterorderby) != 'undefined' && filterorderby !== '' ){
			  shortcodedata +=' filterorderby="'+filterorderby+'"';
		  }else{
			  shortcodedata +=' filterorderby="date"';
		  }
		  
		  if( order !== '' ){
			  shortcodedata +=' order="'+order+'"';
		  }else{
			  shortcodedata +=' order="ASC"';
		  }
		  
		  if( typeof(filterorder) != 'undefined' && filterorder !== '' ){
			  shortcodedata +=' filterorder="'+filterorder+'"';
		  }else{
			  shortcodedata +=' filterorder="ASC"';
		  }
		  
		  if( typeof(paginate) != 'undefined' ){
		  	if( paginate == 'js-pagination' ){
				shortcodedata +=' paginate_items="true" actual_pagination="false" ';
		  	}else if( paginate == 'page-pagination' ){
		  		shortcodedata +=' paginate_items="true" actual_pagination="true" ';
		  	}else{
		  		shortcodedata +=' paginate_items="false" actual_pagination="false" ';
		  	}
		  }else{
			  shortcodedata +=' paginate_items="false" actual_pagination="false" ';
		  }
		  
		  if( typeof(paginate) != 'undefined' && per_page !== '' ){
			  shortcodedata +=' per_page="'+per_page+'"';
		  }
		  
		  if( typeof(infinityscroll) != 'undefined' && infinityscroll !== '' ){
			  shortcodedata +=' infinityscroll="'+infinityscroll+'"';
		  }
		  
		 
		  if( typeof(favorite) != 'undefined' && favorite !== '' ){
			  shortcodedata +=' favorite="'+favorite+'"';
		  }
		  
		  if( typeof(sld_left_filter) != 'undefined' && sld_left_filter !== '' ){
			  shortcodedata +=' enable_left_filter="'+sld_left_filter+'"';
		  }else{
			  shortcodedata +=' enable_left_filter="false"';
		  }
		  
		  if( typeof(sld_main_click) != 'undefined' && sld_main_click !== '' ){
			  shortcodedata +=' main_click="popup"';
		  }else{
			  shortcodedata +=' main_click=""';
		  }

		  if( typeof(sld_video_click) != 'undefined' && sld_video_click !== '' ){
			  shortcodedata +=' video_click="nopopup"';
		  }else{
			  shortcodedata +=' video_click="popup"';
		  }
		  
		  if( typeof(sld_tag_filter) != 'undefined' && sld_tag_filter !== '' ){
			  shortcodedata +=' enable_tag_filter="'+sld_tag_filter+'"';
		  }else{
			  shortcodedata +=' enable_tag_filter="false"';
		  }
		  
		  if( typeof(tooltip) != 'undefined' ){
			  shortcodedata +=' tooltip="true"';
		  }else{
			  shortcodedata +=' tooltip="false"';
		  }

			if(mode=='categorytab'){
				if(typeof(sld_category_orderby)!='undefined' || sld_category_orderby!=''){
					shortcodedata +=' category_orderby="'+sld_category_orderby+'"';
				}

				if(typeof(sld_category_order)!='undefined' || sld_category_order!=''){
					shortcodedata +=' category_order="'+sld_category_order+'"';
				}

				if( typeof(subcategories_as_dropdown)!= 'undefined' && subcategories_as_dropdown == 'true'){
					shortcodedata +=' subcategories_as_dropdown="'+subcategories_as_dropdown+'"';
				}else{
					shortcodedata +=' subcategories_as_dropdown="false"';
				}
			}


		  if(typeof(list_title_font_size)!='undefined' || list_title_font_size!=''){
              shortcodedata +=' list_title_font_size="'+list_title_font_size+'"';
		  }else{
			  shortcodedata +=' list_title_font_size=""';
		  }

		  if(typeof(item_orderby)!='undefined' || item_orderby!=''){
              shortcodedata +=' item_orderby="'+item_orderby+'"';
		  }else{
			  shortcodedata +=' item_orderby=""';
		  }

		  if(typeof(item_order)!='undefined' || item_order!=''){
              shortcodedata +=' item_order="'+item_order+'"';
		  }else{
			  shortcodedata +=' item_order=""';
		  }

		  if(typeof(list_title_line_height)!='undefined' || list_title_line_height!=''){
              shortcodedata +=' list_title_line_height="'+list_title_line_height+'"';
		  }else{
			  shortcodedata +=' list_title_line_height=""';
		  }

		  if(typeof(title_font_size)!='undefined' || title_font_size!=''){
              shortcodedata +=' title_font_size="'+title_font_size+'"';
		  }else{
			  shortcodedata +=' title_font_size=""';
		  }

        if(typeof(subtitle_font_size)!='undefined' || subtitle_font_size!=''){
            shortcodedata +=' subtitle_font_size="'+subtitle_font_size+'"';
        }else{
			shortcodedata +=' subtitle_font_size=""';
		}
		
        if(typeof(title_line_height)!='undefined' || title_line_height!=''){
            shortcodedata +=' title_line_height="'+title_line_height+'"';
        }else{
			shortcodedata +=' title_line_height=""';
		}
        if(typeof(subtitle_line_height)!='undefined' || subtitle_line_height!=''){
            shortcodedata +=' subtitle_line_height="'+subtitle_line_height+'"';
        }else{
			shortcodedata +=' subtitle_line_height=""';
		}

        if(typeof(filter_area)!='undefined' || filter_area!=''){
            shortcodedata +=' filter_area="'+filter_area+'"';
        }else{
			shortcodedata +=' filter_area="normal"';
		}

        if(typeof(topspacing)!='undefined' || topspacing!=''){
            shortcodedata +=' topspacing="'+topspacing+'"';
        }
		  
		  shortcodedata += ']';
		
		/*  tinyMCE.activeEditor.selection.setContent(shortcodedata);
		  
		  $('#sm-modal').remove();
		*/
		
		$('.sld_shortcode_generator_area').hide();
		$('.sld_shortcode_container').show();
		$('#sld_shortcode_container').val(shortcodedata);
		$('.sld_copy_close').attr('short-data', shortcodedata);
		$('#sld_shortcode_container').select();
		document.execCommand('copy');
		

    }).on( 'change', '#sld_mode',function(){
	
		var mode = $('#sld_mode').val();
		
		if( mode == 'one' ){
			$('#sld_list_div').css('display', 'block');
			$('#sld_list_cat').css('display', 'none');
			$('#sld_con_orderby').css('display', 'none');
			$('#display_subcat_as_dropdown').css('display', 'none');
			$('#sld_con_order').css('display', 'none');

            $('#sld_cat_orderby').css('display', 'none');
            $('#sld_cat_order').css('display', 'none');
			$('#sld_infinity_scroll').hide();
			$('#sld_column').parent('div').hide();
			$('#sld_item_per_page').hide();
			

		}else if( mode == 'category' ){
			$('#sld_list_cat').css('display', 'block');
			$('#sld_list_div').css('display', 'none');
            $('#sld_con_orderby').css('display', 'block');
            $('#sld_con_order').css('display', 'block');

            $('#sld_cat_orderby').css('display', 'none');
            $('#sld_cat_order').css('display', 'none');
            $('#display_subcat_as_dropdown').css('display', 'none');
			$('#sld_infinity_scroll').hide();
			$('#sld_item_per_page').hide();
			$('#sld_column').parent('div').show();

		}else if(mode=='categorytab'){
            $('#sld_cat_orderby').css('display', 'block');
            $('#sld_cat_order').css('display', 'block');
            $('#display_subcat_as_dropdown').css('display', 'block');
            $('#sld_list_div').css('display', 'none');
            $('#sld_list_cat').css('display', 'none');
            $('#sld_con_orderby').css('display', 'block');
            $('#sld_con_order').css('display', 'block');
			$('#sld_infinity_scroll').hide();
			$('#sld_item_per_page').hide();
			$('#sld_column').parent('div').show();
		}else{
			$('#sld_list_div').css('display', 'none');
			$('#sld_list_cat').css('display', 'none');
            $('#sld_con_orderby').css('display', 'block');
            $('#sld_con_order').css('display', 'block');
            $('#sld_cat_orderby').css('display', 'none');
            $('#sld_cat_order').css('display', 'none');
            $('#display_subcat_as_dropdown').css('display', 'none');
			$('#sld_column').parent('div').show();
		}
		
	}).on( 'change', '#sld_style',function(){
	
		var style = $('#sld_style').val();

		if( style == '' ){
			alert("Please select a valid template style.");
			return;
		}

		if( style != 'style-10' ){
			$('.sld-off-field').css('display', 'block');
		}
		else
		{
			$('.sld-off-field').css('display', 'none');
		}

		if( style == 'simple' ){
			if($('#sld_mode').val()!=='categorytab' && $('#sld_mode').val()!=='category' && $('#sld_mode').val()!=='one'){
				$('#sld_infinity_scroll').show();
				
			}
			
			$('.tt-template').css('display', 'block');
			
		} else {

			$('#sld_infinity_scroll').hide();
			$('#sld_item_per_page').hide();
			$('.tt-template').css('display', 'none');
			
		}
		
		if( style == 'simple' || style == 'style-1' || style == 'style-2' || style == 'style-8' || style == 'style-9' || style == 'style-12' || style == 'style-13' ){
			$('#sld_column_div').css('display', 'block');
		}
		else{
    		$('#sld_column_div').css('display', 'block');
		}
		
		if( style == 'simple' ){
		   $('#demo-preview-link #demo-url').html('<a href="http://dev.quantumcloud.com/sld/" target="_blank">View Demo for Default Style</a>');
		}
		else if( style == 'style-1' ){
		   $('#demo-preview-link #demo-url').html('<a href="http://dev.quantumcloud.com/sld/style-1/" target="_blank">View Demo for Style-1</a>');
		}
		else if( style == 'style-2' ){
		   $('#demo-preview-link #demo-url').html('<a href="http://dev.quantumcloud.com/sld/style-2/" target="_blank">View Demo for Style-2</a>');
		}
		else if( style == 'style-3' ){
		   $('#demo-preview-link #demo-url').html('<a href="http://dev.quantumcloud.com/sld/style-3/" target="_blank">View Demo for Style-3</a>');
		}
		else if( style == 'style-4' ){
		   $('#demo-preview-link #demo-url').html('<a href="http://dev.quantumcloud.com/sld/style-4/" target="_blank">View Demo for Style-4</a>');
		}
		else if( style == 'style-5' ){
		   $('#demo-preview-link #demo-url').html('<a href="http://dev.quantumcloud.com/sld/style-5/" target="_blank">View Demo for Style-5</a>');
		}
		else if( style == 'style-6' ){
		   $('#demo-preview-link #demo-url').html('<a href="http://dev.quantumcloud.com/sld/style-6/" target="_blank">View Demo for Style-6</a>');
		}
		else if( style == 'style-7' ){
		   $('#demo-preview-link #demo-url').html('<a href="http://dev.quantumcloud.com/sld/style-7/" target="_blank">View Demo for Style-7</a>');
		}
		else if( style == 'style-8' ){
		   $('#demo-preview-link #demo-url').html('<a href="http://dev.quantumcloud.com/sld/style-8/" target="_blank">View Demo for Style-8</a>');
		}
		else if( style == 'style-9' ){
		   $('#demo-preview-link #demo-url').html('<a href="http://dev.quantumcloud.com/sld/style-9/" target="_blank">View Demo for Style-9</a>');
		}
		else if( style == 'style-10' ){
		   $('#demo-preview-link #demo-url').html('<a href="http://dev.quantumcloud.com/sld/style-10/" target="_blank">View Demo for Style-10</a>');
		}
		else if( style == 'style-11' ){
		   $('#demo-preview-link #demo-url').html('<a href="http://dev.quantumcloud.com/sld/style-11/" target="_blank">View Demo for Style-11</a>');
		}
		else if( style == 'style-12' ){
		   $('#demo-preview-link #demo-url').html('<a href="http://dev.quantumcloud.com/sld/style-12/" target="_blank">View Demo for Style-12</a>');
		}
		else{
		   $('#demo-preview-link #demo-url').html('<a href="http://dev.quantumcloud.com/sld/" target="_blank">View Demo for Default Style</a>');
		}	

		if($('#sld_mode').val()=='one'){
			
			if(style == 'style-3' || style == 'style-4' || style == 'style-5' || style == 'style-6' || style == 'style-7' || style == 'style-10' || style == 'style-11' || style == 'style-13' || style == 'style-14' || style == 'style-15'){
				
				$('#sld_column').parent('div').show();
				
			}else{
				
				$('#sld_column').parent('div').hide();
				
			}
			
		}

		if(style == 'style-1'){
			$('#sld_column option[value="3"]').hide();
			$('#sld_column option[value="4"]').hide();

		}else{
			$('#sld_column option[value="3"]').show();
			$('#sld_column option[value="4"]').show();
			
		}	
		
	});

	jQuery(document).on( 'click', '.sld_large_list_item_remove_image', function(){
		jQuery(this).parent('.sld_item_image_preview').find('img').attr('src','');
		jQuery(this).parent('.sld_item_image_preview').prev('#item_image').val('');
		jQuery(this).hide();
	});
	
});



function isGutenbergActive() {
    return typeof wp !== 'undefined' && typeof wp.blocks !== 'undefined';
}



jQuery(document).ready(function ($) {

    jQuery(window).on('load', function() {
       //jQuery("#qcopd_timelaps input[type='text']").attr("disabled", 'disabled');
       jQuery("#qcopd_timelaps input[type='text']").prop("readonly",true);

    });


    jQuery(document).on( 'click', '.hero_tablinks', function(){
    	var evt =jQuery(this);
    	var cityName =jQuery(this).attr('data-id');
    	// console.log(cityName);
    	jQuery('.hero_tablinks').removeClass('hero_active');
    	jQuery(this).addClass('hero_active');

		var i, tabcontent, tablinks;
		tabcontent = document.getElementsByClassName("hero_tabcontent");
		for (i = 0; i < tabcontent.length; i++) {
		  tabcontent[i].style.display = "none";
		}
		
		document.getElementById(cityName).style.display = "block";

	});

	var sld_export_current_url = $('.sld_export_select_btn').attr('href');

	$(document).on( 'change', '#sld_export_select_list',function(){
	
      	var list_id = $(this).find(":selected").val();

    	var list_id_param = sld_export_current_url + '&list_id='+ list_id;

    	$('.sld_export_select_btn').attr('href', list_id_param);


	});


	var sld_from_date = $('.sld_from_date').length;

	if(sld_from_date){
	
	    $('.sld_from_date').datepicker({
	        dateFormat: 'yy-mm-dd',
	       // maxDate: 0,
	        //minDate: 0,
	        onSelect: function (date) {
	          var dt2 		= $('.sld_to_date');
	          var startDate = $(this).datepicker('getDate');
	          var minDate 	= $(this).datepicker('getDate');
	          if (dt2.datepicker('getDate') == null){
	            dt2.datepicker('setDate', minDate);
	          }      
	          //dt2.datepicker('option', 'maxDate', '0');
	          dt2.datepicker('option', 'minDate', minDate);
	        }
	    });
	    $('.sld_to_date').datepicker({
	        dateFormat: 'yy-mm-dd',
	        //maxDate: 0
	       // minDate: 0
	    });    

	}



	});


})(jQuery);