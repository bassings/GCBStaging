<?php
/* Custom Colors: Goodz Magazine */

//Background
add_color_rule( 'bg', '#ffffff', array(
	array( '#infinite-footer .container, #infinite-footer, body, .main-navigation ul ul li, .main-navigation ul ul, .featured-slider .slick-arrow, .shrink, button:hover, input[type="button"]:hover, input[type="reset"]:hover, input[type="submit"]:hover, .grid-wrapper #infinite-handle span button:hover', 'background-color' ),
) );

add_color_rule( 'txt', '#000000', array(

	//Contrast with background bg
	array( '.main-navigation a, .site-title a, .site-description, #big-search-trigger', 'color', 'bg' ),

	array( '.featured-slider .slick-arrow:before', 'color', 'bg' ),
	array( 'label.checkbox:before, input[type="checkbox"] + label:before, label.radio:before, input[type="radio"] + label:before', 'border-color', 'bg' ),
	array( 'select:hover, .widget .search-form:hover input[type="search"], .widget .search-form input[type="search"]:focus, .widget .search-form:hover input[type="submit"]', 'border-color', 'bg' ),
	array( '#infinite-footer .blog-info a, #infinite-footer .blog-credits a', 'color', 'bg' ),
	array( '.grid-wrapper .format-quote .highlighted, .grid-wrapper .format-link .highlighted', 'border-color', 'bg' ),
	array( 'input[type="text"]:hover, input[type="email"]:hover, input[type="url"]:hover, input[type="password"]:hover, input[type="search"]:hover, textarea:hover, input[type="text"]:focus, input[type="email"]:focus, input[type="url"]:focus, input[type="password"]:focus, input[type="search"]:focus, textarea:focus', 'border-color', 'bg' ),
	array( 'button:hover, input[type="button"]:hover, input[type="reset"]:hover, input[type="submit"]:hover, .grid-wrapper #infinite-handle span button:hover', 'color', 'bg' ),
	array( 'button:hover, input[type="button"]:hover, input[type="reset"]:hover, input[type="submit"]:hover, .grid-wrapper #infinite-handle span button:hover', 'border-color', 'bg' ),

),
__( 'Header' ) );

add_color_rule( 'link', '#000000', array(
	array( 'a', 'color', 'bg' ),
	array( 'a:hover, .comment-metadata a:hover', 'color', 'bg', 3 ),
	array( '.featured-image:hover a:before', 'border-color', 'bg' ),

	//No contrast
	array( '.featured-slider .slick-arrow:focus, .featured-slider .slick-arrow:hover', 'background-color' ),
	array( 'button, input[type="button"], input[type="reset"], input[type="submit"], .grid-wrapper #infinite-handle span button', 'background-color' ),
	array( 'button, input[type="button"], input[type="reset"], input[type="submit"], .grid-wrapper #infinite-handle span button', 'border-color' ),

),
__( 'Links' ) );

add_color_rule( 'fg1', '#000000', array(
	array( '.entry-title a, .widget-title a, h1, h2, h3, h4, h5, h6, .widget-title', 'color', 'bg' ),
	array( '.post .entry-content h1, .page .entry-content h1, .post .entry-content h2, .page .entry-content h2, .post .entry-content h3, .page .entry-content h3, .post .entry-content h4, .page .entry-content h4, .post .entry-content h5, .page .entry-content h5, .post .entry-content h6, .page .entry-content h6, .archive .page-title, .search .page-title', 'color', 'bg' ),
),
__( 'Headings' ) );

add_color_rule( 'fg2', '#000000', array(
	array( '.archive .page-title span, .search .page-title span', 'color', 'bg' ),
	array( '.wp-caption-text', 'color', 'bg' ),
	array( '.widget td', 'color', 'bg' ),
	array( 'body, .single-post .entry-content', 'color', 'bg' ),
	array( '.widget_calendar #today', 'color', 'bg' ),
	array( '.widget_calendar #today', 'border-color', 'bg' ),
	array( '.post .entry-content, .grid-wrapper .page .entry-content, .author-info p', 'color', 'bg' ),
),
__( 'Body Text' ) );


//Extra rules
add_color_rule( 'extra', '#000000', array(
	array( '.single-post .entry-meta a:hover, .comment-metadata a:hover, .single-post .tags-links a:hover', 'color', 'bg' ),
) );

add_color_rule( 'extra', '#a2a2a2', array(
	array( '.comment-metadata a, .single-post .entry-meta, .single-post .entry-meta a', 'color', 'bg' ),
) );

add_color_rule( 'extra', '#aeaeae', array(
	array( 'label, body #jp-relatedposts .jp-relatedposts-items .jp-relatedposts-post .jp-relatedposts-post-context, body #jp-relatedposts .jp-relatedposts-items .jp-relatedposts-post .jp-relatedposts-post-date', 'color', 'bg' ),
) );

add_color_rule( 'extra', '#ffffff', array(
	array( '.featured-slider .slick-arrow:focus:before, .featured-slider .slick-arrow:hover:before', 'color', 'txt' ),
	array( 'button, input[type="button"], input[type="reset"], input[type="submit"], .grid-wrapper #infinite-handle span button', 'color', 'txt' ),
) );

add_color_rule( 'extra', '#ebebeb', array(
	array( 'select, input[type="text"], input[type="email"], input[type="url"], input[type="password"], input[type="search"], textarea', 'border-color', 0.3 ),
) );

add_color_rule( 'extra', '#1a1616', array(
	array( 'select', 'color', 'bg' ),
) );

add_color_rule( 'extra', '#9a9a9a', array(
	array( '.widget_recent_entries .post-date, .widget_recent_comments, .widget_categories, .widget_rss, .tagcloud a', 'color', 'bg' ),
) );

add_color_rule( 'extra', '#949494', array(
	array( '.single-post .tags-links a', 'color', 'bg' ),
) );

add_color_rule( 'extra', '#a7a7a7', array(
	array( '.grid-wrapper .format-link .entry-title:after, .grid-wrapper .format-quote blockquote:after', 'color', 'bg' ),
) );

add_color_rule( 'extra', '#c0c0c0', array(

) );

//Additional palettes

add_color_palette( array(
    '#ffe2dc',
    '#181486',
    '#181486',
    '#181486',
    '#181486',
), 'Blue' );

add_color_palette( array(
    '#e1ded9',
    '#413e4a',
    '#5689a3',
    '#413e4a',
    '#413e4a',
), 'Vintage' );

add_color_palette( array(
    '#413e4a',
    '#d3ce3d',
    '#d3ce3d',
    '#f1efa5',
    '#cacac2',
), 'Dark' );

add_color_palette( array(
    '#e5ddcb',
    '#524656',
    '#cf4647',
    '#524656',
    '#524656',
), 'Palette Name 4' );

add_color_palette( array(
    '#e5e5e5',
    '#686868',
    '#686868',
    '#686868',
    '#686868',
), 'Gray' );
