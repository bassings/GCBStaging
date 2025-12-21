<?php
/**
 * 
 */
if( !class_exists('QC_SLD_Help_License_Sub_Menu') ){

	class QC_SLD_Help_License_Sub_Menu
	{
		
		function __construct()
		{
			add_action('admin_menu', array($this, 'help_license_submenu') );
		}

		function help_license_submenu(){
			add_submenu_page( 
		        'edit.php?post_type=sld',
		        __('Help & License', 'qc-opd'),
		        __('Help & License', 'qc-opd'),
		        'manage_options',
		        'qcld_sld_help_license',
		        array($this, 'qcld_sld_help_license_callback')
		    );
		}

		function qcld_sld_help_license_callback(){
?>
			<div id="wrap">
				<div id="licensing">
					<?php sld_display_license_section(); ?>
					
					<div class="qcld-sld-help-section">
						<h1>Welcome to the Simple Link Directory Pro! You are awesome, by the way <img draggable="false" class="emoji" alt="🙂" src="https://s.w.org/images/core/emoji/11/svg/1f642.svg"></h1>

						<div class="qcld-sld-section-block">
							<h3 class="shortcode-section-title">Getting Started</h3>
							
							<p>Over the years we have added a lot of advanced features to this plugin which may be overwhelming when you first get started. But do not worry – getting started is super easy and once you are familiar with how the system works, you can take advantage of all the advanced features.</p>
							<p>The plugin works a little different from others. The most important thing to remember is that the <strong>base pillars of this plugin are Lists</strong>, not individual links or categories. A list is simply a niche or subtopic to group your relevant links together. The most common use of SLD is to create and display multiple Lists of Links on specific topics or subtopics on the same page. Everything revolves around the Lists. Once you create a few Lists, you can then display them in many different ways.</p>
							<p>With that in mind you should start with the following simple steps.</p>
							<p><br><span style="font-weight:bold;">1.</span> Go to New List and create one by giving it a name. Then simply start adding List items or links by filling up the fields you want. Use the <strong>Add New</strong> button to add more Listings in your list.</p>

							<p><br><span style="font-weight:bold;">2.</span> Though you can just create one list and use the Single List mode. This directory plugin works the best when you <strong>create a few Lists</strong> each conatining about <strong>15-20 List items</strong>. This is the most usual use case scenario. But you can do differently once you get the idea.</p>

							<p><br><span style="font-weight:bold;">3.</span> Now go to a page or post where you want to display the directory. On the right sidebar you will see a <strong>ShortCode Generator</strong> block. Click the button and a Popup LightBox will appear with all the options that you can select. Choose All Lists, and select a Style. Then Click Add Shortcode button. Shortcode will be generated. Simply <strong>copy paste</strong> that to a location on your page where you want the <strong>directory to show up</strong>.</p>
							<br>
							<p>That’s it! If you want to create a rather large directory or have more than one broad topics in mind then create some Categories from Category menu and assign your Lists to the relevant categories from the List edit page right panel. Then you can display Lists by a single category on different pages or use the multi page mode. But you can skip this step if you want just a single page directory.</p>
							<p>The above steps are for the basic usages. There are a lot of advanced options available. If you had any specific questions about how something works, do not hesitate to contact us from the <a href="<?php echo admin_url('edit.php?post_type=sld&page=qcpro-promo-page-qcld-sld-pro-1245support'); ?>">Support Page</a>. <img draggable="false" class="emoji" alt="🙂" src="https://s.w.org/images/core/emoji/11/svg/1f642.svg"></p>
						</div>
						
						<div class="qcld-sld-section-block">
							<h3 class="shortcode-section-title">Note</h3>
							<p><strong>If you are having problem with adding more items or saving a list or your changes in the list are not getting saved then it is most likely because of a limitation set in your server. Your server has a limit for how many form fields it will process at a time. So, after you have added a certain number of links, the server refuses to save the List. The server’s configuration that dictates this is max_input_vars. You need to Set it to a high limit like max_input_vars = 15000. Since this is a server setting - you may need to contact your hosting company\'s support for this.</strong></p>
								<h3 class="shortcode-section-title">Shortcode Generator</h3>
							<p>
							We encourage you to use the ShortCode generator found in the toolbar of your page/post editor in visual mode.</p> 
							
							<img src="<?php echo SLD_QCOPD_IMG_URL; ?>/classic.jpg" alt="shortcode generator" />
							
							<p>See sample below for where to find it for Gutenberg.</p>

							<img src="<?php echo SLD_QCOPD_IMG_URL; ?>/gutenburg.jpg" alt="shortcode generator" />						
							<img src="<?php echo SLD_QCOPD_IMG_URL; ?>/gutenburg2.jpg" alt="shortcode generator" />	<p>This is how the shortcode generator will look like.</p>				
							<img src="<?php echo SLD_QCOPD_IMG_URL; ?>/shortcode-generator1.jpg" alt="shortcode generator" />						
						</div>

						<div class="qcld-sld-section-block">
							<h3 class="shortcode-section-title">Enable Paypal IPN</h3>
							<p>
								If you are using PayPal for Front End Submissions, please enable <a href="https://www.paypal.com/businessmanage/account/notifications" target="_blank"> Instant Payment Notification (IPN) </a> from here.
							</p>
							<img src="<?php echo SLD_QCOPD_IMG_URL; ?>/paypal-1.png" alt="shortcode generator" />	
							<img src="<?php echo SLD_QCOPD_IMG_URL; ?>/paypal-2.png" alt="shortcode generator" />	
						</div>

						<div class="qcld-sld-section-block">
							<h3 class="shortcode-section-title">Shortcode for frontend submission</h3>
							<p>
								This feature will allow your users to submit their links to your lists from website front end. To achieve this you have to create 4 different pages and paste the following short code in each page.
							</p>
						</div>
						
						<div class="qcld-sld-section-block">
							<h3 class="shortcode-section-title">Shortcode for frontend submission</h3>
							<p>
								Please make sure that you have installed and activated sld plugin before adding these shortcodes.
								<br>
								<strong><u>For Login Page:</u></strong>
								<br>
								[sld_login]
								<br>
								Login From will appear when you add this shortcode on a page.
								<br>
								<br>
								<strong><u>For Registration Page:</u></strong>
								<br>
								[sld_registration]
								<br>
								Registration From will appear when you add this shortcode on a page.
								<br>
								<br>
								<strong><u>For Dashboard:</u></strong>
								<br>
								[sld_dashboard]
								<br>
								Dashboard (where people can manage there list items) will appear when you add this shortcode.
								<br>
								<br>
								<strong><u>For Restore SLD User Password:</u></strong>
								<br>
								[sld_restore]
								<br>
								User will get password reset option when you add this shortcode on a page. 
								<br>
							</p>
						</div>

						<div class="qcld-sld-section-block">
							<h3 class="shortcode-section-title">Shortcode for Multipage</h3>
							<p>Please make sure that you have installed and activated sld plugin before adding these shortcodes.<br><br>
								<strong><u>For Multipage:</u></strong>
								<br>
								[qcopd-directory-multipage]
								<br>
								<strong style="margin-top: 10px;display: block;"><u>Available Parameters:</u></strong>
								<strong>actual_pagination</strong><br>
								Available values: <strong>true</strong>, <strong>false</strong><br>
								<strong>per_page</strong><br>
								Available values: <strong>5,10,15 etc. in integer number</strong>
							</p>
							<p>
								<strong><u>For Multipage Category:</u></strong>
								<br>
								[sld-multipage-category]
								<br>
								<strong style="margin-top: 10px;display: block;"><u>Available Parameters:</u></strong>
								<strong>exclude</strong><br>
								[sld-multipage-category exclude="131,132,135"]  <br><strong> You can use comma separated category IDs to exclude from multi page listing. </strong> <br>
							</p>
							
							<h3 class="shortcode-section-title">Shortcode for Widgets</h3>
							<p>
								<strong><u>For Random Links:</u></strong>
								<br>
								[qcopd-directory-random]
								<br>
							</p>
							
							<p><strong>Available Parameters</strong></p>
							<p>
								<strong>1. limit_item</strong>
								<br>
								Example: limit_item="5"
							</p>
							<p>
								<strong>2. subtitle</strong>
								<br>
								Example: subtitle="show"
							</p>
							<p>
								<strong>3. category</strong>
								<br>
								Example: category="50,51,52"
								<br>
								You can show specific category using this parameter.
								<br>
								You can add multiple Category ID as coma(,) seperated value.
							</p>
							<p>
								<strong><u>For Latest Links:</u></strong>
								<br>
								[qcopd-directory-latest]
								<br>

							</p>
							
							<p><strong>Available Parameters</strong></p>
							<p>
								<strong>1. limit_item</strong>
								<br>
								Example: limit_item="5"
							</p>
							<p>
								<strong>2. subtitle</strong>
								<br>
								Example: subtitle="show"
							</p>
							<p>
								<strong>3. category</strong>
								<br>
								Example: category="50,51,52"
								<br>
								You can show specific category using this parameter.
								<br>
								You can add multiple Category ID as coma(,) seperated value.
							</p>
							<p>
								<strong><u>For Popular Links:</u></strong>
								<br>
								[qcopd-directory-popular]
								<br>
							</p>
							
							<p><strong>Available Parameters</strong></p>
							<p>
								<strong>1. limit_item</strong>
								<br>
								Example: limit_item="5"
							</p>
							<p>
								<strong>2. subtitle</strong>
								<br>
								Example: subtitle="show"
							</p>
							<p>
								<strong>3. category</strong>
								<br>
								Example: category="50,51,52"
								<br>
								You can show specific category using this parameter.
								<br>
								You can add multiple Category ID as coma(,) seperated value.
							</p>
							
							<p>
								<strong><u>For Popular/Latest/Random tab style:</u></strong>
								<br>
								[qcopd-directory-widget-tab-style]
								<br>
							</p>
							
							<p><strong>Available Parameters</strong></p>
							<p>
								<strong>1. orderby</strong>
								<br>
								Compatible order by values: "ID", "author", "title", "name", "type", "date", "modified", "rand" and "menu_order".
							</p>
							
							<p>
								<strong>2. order</strong>
								<br>
								Value for this option can be set as "ASC" for Ascending or "DESC" for Descending order.
								<br>
								
								<strong>For List Ordering to work, either specify orderby="menu_order" order="ASC in the short code or leave these empty.</strong>
								
							</p>
							<p>
								<strong>3. item_orderby</strong>
								<br>
								Values: "upvotes", "title", "random". You can order/sort list items by upvote counts or by their titles.
								<br>
								Example: item_orderby="upvotes"
							</p>
						</div>
						
						<div class="qcld-sld-section-block">
							<h3 class="shortcode-section-title">Shortcode Example</h3>
							<p>
							<strong><u>Full Shortcode:</u></strong>
								<br>
							[qcopd-directory mode="all" style="simple" column="1" upvote="on" search="true" item_count="on" item_details_page="off" orderby="date" filterorderby="date" order="ASC" filterorder="ASC" paginate_items="false" favorite="disable" enable_left_filter="false" main_click="" enable_tag_filter="false" tooltip="false" list_title_font_size="" item_orderby="" list_title_line_height="" title_font_size="" subtitle_font_size="" title_line_height="" subtitle_line_height="" filter_area="normal" topspacing=""]
							</p>
							<p>
								<strong><u>For all the lists:</u></strong>
								<br>
								[qcopd-directory mode="all" style="simple" column="2" search="true" category="" upvote="on" item_count="on" orderby="date" order="DESC" item_orderby="title"]
								<br>
								<br>
								<strong><u>For only a single list:</u></strong>
								<br>
								[qcopd-directory mode="one" list_id="75"]
								<br>
								<br>
								<strong><u>For Category Tab</u></strong>
								<br>
								[sld-tab mode="categorytab" style="simple" column="2" search="true" category="" upvote="on" item_count="on" orderby="date" order="DESC" item_orderby="title"]
								<br>
								<br>
								<strong><u>Available Parameters:</u></strong>
								<br>
								
							</p>
							
							<p>
								<strong>1. mode</strong>
								<br>
								Value for this option can be set as "one" or "all" or "favorite".
								<br>
								<p>
								If you set mode="one", then filter option will not appear.
								</p>
								<p>
								If you set mode="favorite", show only Favorite List.
								</p>
							</p>
							<p>
								<strong>2. column</strong>
								<br>
								Avaialble values: "1", "2", "3" or "4".
							</p>
							<p>
								<strong>3. style</strong>
								<br>
								Avaialble values: "simple", "style-1", "style-2", "style-3", "style-4", "style-5", "style-6", "style-7", "style-8", "style-9", "style-10", "style-11", "style-12", "style-13", "style-14".
								<br>
								<br>
								To get details idea about how different style templates will look, please see the [Demo Images] tab from the left side.
							</p>
							<p>
								<strong>4. orderby</strong>
								<br>
								Compatible order by values: "ID", "author", "title", "name", "type", "date", "modified", "rand" and "menu_order".
							</p>
							<p>
								<strong>5. order</strong>
								<br>
								Value for this option can be set as "ASC" for Ascending or "DESC" for Descending order.
								<br>
								<br>
								<strong>For List Ordering to work, either specify orderby="menu_order" order="ASC in the short code or leave these empty.</strong>
				
							</p>
							<p>
								<strong>6. list_id</strong>
								<br>
								Only applicable if you want to display a single list [not all]. You can provide specific list id here as a value. You can also get ready shortcode for a single list under "Manage List Items" menu.
							</p>
							<p>
								<strong>7. category</strong>
								<br>
								Supply the category slug of your specific directory category.
								<br>
								Example: category="designs"
								<br>
								For multiple categories add slug with coma(,) seperated without having any space.
								Example: category="designs,planning"
							</p>
							<p>
								<strong>8. search</strong>
								<br>
								Values: true or false. If you want to display on-page search for items, then you can set this parameter to - true.
								<br>
								Example: search="true"
							</p>
							<p>
								<strong>9. upvote</strong>
								<br>
								Values: on or off. This options allows upvoting of your list items.
								<br>
								Example: upvote="on"
							</p>
							<p>
								<strong>10. item_count</strong>
								<br>
								Values: on or off. This options allows to display list items count just beside your list heading.
								<br>
								Example: item_count="on"
							</p>
							<p>
								<strong>11. top_area</strong>
								<br>
								Values: on or off. You can hide top area (search and link submit) from any individual templates if you require. This option is handy if you want to display multiple template in the same page.
								<br>
								Example: top_area="off"
							</p>
							<p>
								<strong>12. item_orderby</strong>
								<br>
								Values: "upvotes", "title". You can order/sort list items by upvote counts or by their titles.
								<br>
								Example: item_orderby="upvotes"
							</p>
							<p>
								<strong>13. mask_url</strong>
								<br>
								Values: "on", "off". This option will allow you to hide promotional/affliate links from the visitors. Visitors will not be able to see these type of links when they mouseover on the links, but upon clicking on these links - they will be redirected to the original/set affliate links.
								<br>
								Example: mask_url="on"
								<br>
								<strong><i>Please note that URL masking may hurt your SEO.</i></strong>
							</p>
							<p>
								<strong>14. paginate_items</strong>
								<br>
								Values: "true", "false". This option will allow you to paginate list items. It will break the list page wise.
								<br>
								Example: paginate_items="true"
								<br>
								[Only applicable for certain templates.]
							</p>
							<p>
								<strong>15. per_page</strong>
								<br>
								This option indicates the number of items per page. Default is "5". paginate_items="true" is required to find this parameter in action.
								<br>
								Example: per_page="5"
								<br>
								[Only applicable for certain templates.]
							</p>
							<p>
								<strong>16. tooltip</strong>
								<br>
								You can enable or disable tooltip by using this parameter. Accepted values are "true" and "false".
								<br>
								Example: tooltip="true"
								<br>
								[Only applicable for certain templates.]
							</p>
							<p>
								<strong>17. Filter Area</strong>
								<br>
								You can set the filter area fixed position using this below parameter.
								<br>
								Example: filter_area="fixed"
								<br>
								Available values: fixed, normal.
							</p>
							<p>
								<strong>18. Filter Area Top spacing</strong>
								<br>
								You can set Top Spacing for filter area using this below parameter.
								<br>
								Example: topspacing="50"
								<br>
								Available values: It could be any integer.
							</p>
							<p>
								<strong>19. Remove specific category from Category Tab</strong>
								<br>
								You can remove specific category from Category Tab using this below parameter.
								<br>
								Example: category_remove="50,51,52"
								<br>
								You can add multiple Category ID as coma(,) seperated value.
								
							</p>
							<p>
								<strong>20. Exclude specific list item from list</strong>
								<br>
								You can exclude specific list item by item id from list using this below parameter.
								<br>
								Example: exclude="1622631788,1622631756"
								<br>
								You can add multiple Item ID as coma(,) seperated value.
								
							</p>
							<p>
								<strong>21. Favorite Section from list</strong>
								<br>
									If you set favorite_hide="hide", then Favorite Section will not appear.
								<br>
								Example: favorite_hide="hide"
								<br>
								
							</p>
							<p>
								<strong>22. style-16 image show</strong>
								<br>
									Add the shortcode parameter enable_image="true" to show images with style-16.
								<br>
								Example: enable_image="true"
								<br>
								
							</p>
						</div>

						<div class="qcld-sld-section-block">
							<h3 class="shortcode-section-title">List Item Image Auto Generation</h3>
							<p><b>Here are all the different ways you can use Images with your List items</b></p>
							<ol>
								<li>You can upload an image for your list item clicking the List Image option</li>
								<li>You can select Generate Image from Website Link checkbox. You need to add Page speed API key from SLD settings to get it to work</li>
								<li>You can select a Font Awesome icon</li>
								<li>You can choose to use Favicon / External Image / YouTube Thumb / Direct Image Link. If you enter a website link it will automatically generate the Favicon URL. If you enter a youtube video link (Ex. https://www.youtube.com/watch?v=Td6Yhh6GFas) it will automatically generate the Youtube Thumb URL. Select the option Pick Image from the Direct Link if you want to use this easy and quick methood</li>
							</ol>

							
							
							
							
							


						</div>

						<div class="qcld-sld-section-block">
							<h3 class="shortcode-section-title">Shortcode for Global Search</h3>
							<p>You can add a search bar to any place of your website. Use the following shortcode to add the search bar.</p>
							<strong>[sld-searchbar post_id="your post id" placeholder="Search"]</strong>
							<p>Or add <strong>do_shortcode('[sld-searchbar post_id="your post id" placeholder="Search"]')</strong> inside your theme file where you want to display the search bar.</p>
							<p>You will also need to create a page and place a regular SLD single page shortcode inside it and add the page's or post's ID in the shortcode to display the results.</p>
						</div>

						<div class="qcld-sld-section-block">
							<h3 class="shortcode-section-title">Shortcode for Random List Item</h3>
							<p>You can add Random List Item to any place of your website. Use the following shortcode to add the Random List Item.</p>
							<strong>[qcopd-directory-random-list-item background="#5C5050" color="#fff" limit="1"]</strong>
							<p>Or add <strong>do_shortcode('[qcopd-directory-random-list-item background="#5C5050" color="#fff" limit="1"]')</strong> inside your theme file where you want to display the Random List Item.</p>

							<p>You can show specific category using this parameter. Example: category="50,51,52"</p>
							<p>You can add multiple Category ID as coma(,) seperated value.</p>
							
						</div>

						<div class="qcld-sld-section-block">
							<h3 class="shortcode-section-title">Template Overriding</h3>
							<p>You can change SLD Templates from your theme. Follow the following steps to override SLD templates from your theme</p>
							<p>
								<strong>1.</strong> Unzip the plugin package in your computer and copy the <strong>/qc-simple-link-directory/templates</strong> folder.
							</p>
							<p>
								<strong>2.</strong> Create a folder named <strong>"sld"</strong> inside your ACTIVE theme folder and paste the <strong>"templates"</strong> folder inside it. Now you can modify the template files inside as required.
							</p>
							<p style="color: red;">We strongly recommend you use a Child theme, so updating the main theme does not delete your SLD customization folders.</p>
						</div>
					</div>
				</div>
			</div>
<?php
		}
	}

	new QC_SLD_Help_License_Sub_Menu();
}

