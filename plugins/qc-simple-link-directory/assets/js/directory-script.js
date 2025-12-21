(function($){
	"use strict";
	//Code here

	function setCookie(cname, cvalue, exdays) {
	    var d = new Date();
	    d.setTime(d.getTime() + (exdays*24*60*60*1000));
	    var expires = "expires="+ d.toUTCString();
	    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
	}
	var filter = [];

	var sld_inclusive_tag_filter = sld_ajax_object.inclusive_tag_filter;
	var sld_enable_image_loaded = sld_ajax_object.sld_enable_image_loaded;

	jQuery(document).ready(function($){
	    
	    //Masonary Grid
	    var grid = $('.qc-grid:not(.qc-style4-grid)').packery({
	      itemSelector: '.qc-grid-item',
	      gutter: 10
	    });
		
		$(document).on('click', '.sldp-holder > a', function(e){
			
			setTimeout(function(){
				 $('.qc-grid:not(.qc-style4-grid)').packery({
				  itemSelector: '.qc-grid-item',
				  gutter: 10
				});
			},500)
			
		})

	    //Filter Directory Lists
		$(document).on("click",".filter-area a", function(event){	   

	        event.preventDefault();

			if(jQuery('.qcopd-list-wrapper:visible').siblings('.sld-tag-filter-area').find('.sld_tag_filter').length>0){
				if( jQuery('.qcopd-list-wrapper:visible').siblings('.sld-tag-filter-area').find('.sld_tag_filter:not(:first)').hasClass('sld_tag_filter-active') ){
					jQuery('.qcopd-list-wrapper:visible').siblings('.sld-tag-filter-area').find('.sld_tag_filter:first').addClass('sld_tag_filter-active').trigger('click');

				}else{
					//console.log('default tag is active');
					jQuery('.qcopd-list-wrapper:visible').siblings('.sld-tag-filter-area').find('.sld_tag_filter:first').removeClass('sld_tag_filter-active').trigger( "click" );
				}
			}
			
			
	        var filterName = $(this).attr("data-filter");

	        //console.log( filterName );

	        $(".filter-area a").removeClass("filter-active");
	        $(this).addClass("filter-active");

	        if( filterName == "all" ) {

	            $("#opd-list-holder .qc-grid-item").css("display", "block");
	            $("#opd-list-holder .qc-grid-item").each(function(){
	            	if( !jQuery(this).hasClass('style-15') && !jQuery(this).hasClass('style-15-multipage') && !jQuery(this).children('.qcopd-list-column').hasClass('style-10') ){

						if( jQuery(this).find('.qcopd-single-list').is(':visible') ){
							jQuery(this).css("display", "block");
						}else{
							jQuery(this).css("display", "none");
						}
	            	}
	            	if( jQuery(this).children('div').hasClass('advertise-block') ){
	            		jQuery(this).css("display", "block");	
	            	}
				});

				// if( $(this).parent('.filter-area').prev('.sld-tag-filter-area').find('.sld_tag_filter-active').length < 1 || $(this).parent('.filter-area').prev('.sld-tag-filter-area').find('.sld_tag_filter-active').data('tag') == '' ){
				// 	$(this).parent('.filter-area').next('.qcopd-list-wrapper').find("#opd-list-holder .qc-grid-item").css("display", "block");
				// 	$(this).parent('.filter-area').next('.qcopd-list-wrapper').find("#opd-list-holder .qc-grid-item .qcopd-single-list").css("display", "block");
				// 	$(this).parent('.filter-area').next('.qcopd-list-wrapper').find("#opd-list-holder .qc-grid-item .qcopd-single-list li").addClass('showMe').css("display", "block");
				// 	$("#opd-list-holder .qc-grid-item").each(function(){
				// 		jQuery(this).css("display", "block");
				// 		jQuery(this).find(".qcopd-single-list").css("display", "block");
				// 		jQuery(this).find(".qcopd-single-list li").addClass('showMe').css("display", "block");
				// 	});
				// }

	        }else{
	        	//console.log( filterName );

	            $("#opd-list-holder .qc-grid-item").css("display", "none");
	            $("#opd-list-holder .qc-grid-item."+filterName+"").css("display", "block");

	            if( sld_inclusive_tag_filter == 'off' ){

		            jQuery("#opd-list-holder .qc-grid-item."+filterName+"").find('.qcopd-single-list').css("display", "block");
		            jQuery("#opd-list-holder .qc-grid-item."+filterName+"").find('div:first').css("display", "block");
		            jQuery("#opd-list-holder .qc-grid-item."+filterName+"").find('.qcopd-single-list li').css("display", "block");

		        }

	           
	        }
			
			if(sld_ajax_object.rtl=='on'){
				$('.qc-grid:not(.qc-style4-grid)').packery({
				  itemSelector: '.qc-grid-item',
				  gutter: 10,
				  originLeft: false
				});
			}else{
				$('.qc-grid:not(.qc-style4-grid)').packery({
				  itemSelector: '.qc-grid-item',
				  gutter: 10,
				  
				});
			}
	        

	    });

		$('.sld_tag_filter_select').on('change', function(){
			var value = jQuery(this).val();
			jQuery('.sld_tag_filter').removeClass('sld_tag_filter-active');
			localStorage.setItem("sld_tag_filter_select",  1 );
			jQuery(".sld-tag-filter-area a[data-tag='"+value+"']").trigger('click');

			
		});

		$('.sld-filter-area-select-mobile select').on('change', function(){
			var value = jQuery(this).val();
			jQuery('.filter-btn').removeClass('filter-active');
			localStorage.setItem("sld_tag_filter_select",  1 );
			if( value == 'all'){
				jQuery(".filter-btn[data-filter='"+value+"']").trigger('click');
			}else{
				jQuery(".filter-btn[data-filter='opd-list-id-"+value+"']").trigger('click');

			}
		});

		$(document).on("click",".sld-tag-filter-area a", function(event){
			
			event.preventDefault();

	        //$(".sld-tag-filter-area a").removeClass("sld_tag_filter-active");
	        $(this).toggleClass("sld_tag_filter-active");

	        var currently_tag = $(this).attr('data-tag');
			if( !currently_tag && $(this).hasClass('sld_tag_filter-active') ){
				currently_tag = 'all';
				filter = [];
			}

			if( sld_inclusive_tag_filter == 'on' && currently_tag == 'all' ){
				jQuery(this).siblings('.sld_tag_filter').removeClass('sld_tag_filter-active');
			}
			
			// Retrieve the input field text and reset the count to zero
	       //var filter = $(this).attr('data-tag');
	       var current_tag = $(this).attr('data-tag');
	       	
	        if($(this).hasClass('sld_tag_filter-active')){

	        	if(localStorage.getItem("sld_tag_filter_select" ) == 1){
	        	 	filter = [];
	        	 	localStorage.setItem("sld_tag_filter_select", '');
	        	}
				
	        	filter.push(current_tag);

	        	if( sld_inclusive_tag_filter != 'on' ){
					jQuery('.sld_tag_filter').removeClass('sld_tag_filter-active');
					jQuery(this).addClass('sld_tag_filter-active');

					filter = [];
	        		filter.push(current_tag);
				}


			}else{
				var index = filter.indexOf(current_tag);
			    if (index > -1) {
			       filter.splice(index, 1);
			    }
			}

	       var count = 0;

	       //Deselct "All" when clicked on any other tag
	        if( current_tag ){
	        	filter = filter.filter(function(item, pos) {
				    return item;
				});
				jQuery(this).siblings().eq(0).removeClass('sld_tag_filter-active');
	        }
	        
	       var filtered_array = filter.filter(function( currentValue, index, arr ){
	       		return arr.indexOf(currentValue) == index;
	       });

	       if( !jQuery('.qcopd-list-wrapper:visible').siblings('.sld-tag-filter-area').find('.sld_tag_filter:first').hasClass('sld_tag_filter-active') ){
	       		var index = filtered_array.indexOf('');
			    if (index > -1) {
			       filtered_array.splice(index, 1);
			    }
	       }

	       // When Select "all" tag, deselct all previous tags
	       if( ( !current_tag && $(this).hasClass('sld_tag_filter-active') ) ){
	       		$(this).siblings('.sld_tag_filter').removeClass('sld_tag_filter-active');
	       		filtered_array = [];
	       	}

	       	//When DeSelect "all" tag, selct all previously active tags
	       	if( !current_tag && !$(this).hasClass('sld_tag_filter-active') ){
	       		if( filtered_array && filtered_array.length > 0 ){
	       			for (var i = 0; i < filtered_array.length ; i++) {
	       				$(this).siblings('.sld_tag_filter[data-tag="'+filtered_array[i]+'"]').addClass('sld_tag_filter-active');
	       			}
	       		}
	       	}



	     
	       if( sld_inclusive_tag_filter == 'on' ){
	        	var search_term = new RegExp(filtered_array.join('|'), "ig");
	        }else{
	        
	        	var search_term = new RegExp(filtered_array.join('|'), "i");
	        }
	       
	     
	        // Loop through the comment list

	        //$("#opd-list-holder:visible ul li").each(function(){
	        $(".qcopd-list-wrapper:visible ul li").each(function(){

	        	// qcsld_CheckArray

	        	if( sld_inclusive_tag_filter == 'off' ){
	        		jQuery("#opd-list-holder").find('div').css("display", "block");
	        	}

	            var dataTitleTxt = $(this).children('a').attr('data-tag');

	            if( jQuery(this).parents('.qc-grid-item').hasClass('style-13') ){
					dataTitleTxt = $(this).find('.ilist-item-main').children('a').attr('data-tag');            	
				}

				if( jQuery(this).parents('.qc-grid-item').hasClass('style-14') ){
					dataTitleTxt = $(this).find('.sld-hover-content').children('a[data-tag]').attr('data-tag');            	
				}

				if( jQuery(this).parents('.qc-grid-item').children('div').hasClass('style-4') ){
					dataTitleTxt = $(this).find('.ca-content a').attr('data-tag');            	
				}

	            if( typeof(dataTitleTxt) == 'undefined' ){
	                dataTitleTxt = "-----";
	            }
	            // Check inclusive Tag Filter
	 			if( sld_inclusive_tag_filter == 'on' && currently_tag != 'all' ){
	 				// If the list item does not contain the text phrase fade it out
	 				// var checked_array = dataTitleTxt.match(search_term);
	 				var main_array = dataTitleTxt.split(',');
	 				var match_array = qcsld_CheckArray(main_array, filtered_array);
	 				// If the list item does not contain the text phrase fade it out

					// console.log('main_array');
					// console.log(main_array);
		            if ( match_array ) {
			            // if ( dataTitleTxt.search( search_term ) < 0 ) {
							// console.log(filtered_array, dataTitleTxt);
			                $(this).show();
							$(this).addClass("showMe");
				            // Show the list item if the phrase matches and increase the count by 1
			            // }
		            }else {
		                $(this).fadeOut();
						$(this).removeClass("showMe");		
		            }
	 			}

	 			if( sld_inclusive_tag_filter != 'on' || currently_tag == 'all' ){
	 				// If the list item does not contain the text phrase fade it out
		            if ( dataTitleTxt.search( search_term ) < 0 ) {
		                $(this).fadeOut();
						$(this).removeClass("showMe");
		            	// Show the list item if the phrase matches and increase the count by 1
		            }
		            else {
						// console.log('search_term');
						// console.log(dataTitleTxt.search( search_term ));
						// console.log(filter, dataTitleTxt);
		                $(this).show();
						$(this).addClass("showMe");
		                

		            }
	 			}
				
	        });

	    
	        if( $(this).parent('.sld-tag-filter-area').find('.sld_tag_filter-active').length < 1 || $(this).parent('.sld-tag-filter-area').find('.sld_tag_filter-active').data('tag') == '' ){
	        	if( jQuery(this).parent('.sld-tag-filter-area').next('.filter-area').find('.filter-btn.filter-active').data('filter') == 'all' ){
	        		jQuery(this).parent('.sld-tag-filter-area').siblings('.qcopd-list-wrapper').find('#opd-list-holder .qc-grid-item').each(function(){
	        			jQuery(this).css("display", "block");
	        		});
	        		jQuery(this).parent('.sld-tag-filter-area').next('.qcopd-list-wrapper').find(".qcopd-single-list li").addClass('showMe').css("display", "block");
	        	}
	        }
			
			$(".qcopd-single-list, .qcopd-single-list-1, .opd-list-style-8, .opd-list-style-9, opd-list-style-12, .style-15, .sld-container").each(function(){
	            
				var visibleItems = $(this).find("li.showMe").length;
				$(this).find("li").removeClass('jp-hidden');
				// console.log(visibleItems);
				
				if(visibleItems==0){
					$(this).hide();
					$(this).parent().hide();
				}else{
					$(this).show();
					$(this).parent().show();
				}
			});

			
			setTimeout(function(e){
				var grid = $('.qc-grid:not(.qc-style4-grid)');
				
				if(sld_ajax_object.rtl=='on'){
					grid.packery('destroy').packery({
					  itemSelector: '.qc-grid-item',
					  gutter: 10,
					  originLeft: false
					});
				}else{
					grid.packery('destroy').packery({
					  itemSelector: '.qc-grid-item',
					  gutter: 10,
					  
					});
				}
				
			},1000);
			
			var grid = $('.qc-grid:not(.qc-style4-grid)');
			grid.packery('destroy').packery({
			  itemSelector: '.qc-grid-item',
			  gutter: 10
			});
			setTimeout(function(e){
				var total_items = 0;
				jQuery('.qcopd-list-column:visible').each(function(){
					var current_list_items = jQuery(this).find('.qcopd-single-list li.showMe').length;
					if( jQuery(this).find('.list-style-seven.showMe').length > 0 ){
						current_list_items = jQuery(this).find('.list-style-seven.showMe').length;
					}
					total_items += current_list_items;
				});

				if( sld_inclusive_tag_filter == 'on' ){
					jQuery('.qcopd-list-wrapper:visible').siblings('.filter-area').find('.filter-active .opd-item-count-fil').text('('+total_items+')');
				}

				//console.log( total_items );
			},1000);

			
			jQuery(document).trigger('sld_pagination_tag_filter_refresh');
		})
		
		
	    //UpvoteCount
		$(document).on("click", '.upvote-btn', function(e){
	    
			e.preventDefault();
			e.stopPropagation();
			
			var chk = $(this);
			
			if ( chk.data('requestRunning') ) {
				return;
			}

			chk.data('requestRunning', true);
			
			if(typeof(allowupvote)!=='undefined' && allowupvote && !bookmark.is_user_logged_in){
				var slduserMessage = 'You have to login to upvote an item';
				
				if(upvoteloginurl!=''){
					if (confirm(slduserMessage)) {
						window.location.href = upvoteloginurl;
					}
				}else{
					alert('You have to login to upvote an item.');		
				}

				return;
			}
			
			
			if($(this).hasClass('sld_upvote_animation')){
				$(this).removeClass('sld_upvote_animation')
			}
			
			$(this).addClass('sld_upvote_animation');
			
	        var data_id = $(this).attr("data-post-id");
	        var data_title = $(this).attr("data-item-title");
	        var data_link = $(this).attr("data-item-link");

	        var parentLI = $(this).closest('li').attr("id");
			var uniqueId = $(this).attr("data-unique");
			
			var widgetab = $(this).closest('.qc-column-4').attr('id');

	        var selectorBody = $('.qc-grid-item span[data-post-id="'+data_id+'"][data-item-title="'+data_title+'"][data-item-link="'+data_link+'"]');

	        var selectorWidget = $('.widget span[data-post-id="'+data_id+'"][data-item-title="'+data_title+'"][data-item-link="'+data_link+'"]');

	        var bodyLiId = $(".qc-grid-item").find(selectorBody).closest('li').attr("id");
	        var WidgetLiId = $(selectorWidget).closest('li').attr("id");

	        //alert( bodyLiId );
			setCookie('usnidg',sld_ajax_object.ajax_nonce,1);
	        $.post(ajaxurl, {
	            action: 'qcopd_upvote_action', 
	            post_id: data_id,
	            meta_title: data_title,
	            meta_link: data_link,
	            li_id: parentLI,
				uniqueid: uniqueId,
				security:sld_ajax_object.ajax_nonce
	                
	        }, function(data) {
	            var json = $.parseJSON(data);
	            //console.log(json.cookies);
	            //console.log(json.exists);
	            if( json.vote_status == 'success' )
	            {

	            	chk.closest('.upvote-section').find('.upvote-count').html(json.votes);

	                $('#'+parentLI+' .upvote-section .upvote-count').html(json.votes);
	                $('#'+parentLI+' .upvote-section .upvote-btn').css("color", "green");
	                $('#'+parentLI+' .upvote-section .upvote-count').css("color", "green");

	                $('#'+bodyLiId+' .upvote-section .upvote-count').html(json.votes);
	                $('#'+bodyLiId+' .upvote-section .upvote-btn').css("color", "green");
	                $('#'+bodyLiId+' .upvote-section .upvote-count').css("color", "green");

	                $('#'+WidgetLiId+' .upvote-section .upvote-count').html(json.votes);
	                $('#'+WidgetLiId+' .upvote-section .upvote-btn').css("color", "green");
	                $('#'+WidgetLiId+' .upvote-section .upvote-count').css("color", "green");
					
					$('#'+widgetab+' .upvote-section .upvote-count').html(json.votes);
	                $('#'+widgetab+' .upvote-section .upvote-btn').css("color", "green");
	                $('#'+widgetab+' .upvote-section .upvote-count').css("color", "green");

	                if( jQuery('body').hasClass('page-template-template-home-v4') ){

						$('#'+WidgetLiId+' .upvote-section .upvote-count').html(json.votes);
					    $('#'+WidgetLiId+' .upvote-section .upvote-btn').css("color", "green");
					    $('#'+WidgetLiId+' .upvote-section .upvote-count').css("color", "green");
						
						var widgetab = chk.parents('.featured-block').parent().attr('id');
						$('#'+widgetab+' .upvote-section .upvote-count').html(json.votes);
					    $('#'+widgetab+' .upvote-section .upvote-btn').css("color", "green");
					    $('#'+widgetab+' .upvote-section .upvote-count').css("color", "green");
					}
	            }

	        });
	       
	    });
		$(document).on("click",".sld-upvote-btn-single", function(event){
	    
			
			var chk = $(this);
			event.preventDefault();
			event.stopPropagation();
			if ( chk.data('requestRunning') ) {
				return;
			}

			chk.data('requestRunning', true);
			
			if($(this).hasClass('sld_upvote_animation')){
				$(this).removeClass('sld_upvote_animation')
			}
			$(this).addClass('sld_upvote_animation');
	        var data_id = $(this).attr("data-post-id");
	        var data_title = $(this).attr("data-item-title");
	        var data_link = $(this).attr("data-item-link");
			var uniqueId = $(this).attr("data-unique");
	        //alert( bodyLiId );
	        $.post(ajaxurl, {            
	            action: 'qcopd_upvote_action', 
	            post_id: data_id,
	            meta_title: data_title,
	            meta_link: data_link,
	            li_id: '',
				uniqueid: uniqueId,
				security:sld_ajax_object.ajax_nonce
	                
	        }, function(data) {
	            var json = $.parseJSON(data);
	            //console.log(json.cookies);
	            //console.log(json.exists);
	            if( json.vote_status == 'success' )
	            {
	                $('.upvote-section-style-single .upvote-count').html(json.votes);
	                $('.upvote-section-style-single .upvote-on').css("color", "green");
	                $('.upvote-section-style-single .upvote-count').css("color", "green");

	            }
	        });
	       
	    });
		
		/*$(document).on("click",".open-mpf-sld-more", function(e){
			e.preventDefault();
			var data_id = $(this).attr("data-post-id");
	        var data_title = $(this).attr("data-item-title");
	        var data_link = $(this).attr("data-item-link");
			var container = $(this).attr("data-mfp-src");
			
			$.post(ajaxurl, {            
	            action: 'qcopd_load_long_description', 
	            post_id: data_id,
	            meta_title: data_title,
	            meta_link: data_link,

	        }, function(data) {
				$(container+' .sld_more_text').html(data);
	        });
		})*/
		
		$('.sld_load_more').click(function(e){
			e.preventDefault();
			e.stopPropagation();
			
			var data_id = $(this).attr("data-post-id");
	        var data_title = $(this).attr("data-item-title");
	        var data_link = $(this).attr("data-item-link");
			var container = $(this).attr("data-mfp-src");
			
			var upvote = 'on';
			if(jQuery('.upvote-btn').length<1){
				var upvote = 'off';
			}
			
			
			$.post(ajaxurl, {            
	            action: 'qcopd_load_long_description', 
	            post_id: data_id,
	            meta_title: data_title,
	            meta_link: data_link,
				upvote: upvote,
				security:sld_ajax_object.ajax_nonce

	        }, function(data) {
				$(container+' .sld_more_text').html(data);
	        });
		});

		$(document).on('click', '#sld_bookmark_ul .sld_load_more', function(e){
			var list_id = jQuery(this).parents('li').attr('data-main-li-id');
			jQuery('#'+list_id).find('.open-mpf-sld-more').trigger('click');
		});
		

		
		$('.sld_load_video').click(function(e){
			e.preventDefault();
			
	        var video_link = $(this).attr("data-videourl");
			var container = $(this).attr("data-mfp-src");
			
			$.post(ajaxurl, {            
	            action: 'qcopd_load_video', 
	            videurl: video_link,
				security:sld_ajax_object.ajax_nonce

	        }, function(data) {
				$(container+' .sld_video').html(data);
	        });
		})
		
		
		
		$(document).on('click', '.bookmark-btn', function(e){
			e.preventDefault();
			e.stopPropagation();
			var data_id = $(this).attr("data-post-id");
	        var item_code = $(this).attr("data-item-code");
			var is_bookmarked = $(this).attr("data-is-bookmarked");
			var li_id = $(this).attr('data-li-id');
			
			var parentLi = $(this).closest('li').attr('id');
			
			var obj = $(this);

			//console.log(bookmark.is_user_logged_in);
			
			if(!bookmark.is_user_logged_in){
				if(typeof login_url_sld ==="undefined" || login_url_sld==''){
					if(typeof(slduserMessage)!=="undefined" && slduserMessage!==''){
						alert(slduserMessage);
					}else{
						alert('You need to log in to add items to your favorite list.');
					}
					
				}else{
					
					if(typeof(slduserMessage)!=="undefined" && slduserMessage!==''){
						
						if (confirm(slduserMessage)) {
							window.location.href = login_url_sld;
						} else {
							// Do nothing!
						}	
						
					}else{
						if (confirm('You need to log in to add items to your favorite list.')) {
							window.location.href = login_url_sld;
						} else {
							// Do nothing!
						}	
					}
					
								
				}
			}else{
				
				if(is_bookmarked==0){
					
					$.post(ajaxurl, {
					action: 'qcopd_bookmark_insert_action', 
					post_id: data_id,
					item_code: item_code,
					userid: bookmark.userid,

					}, function(data) {
						
						obj.attr('data-is-bookmarked',1);
						obj.children().removeClass('fa-star-o').addClass('fa-star');
						
						if(typeof(template)!=="undefined"){
							var newliid = parentLi+'_clone';
							var cloneElem = $('#'+parentLi).clone();

							cloneElem.attr('data-main-li-id',parentLi);
							cloneElem.find('.open-mpf-sld-more2').removeClass('sld_load_more2').addClass('sld_bookmark_load_more-added'); //add for fixing bookmark popup not open
							cloneElem.prop({ id: newliid});
							cloneElem.find('.upvote-section').remove();
							cloneElem.find('.bookmark-section').find('span').attr("data-li-id",newliid);
							cloneElem.find('.bookmark-section').find('span').find('i').removeClass('fa-star').addClass('fa-times-circle');
							cloneElem.prependTo("#sld_bookmark_ul");
							jQuery('#bookmark_list').css("display", "block");
							jQuery('#bookmark_list .qcopd-single-list').css("display", "block");
							$('.qc-grid:not(.qc-style4-grid)').packery({
							  itemSelector: '.qc-grid-item',
							  gutter: 10
							});
							jQuery(document).trigger('addBookmarkSlider');
						}
						
						
					});
					
				}else{
					$.post(ajaxurl, {
					action: 'qcopd_bookmark_remove_action', 
					post_id: data_id,
					item_code: item_code,
					userid: bookmark.userid,

					}, function(data) {
						if(typeof li_id === "undefined" || li_id==''){
							obj.attr('data-is-bookmarked',0);
							obj.children().removeClass('fa-star').addClass('fa-star-o');
						}else{
							obj.closest('li').remove();
							$('.qc-grid:not(.qc-style4-grid)').packery({
							  itemSelector: '.qc-grid-item',
							  gutter: 10
							});
						}
						
						
					});
				}
				
				
				
			}

			
		})
		
		jQuery(document).on('click', '.mfp-close', function(e){
			jQuery.magnificPopup.close();
		})
		

	});

	jQuery(document).ready(function($){
		
		if(sld_ajax_object.main_click_upvote=='on'){
			
			$('#opd-list-holder ul li a, .widget-sld-list li a').on('click', function(e){

				if($(this).parent('li').find('.upvote-btn').length>0){
				
					var data_id = $(this).parent('li').find('.upvote-btn').attr("data-post-id");
					var data_title = $(this).parent('li').find('.upvote-btn').attr("data-item-title");
					var data_link = $(this).parent('li').find('.upvote-btn').attr("data-item-link");

					var parentLI = $(this).parent('li').find('.upvote-btn').closest('li').attr("id");
					var uniqueId = $(this).parent('li').find('.upvote-btn').attr("data-unique");
					
					var widgetab = $(this).parent('li').find('.upvote-btn').closest('.qc-column-4').attr('id');

					var selectorBody = $('.qc-grid-item span[data-post-id="'+data_id+'"][data-item-title="'+data_title+'"][data-item-link="'+data_link+'"]');

					var selectorWidget = $('.widget span[data-post-id="'+data_id+'"][data-item-title="'+data_title+'"][data-item-link="'+data_link+'"]');

					var bodyLiId = $(".qc-grid-item").find(selectorBody).closest('li').attr("id");
					var WidgetLiId = $(selectorWidget).closest('li').attr("id");

					//alert( bodyLiId );
					setCookie('usnidg',sld_ajax_object.ajax_nonce,1);
					$.post(ajaxurl, {
						action: 'qcopd_upvote_action2', 
						post_id: data_id,
						meta_title: data_title,
						meta_link: data_link,
						li_id: parentLI,
						uniqueid: uniqueId,
						security:sld_ajax_object.ajax_nonce
							
					}, function(data) {
						var json = $.parseJSON(data);
						//console.log(json);
						//console.log(json.exists);
						
					});
				
				}
			})
		}
		
		//console.log(statistic);
		
		if(typeof(statistic)!=='undefined' && statistic==true){
			
			$('#opd-list-holder ul li a').on('click', function(e){
				if( jQuery(this).hasClass('sld_internal_link') ){
					return;
				}
				e.preventDefault();
				if($(this).hasClass('sld_load_more2'))
					return;
				if($(this).hasClass('sld_load_video')){
					if( $(this).attr('target') == '_blank'){
					}else{
						return;
					}
				}
				if($(this).data('itemurl')!='' && $(this).data('itemid')!=''){
					var itemid = $(this).data('itemid');
					var itemurl = $(this).data('itemurl');
					var itemsid = $(this).data('itemsid');
					
					if(typeof($(this).attr('target'))==='undefined'){
						window.open(itemurl,"_self");
					}else{
						window.open(itemurl);
					}
					
					
					$.post(ajaxurl, {
						action: 'qcopd_item_click_action', 
						itemid: itemid,
						itemurl: itemurl,
						itemsid: itemsid,
						security:sld_ajax_object.ajax_nonce
					}, function(data) {
						//window.location.href = itemurl;
						
					});
					
				}
			})
			
		}
		
		
		$('.sld_load_more2').click(function(e){
			e.preventDefault();
			var data_id = $(this).attr("data-post-id");
	        var data_title = $(this).attr("data-item-title");
	        var data_link = $(this).attr("data-item-link");
			var container = $(this).attr("data-mfp-src");
			
			var upvote = 'on';
			if(jQuery('.upvote-btn').length<1){
				var upvote = 'off';
			}
			
			$.post(ajaxurl, {            
	            action: 'qcopd_load_long_description', 
	            post_id: data_id,
	            meta_title: data_title,
	            meta_link: data_link,
				upvote: upvote,
				security:sld_ajax_object.ajax_nonce

	        }, function(data) {
				$(container+' .sld_more_text').html(data);
	        });
		});

		$(document).on('click', '.sld_bookmark_load_more-added', function(e){
			e.preventDefault();
			var cloned_parent = jQuery(this).parents('li').data('main-li-id');
			jQuery(document).find('#'+cloned_parent+' .sld_load_more2').trigger('click');
		});

		$(document).on('click', '.sld_bookmark_load_more', function(e){
			
			e.preventDefault();
			var data_id = $(this).attr("data-post-id");
	        var data_title = $(this).attr("data-item-title");
	        var data_link = $(this).attr("data-item-link");
			var container = $(this).attr("data-mfp-src");
			
			var upvote = 'on';
			if(jQuery('.upvote-btn').length<1){
				var upvote = 'off';
			}
			
			
			$.post(ajaxurl, {            
	            action: 'qcopd_load_long_description', 
	            post_id: data_id,
	            meta_title: data_title,
	            meta_link: data_link,
				upvote: upvote,
				security:sld_ajax_object.ajax_nonce

	        }, function(data) {
				$(container+' .sld_more_text').html(data);
	        });
		});
	})




	jQuery(document).ready(function($){


	    $(".sld_search_filter").keyup(function(){
	    	jQuery('#opd-list-holder').find('.qc_sld_not_item_found').remove();

	        // Retrieve the input field text and reset the count to zero
	        var filter = $(this).val().trim(), count = 0;
	        filter = filter.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
	        var filter_multi_string = $(this).val();

	        var checkDomWrap = $(this).closest('form');
	        var sld_search_filter_clear = checkDomWrap.find('.sld_search_filter_clear');
	       	checkDomWrap.removeClass('sld_search_filter_clear_wrap');
	       	sld_search_filter_clear.remove();
	       	if ( filter != '' ) {
		       	checkDomWrap.addClass('sld_search_filter_clear_wrap');
		        $(this).after('<i class="fa fa-times sld_search_filter_clear"></i>');
		    }

	        if ( filter != '' && filter.length < 2 ) {
	            return;
	        }
	        
	        //console.log( filter );
			var selector = $('#opd-list-holder ul li');
			if($('.qcld_sld_tabcontent').length>0){
				selector = $('.qcld_sld_tabcontent:visible #opd-list-holder ul li');
			}
	 
	        // Loop through the comment list
	        selector.each(function(){

	        	//console.log( filter_multi_string );

	        	$(this).removeClass('jp-hidden');

	            var dataTitleTxt = $(this).children('a').attr('data-title');
	            var dataSubtitle = $(this).children('a').attr('data-subtitle') ? $(this).children('a').attr('data-subtitle') : $(this).find('a').attr('data-subtitle');
	            var datatag = $(this).children('a').attr('data-tag');
	            var dataurl = $(this).find('a').attr('href');


	            if( typeof(dataurl) == 'undefined' ){
	                dataurl = "-----";
	            }
				if( typeof(dataSubtitle) == 'undefined' ){
	                dataSubtitle = "-----";
	            }
				if( typeof(datatag) == 'undefined' ){
	                datatag = "-----";
	            }


	            if( typeof(dataTitleTxt) == 'undefined' ){
	                dataTitleTxt = "-----";
	            }

	            var parentH3 = $(this).parentsUntil('.qc-grid-item').children('h3').text();
	            
	            //var pattern = filter_multi_string.trim().match(/[a-zA-Z]+|[0-9]+/g) || ""; 
	            var pattern = filter_multi_string.trim().match(/[a-zA-Z0-9-’/`~!#*$@_%+=.,^&(){}[\]|;:”<>?\\]+/g) || ""; 
				if(pattern) {
					pattern = pattern.map(function(el) { 
					  return '(?=.*' + el.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&") + ')';
					});
					pattern = pattern.join("");
				}
	 
	            // If the list item does not contain the text phrase fade it out
	            if ($(this).text().search(new RegExp(pattern, "i")) < 0 && dataurl.search(new RegExp(filter, "i")) < 0 && dataTitleTxt.search(new RegExp(filter, "i")) < 0 && parentH3.search(new RegExp(filter, "i")) < 0 && dataSubtitle.search(new RegExp(filter, "i")) < 0 && datatag.search(new RegExp(filter, "i")) < 0 ) {
	                //$(this).fadeOut();
	                $(this).hide();
					$(this).removeClass("showMe");		
	 
	            // Show the list item if the phrase matches and increase the count by 1
	            }
	            else {
	                $(this).show();
					$(this).addClass("showMe");
	                count++;
	            }

	        });
			
			var listholder = $(".qcopd-single-list, .qcopd-single-list-1, .opd-list-style-8, .opd-list-style-9, .opd-list-style-12, .sld-container");
			if($('.qcld_sld_tabcontent').length>0){
				var listholder = $(".qcld_sld_tabcontent:visible .qcopd-single-list, .qcld_sld_tabcontent:visible .qcopd-single-list-1, .qcld_sld_tabcontent:visible .opd-list-style-8, .qcld_sld_tabcontent:visible .opd-list-style-9, .qcld_sld_tabcontent:visible .opd-list-style-12, .qcld_sld_tabcontent:visible .sld-container");
			}

			listholder.each(function(){
	            
				var visibleItems = $(this).find("li.showMe").length;
				
				// console.log(visibleItems);
				
				if(visibleItems==0){
					$(this).hide();
					$(this).parent('.qcopd-list-column').hide();
				}else{
					$(this).show();
					$(this).parent('.qcopd-list-column').show();
				}

			});
			setTimeout(function(e){
				var grid = $('.qc-grid:not(.qc-style4-grid)');
				
				if(sld_ajax_object.rtl=='on'){

					grid.packery('destroy').packery({
					  itemSelector: '.qc-grid-item',
					  gutter: 10,
					  originLeft: false
					});

				}else{
					
					grid.packery('destroy').packery({
					  itemSelector: '.qc-grid-item',
					  gutter: 10,
					  
					});
				}
				
			},100);

			if(jQuery('#opd-list-holder').find('.qc-grid-item:visible').length < 1){
				jQuery('#opd-list-holder').prepend('<div class="qc_sld_not_item_found">'+sld_ajax_object.no_result_found_text+'</div>')
			}else{
				// console.log('result match');
			}


			// setTimeout(function(e){
				jQuery(document).trigger('sld_pagination_search_filter_refresh');
			// }, 900);

	    });

	  	  
	  	$(document).on('click', '.sld_search_filter_clear', function(){
	  		var checkDom = $(this);
	  		var checkDomWrap = checkDom.closest('form');
	  		var sld_search_filter = checkDomWrap.find('.sld_search_filter');
	    	sld_search_filter.val('');
	  		var sld_search_filter_clear_wrap = checkDomWrap.find('input[type="text"]');
	    	jQuery(sld_search_filter_clear_wrap).trigger('keyup');
	    	//$(this).remove();
	    });
	    

	    $('#live-search').on('submit',function(e){
	        e.preventDefault();
	    })

	    $('#captcha_reload').on('click', function(e){
	        e.preventDefault();
	        $.post(
	            ajaxurl,
	            {
	                action : 'qcld_sld_change_captcha',
					security:sld_ajax_object.ajax_nonce
	            },
	            function(data){
	                $('#sld_captcha_image').attr('src', data);
	            }
	        );
	    })
		
		$('.qcld_sld_tablinks').on('click',function(){
				$('.qc-grid:not(.qc-style4-grid)').packery({
				  itemSelector: '.qc-grid-item',
				  gutter: 10
				});
		})
		

		$('.open-mpf-sld-more').magnificPopup({
		  type:'inline',
		  mainClass: 'sld-mfp-mainclass',
		  midClick: true
		});
		$('.open-mpf-sld-more2').magnificPopup({
		  type:'inline',
		  mainClass: 'sld-mfp-mainclass',
		  midClick: true
		});
		$('.open-mpf-sld-video').magnificPopup({
		  type:'inline',
		  mainClass: 'mfp-with-nopadding',
		  midClick: true
		});
		$('.sld_2co_btn').magnificPopup({
		  type:'inline',
		  mainClass: 'mfp-with-2co',
		  midClick: true
		});
		
		
		/*
		$('#sld_enable_recurring').on('click', function(){
			
	        if(this.checked){
	            $('#paypalProcessor').hide();
	            $('#paypalProcessor_recurring').show();
			}
	        else{
	            $('#paypalProcessor').show();
	            $('#paypalProcessor_recurring').hide();
			}
			
		});
		*/
		$('.sld_enable_recurring_package').on('click', function(){
			var currentDom = $(this);
			var parentWrap = currentDom.closest('table');
			
	        if(this.checked){
	            parentWrap.find('#paypalProcessor').hide();
	            parentWrap.find('#paypalProcessor_recurring').show();
			}
	        else{
	            parentWrap.find('#paypalProcessor').show();
	            parentWrap.find('#paypalProcessor_recurring').hide();
			}
			
		});
		
		
		$('.sld_enable_recurring_dashboard').on('click', function(e){
			//e.preventDefault();
			var currentDom = $(this);
			var parentWrap = currentDom.closest('ul');
			
	        if(this.checked){
	            parentWrap.find('#paypalProcessor').hide();
	            parentWrap.find('#paypalProcessor_recurring').show();
			}
	        else{
	            parentWrap.find('#paypalProcessor').show();
	            parentWrap.find('#paypalProcessor_recurring').hide();
			}
			
		});

		$('#sld_claim_list').on('change',function(e){
			e.preventDefault();
			if($(this).val()!==''){
				$.post(
					ajaxurl,
					{
						action : 'qcld_sld_show_list_item',
						listid: $(this).val(),
						security:sld_ajax_object.ajax_nonce
					},
					function(data){
						$('#sld_list_item').html(data);
					}
				);
			}else{
				$('#sld_list_item').html('');
			}
			
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
						$('#sld_title').val(data.title);
						//console.log(data.title);
						$('#sld_subtitle').val(data.description);
						
					});
					
				}else{
					alert('Item link field cannot left empty!');
				}

			
		})
		
		
	});
	jQuery(window).on('load',function(){
		// jQuery('#sld_tags').tagInput();
		jQuery(document).on('click','.closelabel',function(e){
			e.preventDefault();
		})
		jQuery(document).on('click','.fa-field-modal-close',function(e){
			e.preventDefault();
			jQuery('.fa-field-modal').hide();
		})
		jQuery(document).on('focus', '#sld_tags', function(e){
			var elem = jQuery(this);
			var elemid = this.id;
			// jQuery('.qcld_sld_ajax_loader').removeClass('qc-sld-d-none');
			jQuery.post(ajaxurl, {
				action: 'qcopd_tag_pd_page', 
				security:sld_ajax_object.ajax_nonce
				
			},
				
			function(data) {
				// jQuery('.qcld_sld_ajax_loader').addClass('qc-sld-d-none');
				jQuery('body').append(data);
				jQuery('#sldtagvalue').val(elem.val());
				jQuery('#sld-tags').attr('data', elemid);
				
				//console.log(jQuery(data).find('.fa-field-modal-title').text());

				jQuery('#sld-tags').tagInput();
				jQuery('.labelinput').focus();
				jQuery.post(ajaxurl, {
					action: 'qcopd_search_pd_tags', 
					security:sld_ajax_object.ajax_nonce
				},
				
				function(data) {
					console.log(data);
					
					jQuery('.labelinput').autocomplete({
						  source: data.split(','),
						  
					});					
					
				});
					

				
			});
			
		});
		jQuery(document).on('click','#sld_tag_select', function(){
			jQuery('#'+jQuery('#sld-tags').attr('data')).val(jQuery('#sldtagvalue').val());
			jQuery('#fa-field-modal-tag').remove();
		})
		jQuery(document).on('click', '#sld_item_tags', function(e){
			var elem = jQuery(this);
			var elemid = this.id;
			jQuery.post(ajaxurl, {
				action: 'qcopd_tag_pd_page', 
				security:sld_ajax_object.ajax_nonce
			},
				
			function(data) {
				
				jQuery('#wpwrap').append(data);
				jQuery('#sldtagvalue').val(elem.val());
				jQuery('#sld-tags').attr('data', elemid);
				
				//console.log(jQuery(data).find('.fa-field-modal-title').text());

				jQuery('#sld-tags').tagInput();
				jQuery('.labelinput').focus();
				jQuery.post(ajaxurl, {
					action: 'qcopd_search_pd_tags', 
					security:sld_ajax_object.ajax_nonce
				},
				
				function(data) {
					console.log(data);
					
					jQuery('.labelinput').autocomplete({
						  source: data.split(','),
						  
					});					
					
				});
					
					

				
			});
			
		});

		if( sld_enable_image_loaded == 'on' ){
			var packery_grid = jQuery('.qc-grid:not(.qc-style4-grid)').packery({
			  itemSelector: '.qc-grid-item',
			  gutter: 10
			});
			// layout Packery after each image loads
			packery_grid.imagesLoaded().progress( function() {
			  packery_grid.packery();
			  console.log('imagesLoaded');
			});
		}else{
			jQuery('.qc-grid:not(.qc-style4-grid)').packery({
			  itemSelector: '.qc-grid-item',
			  gutter: 10
			});
		}

		// setTimeout(function(){
		// 	jQuery('.qc-grid').packery('destroy');
		// }, 3000);
		
		// jQuery('.style-2 .ca-menu li').each(function(){
		// 	var main_height = jQuery(this).height();
		// 	var title_height = jQuery(this).find('.ca-content').height();
		// 	if( jQuery(this).find('.ca-sub').length > 0 ){
		// 		var subtitle_height = jQuery(this).find('.ca-sub').height();
		// 	}else{
		// 		var subtitle_height = 0;
		// 	}
		// 	var content_height = title_height+subtitle_height;
		// 	if( content_height > main_height){
		// 		var original_height = content_height-main_height;
		// 	}else{
		// 		var original_height = main_height;
		// 	}

		// 	jQuery(this).css('height', original_height+title_height+subtitle_height);
		// 	jQuery(this).attr('data-height', original_height);
		// });
		
	})

	jQuery(document).ready(function() {
	   jQuery('.sld_search_filter').keypress(function(e) {
	       var code = (e.keyCode ? e.keyCode : e.which);
	  
	       	if ( (code==13) || (code==10)){

		        jQuery(this).trigger('blur');
		        return false;
	       }
	   });

	   jQuery(document).on('click','.sld_remove_preview_img',function(){
	   		jQuery(this).parent('#sld_preview_img').empty();
	   		jQuery('#sld_link_image').show();
	   });

	   jQuery(document).on('click', "#sld_generate_image_from_link", function(e){
			var objc = jQuery(this);
			if(jQuery('#sld_item_link').val()==''){
				alert('Could not generate image. Please check Link field and try again.');
			}
			if(jQuery('#sld_item_link').val()!='' && jQuery('#sld_link_image').val()==''){
				jQuery('body').find('.qcld_sld_ajax_loader').removeClass('qc-sld-d-none');
				jQuery('body').find('.qcld_sld_ajax_loader').css("display", "");
				jQuery.post(
					ajaxurl,
					{
						action: 'qcopd_img_download', 
						url: jQuery('#sld_item_link').val(),
						security:sld_ajax_object.ajax_nonce
					},
					function(data) {
						data = JSON.parse(data);
						console.log(data);
						jQuery('.qcld_sld_ajax_loader').addClass('qc-sld-d-none');

						
						if(data.imgurl.match(/.jpg/g) || data.imgurl!==null){
							// objc.closest('.cmb_metabox').find('#qcopd_item_img input').val(data.attachmentid);
							jQuery('#sld_link_image').hide();
							jQuery('#sld_preview_img').html('<img class="sld-auto-generated-preview-img" src="'+data.imgurl+'" width="150" height="150" /><div class="sld_remove_preview_img">Remove</div><input type="hidden" name="sld-auto-generate-img" value="'+data.imgurl+'" />');
						}else{
							alert('Could not generate image. Please check URL and try again.');
						}
						
					}
				);
				
			}	
		})
	});

	function qcsld_CheckArray(array1, array2){
		if( Array.isArray(array1) && Array.isArray(array2) ){
			//return array1.length === array2.length && array1.sort().every(function(value, index) { return value === array2.sort()[index]});
			return array2.every(function (v) {
		        return array1.includes(v);
		    });
		}else{
			return false;
		}
	}


	//var overflow = $(".qcopd-list-column").get(0);
	//alert( "Height: " + overflow.scrollHeight + "px Width: " + overflow.scrollWidth + "px" );

})(jQuery);




jQuery(document).ready(function($){

	$(".qcopd-list-wrapper ul li").each(function(){
		var currentDom = $(this);
  		var currentDomwrap = currentDom.find('a');
  		var myTextAll = currentDomwrap.attr('data-tag');
  		if( typeof(myTextAll) !== 'undefined' && myTextAll.length ){
  			var myRemoveSpaceAll = myTextAll.replace(', ',',') ? myTextAll.replace(', ',',') : myTextAll;
  			currentDomwrap.attr('data-tag', myRemoveSpaceAll);

  		}

  	});

	// remove blank href tag from anchor tag
    $(".qcopd-single-list ul li a").each(function(){
    	var sld_current_anchor = $(this).attr('href');
        if(sld_current_anchor == '' || sld_current_anchor == '#' ){
            $(this).removeAttr("href");
        }
    });

});