(function($){
  "use strict";

  jQuery(document).ready(function($){

    $('.sld-add-as-new').on('click', function(){
        return confirm("Do you realy want to import lists and elements from the attached CSV file?");
    });

    $('.delete-old').on('click', function(){
        return confirm("This option is irreversible. Do you really want to delete your current SLD lists with its elements and then add new entries from the attached CSV file?");
    });


    var sld_wp_color_picker = $(".sld_wp_color_picker").length;
    if(sld_wp_color_picker){
      $('.sld_wp_color_picker').wpColorPicker();
    }

  
  	
  });

})(jQuery);