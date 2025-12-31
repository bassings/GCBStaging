(function($){
	"use strict";
	//Code here

jQuery(document).ready(function($){
	
	    $('#sld-upload-btn').click(function(e) {
        e.preventDefault();
        var image = wp.media({ 
            title: 'Upload Image',
            multiple: false
        }).open()
        .on('select', function(e){
            
            var uploaded_image = image.state().get('selection').first();
            var image_url = uploaded_image.toJSON().url;
            $('#sld_pf_image_url').val(image_url);
			var html = ['<span class="sld_remove_bg_image">X</span>',
				'<img src="'+image_url+'" alt="" />'
			].join("");
			$('#sld_preview_img').html(html);
        });
    });
	
	$(document).on( 'click', '.sld_remove_bg_image', function(){
		
		$('#sld_preview_img').html('');
		$('#sld_pf_image_url').val('');
	})
	
	
	
	$('#qc_sld_category').on('change', function(){
		
		var city = $('#qc_sld_category').val();
		jQuery('.qcld_sld_ajax_loader').removeClass('qc-sld-d-none');
		$.post(
			ajaxurl,
			{
				action : 'qcld_sld_category_filter',
				cat : city,
			},
			function(data){
				jQuery('.qcld_sld_ajax_loader').addClass('qc-sld-d-none');
				$('#qc_sld_list').html(data);
				
			}
		);
		
	})
	
})


})(jQuery);