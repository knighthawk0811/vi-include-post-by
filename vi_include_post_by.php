<?php
/*
Plugin Name: VI: Include Post By
Plugin URI: http://neathawk.com
Description: Ability to include posts inside other posts/pages, etc, with a shortcode.
Version: 0.4.200706
Author: Joseph Neathawk
Author URI: http://Neathawk.com
License: GNU General Public License v2 or later
*/

class vi_include_post_by
{
    /*--------------------------------------------------------------
    >>> TABLE OF CONTENTS:
    ----------------------------------------------------------------
    # Instructions
    # TODO
    # Attributes
    # Constructive Functions
    # Reusable Functions
    # Shortcode Functions (are plugin territory)
    --------------------------------------------------------------*/


    /*--------------------------------------------------------------
    # TODO
    --------------------------------------------------------------*/

    //include_by_cat
    //only do "get_post" once then programatically do the offset and page rather than query the DB again
    //put the desired posts into a new array and delete the old array
    //then you should be able to carry on as usual
    //??? does this help performance considering that many users will not continue on to further pages?

    /*--------------------------------------------------------------
    # Attributes
    --------------------------------------------------------------*/
    private static $error_report = false;

    /*--------------------------------------------------------------
    # Constructive Functions
    --------------------------------------------------------------*/
	/**
	 * ENQUEUE SCRIPTS AND STYLES
	 *
	 * wp_enqueue_style( string $handle, string $src = '', array $deps = array(), string|bool|null $ver = false, string $media = 'all' )
	 * wp_enqueue_script( string $handle, string $src = '', array $deps = array(), string|bool|null $ver = false, bool $in_footer = false )
	 *
	 * @link https://developer.wordpress.org/themes/basics/including-css-javascript/#stylesheets
	 */
	public static function enqueue_scripts() {
	    //style for the plugin
	    wp_enqueue_style( 'vi-ipb-css', plugins_url( '/style.css', __FILE__ ), NULL , NULL , 'all' );

	    wp_enqueue_script( 'vi-ipb-js', plugins_url( '/common.js', __FILE__ ), array('jquery') , NULL , true );
	}


    /*--------------------------------------------------------------
    # Reusable Functions
    --------------------------------------------------------------*/

	/**
	 * return the thumbnail URL as a string
	 *
	 * @version 0.4.200611
	 * @since 0.1.181213
	 * @todo custom ID is not returning the post properly for some reason.
	 *		result is going to the current page/post after returning.
	 *		following procedures then have the wrong post data
	 */
	private static function get_thumbnail_url($input_post = null, $image_size = 'full')
	{
		//return value
		$the_post_thumbnail_url = plugins_url( '/default-image.png', __FILE__ );
		$image_size = sanitize_text_field($image_size);
		$working_post = $input_post;

		if($input_post == null)
		{
			//fail gracefully
		}
		elseif( is_int($input_post) )
		{
			//need to lookup post

			//new loop
			$query2 = new WP_Query( array( 'p' => $input_post ) );
			if ( $query2->have_posts() )
			{
				// The 2nd Loop
				while ( $query2->have_posts() )
				{
					//setup post
					$query2->the_post();

					$working_post = $query2->post;

				}
				// Restore original Post Data
				wp_reset_postdata();
			}
		}

		if( is_object($working_post) )
		{
			//is this a proper post type?
			if( 'post' === get_post_type($input_post) || 'page' === get_post_type($input_post) )
			{
				//already have a thumbnail? use that one

				$the_post_thumbnail_url = get_the_post_thumbnail_url($input_post->ID, $image_size);
				if( $the_post_thumbnail_url == false )
				{
					//no thumbnail set, then grab the first image
					ob_start();
					ob_end_clean();
					$matches = array();
					$output = preg_match_all('/<img.+?src=[\'"]([^\'"]+)[\'"].*?>/i', $input_post->post_content, $matches);
					$the_post_thumbnail_url = $matches[1][0];

					//set a default image inside the theme folder
					if(empty($the_post_thumbnail_url))
					{
						$the_post_thumbnail_url = plugins_url( '/default-image.png', __FILE__ );
					}
				}
			}
		}

		return $the_post_thumbnail_url;
	}


	/**
	 * return the thumbnail URL of all sizes as a string
	 * NOTE: works, but image-set is not supported yet
	 *
	 * @version 0.4.200611
	 * @since 0.4.200611
	 * @todo
	 */
	private static function get_thumbnail_image_set($input_post = null)
	{
		//return value
		$the_post_thumbnail_url = plugins_url( '/default-image.png', __FILE__ );
		$working_post = $input_post;

		if($input_post == null)
		{
			//fail gracefully
		}
		elseif( is_int($input_post) )
		{
			//need to lookup post

			//new loop
			$query2 = new WP_Query( array( 'p' => $input_post ) );
			if ( $query2->have_posts() )
			{
				// The 2nd Loop
				while ( $query2->have_posts() )
				{
					//setup post
					$query2->the_post();

					$working_post = $query2->post;

				}
				// Restore original Post Data
				wp_reset_postdata();
			}
		}

		if( is_object($working_post) )
		{
			//is this a proper post type?
			if( 'post' === get_post_type($input_post) || 'page' === get_post_type($input_post) )
			{
				//already have a thumbnail? use that one
				if( get_the_post_thumbnail_url($input_post->ID) != false )
				{
					$the_post_thumbnail_url = 'url(' . get_the_post_thumbnail_url($input_post->ID, 'thumbnail') . ') ' . intval( get_option( "thumbnail_size_w" ) ) . 'px, ';
					$the_post_thumbnail_url .= 'url(' . get_the_post_thumbnail_url($input_post->ID, 'medium') . ') ' . intval( get_option( "medium_size_w" ) ) . 'px, ';
					$the_post_thumbnail_url .= 'url(' . get_the_post_thumbnail_url($input_post->ID, 'full') . ') ' . intval( get_option( "large_size_w" ) ) . 'px';

				}
				else
				{
					//no thumbnail set, then grab the first image
					ob_start();
					ob_end_clean();
					$matches = array();
					$output = preg_match_all('/<img.+?src=[\'"]([^\'"]+)[\'"].*?>/i', $input_post->post_content, $matches);
					$the_post_thumbnail_url = 'url(' . $matches[1][0] . ') ' . intval( get_option( "medium_size_w" ) ) . 'px, ';

					//set a default image inside the theme folder
					if(empty($the_post_thumbnail_url))
					{
						$the_post_thumbnail_url = 'url(' . plugins_url( '/default-image.png', __FILE__ ) . ') ' . intval( get_option( "medium_size_w" ) ) . 'px, ';
					}
				}
			}
		}

		return $the_post_thumbnail_url;
	}

	/**
	 * return the thumbnail <img> as a string
	 *
	 * @version 0.1.200415
	 * @since 0.1.181213
	 */
	private static function get_thumbnail_tag($post = null, $class = '')
	{
		//return string <img> value
		return '<img src="' . vi_include_post_by::get_thumbnail_url($post) .'" class=" ' . $class . ' attachment-thumbnail size-thumbnail wp-post-image" alt="" />';
	}

	/**
	 * return the full thumbnail content
	 *
	 * @version 0.4.200706
	 * @since 0.3.191007
	 */
	private static function get_thumbnail($post = NULL, $link = true, $class = '', $image_size = 'full')
	{
		if($error_report){echo('<pre>[ipb] thumbnail' . "\n" . var_dump_return( $post ) . '</pre>');}
        echo( '<div class="post-thumbnail aspect-ratio ' . $class . '">' );
        if( $link ){ echo( '<a href="' . esc_url( get_permalink($post) ) . '" >' ); }
        echo( '<img class="element" src="' . vi_include_post_by::get_thumbnail_url($post, $image_size) . '" alt="thumbnail for ' . $post->post_title . '" />' );
        if( $link ){ echo( '</a>' ); }
        echo( '</div>' );
    }

	/**
	 * get_title
	 *
	 * @version 0.3.191007
	 * @since 0.3.191007
	 */
	private static function get_title($post = NULL, $link = true)
	{
		if( $link )
		{
			echo( '<h2 class="entry-title"><a href="' . esc_url( get_permalink($post) ) . '" rel="bookmark">' . get_the_title($post) . '</a></h2>' );
		}
		else
		{
			echo( '<h2 class="entry-title">' . get_the_title($post) . '</h2>' );
		}
	}

	/**
	 * posted_on
	 * copied from twentytwenty
	 *
	 * @version 0.1.181213
	 * @since 0.1.181213
	 */
	private static function posted_on($post = NULL)
	{
		$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
		if ( get_the_time( 'U', $post ) !== get_the_modified_time( 'U', $post ) ) {
			$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
		}
		$date_format = get_option('date_format', 'c');

		$time_string = sprintf( $time_string,
			esc_attr( get_the_date( 'c', $post ) ),
			esc_html( get_the_date( $date_format, $post ) ),
			esc_attr( get_the_modified_date( 'c', $post ) ),
			esc_html( get_the_modified_date( $date_format, $post ) )
		);

		$posted_on = sprintf(
			/* translators: %s: post date. */
			esc_html_x( 'Posted on %s', 'post date', 'vi_include_post_by' ),
			'<a href="' . esc_url( get_permalink($post) ) . '" rel="bookmark">' . $time_string . '</a>'
		);

		echo '<span class="posted-on">' . $posted_on . '</span>'; // WPCS: XSS OK.
	}

	/**
	 * posted_by
	 * copied from twentytwenty
	 *
	 * @version 0.1.181213
	 * @since 0.1.181213
	 */
	private static function posted_by($post = NULL)
	{
		$author_id = $post->post_author;

		$byline = sprintf(
			/* translators: %s: post author. */
			esc_html_x( 'by %s', 'post author', 'vi_include_post_by' ),
			'<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( $author_id ) ) . '">' . esc_html( get_the_author_meta('display_name', $author_id) ) . '</a></span>'
		);

		echo '<span class="byline"> ' . $byline . '</span>'; // WPCS: XSS OK.
	}

	/**
	 * get_meta
	 *
	 * @version 0.3.191007
	 * @since 0.3.191007
	 */
	private static function get_meta($post = NULL)
	{
        echo( '<div class="entry-meta">' );
        vi_include_post_by::posted_on($post);
        vi_include_post_by::posted_by($post);
        echo( '</div>' );
	}

	/**
	 * get_content
	 *
	 * @version 0.3.191007
	 * @since 0.3.191007
	 */
	private static function get_content($post = NULL)
	{
        echo( '<div class="entry-content">' );
        echo( do_shortcode( get_the_content('', false, $post) ) );
        echo( '</div>' );
	}

	/**
	 * get_excerpt
	 *
	 * @version 0.3.191007
	 * @since 0.3.191007
	 */
	private static function get_excerpt($post = NULL)
	{
        echo( '<div class="entry-content">' );
        echo( get_the_excerpt($post) );
        echo( '</div>' );
	}

	/**
	 * get_more
	 *
	 * @version 0.3.191007
	 * @since 0.3.191007
	 */
	private static function get_more($post = NULL, $more_text = "Continue Reading")
	{
        echo( '<a class="read-more" href="' . esc_url( get_permalink($post) ) . '">' . $more_text . '</a>' );
	}

	/**
	 * category_list
	 *
	 * @version 0.1.181213
	 * @since 0.1.181213
	 */
	private static function category_list($post = NULL)
	{
		if ( 'post' === get_post_type($post) ) {
			/* translators: used between list items, there is a space after the comma */
			$categories_list = get_the_category_list( esc_html__( ', ', 'vi_include_post_by' ), '', $post->ID );
			if ( $categories_list ) {
				/* translators: 1: list of categories. */
				printf( '<span class="cat-links">' . esc_html__( 'Posted in %1$s', 'vi_include_post_by' ) . '</span>', $categories_list ); // WPCS: XSS OK.
			}
		}
	}//category_list

	/**
	 * get_footer
	 *
	 * @version 0.3.191007
	 * @since 0.3.191007
	 */
	private static function get_footer($post = NULL)
	{
        echo( '<div class="entry-footer">' );
        vi_include_post_by::category_list($post);
        echo( '</div>' );
	}


	/**
	 * get the paginate page number links
	 *
	 * @version 0.3.191007
	 * @since 0.3.191007
	 */
	private static function get_page_number_link($number, $current)
	{
		$style = 'display:inline-block; margin:3px; min-width:20px; padding:0 3px; background:rgba(0,0,0,0.25);';
        if( $number == $current )
        {
            $style .= ' border:1px solid #000000; ';
        }
        else
        {
            $style .= ' border:1px solid rgba(0,0,0,0); ';
        }
        return '<a style="' . $style . '" href="' . esc_url(get_permalink($post)) . '?pn=' . $number . '" title="Page ' . $number . '">' . $number . '</a>';
	}



    /*--------------------------------------------------------------
    # Shortcode Functions (are plugin territory)
    --------------------------------------------------------------*/

	/**
	 * include post by ID
	 *
	 * @version 0.3.200415
	 * @since 0.1.181219
	 */
	public static function include_post_by_id( $attr )
	{
	    /*
	    ***************************************************************************
	    ***************************************************************************
	    //*/

	    $post_object = null;
	    $output = '';

	    //get input
	    extract( shortcode_atts( array(
	    	'id' => NULL,
	    	'link' => true,
	    	'moretext' => 'Continue Reading',
	    	'display' => 'all',
	    	'display_header' => '',
	    	'display_body' => '',
	    	'display_footer' => '',
	    	'image_size' => 'full',
	    	'card' => false,
	    	'class' => '',
	    	'class_inner' => '',
	    	'class_header' => '',
	    	'class_body' => '',
	    	'class_footer' => '',
	    	'class_thumbnail' => '',
	    	'first_item' => false,
	    	'error_report' => false
	    ), $attr ) );


	    if ( $error_report === 'true' ) $error_report = true;
	    if($error_report){$output .= '*** [ipb-id] error reporting enabled <br>';}

		if($error_report){$output .= '<pre>[ipb-id] ' . "\n" . var_dump_return( $attr ) . '</pre>';}

	    //remove spaces, and build array

	    $display_header = sanitize_text_field( $display_header );
	    $display_header_option_input = explode(',', str_replace(' ', '', $display_header));

	    $display_body = sanitize_text_field( $display_body );
	    if(empty($display_body)) $display_body = sanitize_text_field( $display );
	    $display_body_option_input = explode(',', str_replace(' ', '', $display_body));

	    $display_footer = sanitize_text_field( $display_footer );
	    $display_footer_option_input = explode(',', str_replace(' ', '', $display_footer));

	    $image_size = sanitize_text_field( $image_size );

	    $class = sanitize_text_field( $class );
	    $class_inner = sanitize_text_field( $class_inner );
	    if(empty($class_inner)) $class_inner = $class ;
	    $class_header = sanitize_text_field( $class_header );
	    $class_body = sanitize_text_field( $class_body );
	    $class_footer = sanitize_text_field( $class_footer );
	    $class_thumbnail = sanitize_text_field( $class_thumbnail );

	    $more_text = sanitize_text_field( $moretext );

	    if ( $link === 'false' ) $link = false; // just to be sure...
	    if ( $card === 'false' ) $card = false; // just to be sure...
	    if ( $first_item === 'false' ) $first_item = false; // just to be sure...

	    if($card)
	    {
	    	$class_inner.= ' card';
	    	$class_header .= ' card-header';
	    	$class_body .= ' card-body';
	    	$class_footer .= ' card-footer';
	    }


	    if($error_report){$output .= '*** [ipb-id] sanitization complete <br>';}


	    //get started, query the post, start a new loop
	    if( is_numeric( $id) )
	    {
	        //obstream
	        ob_start();

	        //setup post, loop
	        $args = array( 'p' => $id );
	        $the_posts = new WP_Query($args);
	        //normal output the post stuff
	        if ( $the_posts->have_posts() )
	        {
	            while( $the_posts->have_posts() )
	            {
	                $the_posts->the_post();//setup the current post

	                /***********************
	                begin the output
	                ***********************/

	                //do each display in the order in which it was given by the user
	                //also break apart into -> header, body, footer

        			echo( '<div class="include-post-by inner ' . $class_inner . ($first_item ? ' active' : '') . '">' );

        			//HEADER
        			if(!empty($display_header))
        			{
						if($error_report){$output .= '<pre>[ipb-id] header' . "\n" . var_dump_return( $the_posts->post->ID ) . '</pre>';}
						if($error_report){$output .= '<pre>[ipb-id] header' . "\n" . var_dump_return( $display_header_option_input ) . '</pre>';}
	        			echo( '<div class="header ' . $class_header . '">' );
					    foreach( $display_header_option_input as $key => &$value )
					    {
					        switch( $value )
					        {
					            case 'title':
					                vi_include_post_by::get_title($the_posts->post, $link);
					                break;
					            case 'meta':
					                vi_include_post_by::get_meta($the_posts->post);
					                break;
					            case 'thumbnail':
					                vi_include_post_by::get_thumbnail($the_posts->post, $link, $class_thumbnail, $image_size);
					                break;
					            case 'content':
					                vi_include_post_by::get_content($the_posts->post);
					                break;
					            case 'excerpt':
					                vi_include_post_by::get_excerpt($the_posts->post);
					                break;
					            case 'more':
					                vi_include_post_by::get_more($the_posts->post, $more_text);
					                break;
					            case 'footer':
					                vi_include_post_by::get_footer($the_posts->post);
					                break;
					            case 'all':
					            	//default ordering
					                vi_include_post_by::get_title($the_posts->post, $link);
					                vi_include_post_by::get_meta($the_posts->post);
					                vi_include_post_by::get_thumbnail($the_posts->post, $link, $class_thumbnail, $image_size);
					                vi_include_post_by::get_content($the_posts->post);
					                vi_include_post_by::get_footer($the_posts->post);
					                break;
					            default:
					                //any other values are garbage in
					                $value = null;
					                unset($display_header_option_input[$key]);
					        }
					    }
	        			echo( '</div>' );//HEADER
	        		}

        			//BODY
        			if(!empty($display_body))
        			{
						if($error_report){$output .= '<pre>[ipb-id] body' . "\n" . var_dump_return( $the_posts->post->ID ) . '</pre>';}
						if($error_report){$output .= '<pre>[ipb-id] body' . "\n" . var_dump_return( $display_body_option_input ) . '</pre>';}
	        			echo( '<div class="body ' . $class_body. '">' );
					    foreach( $display_body_option_input as $key => &$value )
					    {
					        switch( $value )
					        {
					            case 'title':
					                vi_include_post_by::get_title($the_posts->post, $link);
					                break;
					            case 'meta':
					                vi_include_post_by::get_meta($the_posts->post);
					                break;
					            case 'thumbnail':
					                vi_include_post_by::get_thumbnail($the_posts->post, $link, $class_thumbnail, $image_size);
					                break;
					            case 'content':
					                vi_include_post_by::get_content($the_posts->post);
					                break;
					            case 'excerpt':
					                vi_include_post_by::get_excerpt($the_posts->post);
					                break;
					            case 'more':
					                vi_include_post_by::get_more($the_posts->post, $more_text);
					                break;
					            case 'footer':
					                vi_include_post_by::get_footer($the_posts->post);
					                break;
					            case 'all':
					            	//default ordering
					                vi_include_post_by::get_title($the_posts->post, $link);
					                vi_include_post_by::get_meta($the_posts->post);
					                vi_include_post_by::get_thumbnail($the_posts->post, $link, $class_thumbnail, $image_size);
					                vi_include_post_by::get_content($the_posts->post);
					                vi_include_post_by::get_footer($the_posts->post);
					                break;
					            default:
					                //any other values are garbage in
					                //$value = null;
					                //unset($display_body_option_input[$key]);
					        }
					    }
	        			echo( '</div>' );//BODY
	        		}

        			//FOOTER
        			if(!empty($display_footer))
        			{
						if($error_report){$output .= '<pre>[ipb-id] footer' . "\n" . var_dump_return( $the_posts->post->ID ) . '</pre>';}
						if($error_report){$output .= '<pre>[ipb-id] footer' . "\n" . var_dump_return( $display_footer_option_input ) . '</pre>';}
	        			echo( '<div class="footer ' . $class_footer . '">' );
					    foreach( $display_footer_option_input as $key => &$value )
					    {
					        switch( $value )
					        {
					            case 'title':
					                vi_include_post_by::get_title($the_posts->post, $link);
					                break;
					            case 'meta':
					                vi_include_post_by::get_meta($the_posts->post);
					                break;
					            case 'thumbnail':
					                vi_include_post_by::get_thumbnail($the_posts->post, $link, $class_thumbnail, $image_size);
					                break;
					            case 'content':
					                vi_include_post_by::get_content($the_posts->post);
					                break;
					            case 'excerpt':
					                vi_include_post_by::get_excerpt($the_posts->post);
					                break;
					            case 'more':
					                vi_include_post_by::get_more($the_posts->post, $more_text);
					                break;
					            case 'footer':
					                vi_include_post_by::get_footer($the_posts->post);
					                break;
					            case 'all':
					            	//default ordering
					                vi_include_post_by::get_title($the_posts->post, $link);
					                vi_include_post_by::get_meta($the_posts->post);
					                vi_include_post_by::get_thumbnail($the_posts->post, $link, $class_thumbnail, $image_size);
					                vi_include_post_by::get_content($the_posts->post);
					                vi_include_post_by::get_footer($the_posts->post);
					                break;
					            default:
					                //any other values are garbage in
					                $value = null;
					                unset($display_footer_option_input[$key]);
					        }
					    }
	        			echo( '</div>' );//FOOTER
	        		}

        			echo( '</div>' );//include-post-by

	                /***********************
	                output build complete
	                ***********************/
	            }
	            wp_reset_postdata();
	        }

	        //obstream to $output
	        $output .= ob_get_contents();
	        ob_end_clean();
	    }

	    return $output;
	}//include_post_by_id


	/**
	 * include post by category
	 * uses include-post-by-id
	 *
	 * @version 0.4.200403
	 * @since 0.1.181212
	 */
	public static function include_post_by_cat( $attr )
	{
	    /*
	    ***************************************************************************
	    ***************************************************************************
	    //*/

	    /*
	    uses/requires:
	    [include-post-by-id]
	    //*/

	    $output = '';
	    $first_item = true;
	    $input_array =  shortcode_atts( array(
	    	'cat' => NULL,
	    	'order' => 'DESC',
	    	'orderby' => 'date',
	    	'pageinate' => true,
	    	'paginate' => true,
	    	'perpage' => -1,
	    	'offset' => 0,
	    	'class_container' => '',
	    	'id_container' => '',
	    	'moretext' => 'Continue Reading',
	    	'error_report' => false
	    ), $attr );
		$intput_string = implode($input_array);
	    extract( $input_array );


	    if ( $error_report === 'true' ) $error_report = true;
	    if($error_report){$output .= '*** [ipb-cat] error reporting enabled <br>';}

		if($error_report){$output .= '<pre>[ipb-cat] ' . "\n"  . var_dump_return( $attr ) . '</pre>';}


	    if ( !is_null( $cat ) && ( is_numeric( $cat ) || preg_match( '/^[0-9,]+$/', $cat ) ) && !is_feed() )
	    {

	        //paginate
	        if ( $paginate === 'false' ) $paginate = false; // just to be sure...
	        if ( $pageinate === 'false' ) $paginate = false; // used to be spelled wrong in an old version
	        $paginate = (bool) $paginate;

	        //perpage
	        if ( !is_null( $perpage ) && is_numeric( $perpage ) )
	        {
	            if( $perpage < 1 )
	            {
	                $perpage = -1;
	            }
	        }
	        else
	        {
	            $perpage = -1;
	        }

	        //order
	        if ( !is_null( $order ) && ( $order != 'DESC' && $order != 'ASC' ) )
	        {
	            $order = 'DESC';
	        }

	        //orderby
	        if ( !is_null( $orderby ) && ( preg_match('/^[a-zA-Z\_ ]+$/', $orderby ) != 1) )
	        {
	            $orderby = 'date';
	        }

	        //offest + offset_by_page
	        $page_current = 1;
	        $offset = intval($offset);
	        $offset_by_page = 0;
	        if( isset( $_GET['pn'] ) && is_numeric( $_GET['pn'] ) )
	        {
	            $page_current = intval( $_GET['pn'] );
	            $offset_by_page = ( $page_current - 1 ) * $perpage;
	            if( $offset_by_page < 0 )
	            {
	                $offset_by_page = 0;
	            }
	        }
	        $offset += $offset_by_page;

	    	$class_container = sanitize_text_field( $class_container );
	    	$id_container = sanitize_text_field( $id_container );


	    	if($error_report){$output .= '*** [ipb-cat] sanitization complete <br>';}

	        //get all posts
	        $post_array = array();
	        $transient_name = 'vi_ipb_' . md5( $intput_string );
	        if( false === ( $post_array = get_transient( $transient_name ) ) )
	        {
	            //create a new transient
	            $args = array(
	                'posts_per_page'   => -1,
	                'offset'           => 0,
	                'category'         => "$cat",
	                'orderby'          => "$orderby",
	                'order'            => "$order",
	                'post_type'        => 'post',
	                'post_status'      => 'publish',
	                );
	            $post_array = get_posts( $args );
	            set_transient( $transient_name, $post_array, 10 * MINUTE_IN_SECONDS );
	        }
	        //array_slice ( array $array , int $offset [, int $length = NULL [, bool $preserve_keys = FALSE ]] )
	        $post_array = array_slice( $post_array, $offset, null, true);


	    	if($error_report){$output .= '*** [ipb-cat] content gathered, transient id = ' . $transient_name . '<br>';}

	        //display content
        	$output .= '<div id="' . $id_container .'" class="include-post-by-container ' . get_category( $cat )->slug . ' ' . $class_container . '">';
	        if(is_array( $post_array ) && count( $post_array ) > 0)
	        {

	        	//only process the amount that will be one a single page
				$i = 0;
	            foreach( $post_array as $item )
	            {
	            	if( $i++ >= $perpage && $perpage > 0 )
	            	{
	            		break;
	            	}

		            $attr['id'] = $item->ID;
		            //used to note the first item is "active" for bootstrap stuff like a carousel
		            $attr['first_item'] = $first_item;
		            $first_item = false;

		            if($error_report){$output .= '<pre>[ipb-cat] ' . "\n"   . var_dump_return( $item ) . '</pre>';}

	                $output .= vi_include_post_by::include_post_by_id( $attr );
	            }


	    		if($error_report){$output .= '*** [ipb-cat] content complete, begin paginate <br>';}

				//pagination
	            if( $paginate )
	            {
                    // actual count of pages = intval( round-up( count("posts-left") / perpage ) ) + current-page# - 1
                    $page_count = intval( ceil( count( $post_array ) / $perpage ) + $page_current - 1 );

	            	$output .= '<div class="paginate-container">';

	                //paginate link to previous/first content
	                if( $page_current > 1 )
	                {
	                    $output .= '<div class="previous" style="clear:left;float:left;">';
	                    $output .= '<a href="' . esc_url( get_permalink() ) . '?pn=' . ( $page_current - 1 ) . '" title="Previous Page">Previous Page</a>';
	                    $output .= '</div>';
	                }

	                //paginate link to next/last content
	                if( $page_current < $page_count )
	                {
	                    $output .= '<div class="next" style="clear:right;float:right;">';
	                    $output .= '<a href="' . esc_url( get_permalink() ) . '?pn=' . ( $page_current + 1 ) . '" title="Next Page">Next Page</a>';
	                    $output .= '</div>';
	                }


	                //paginate page numbers
	                if( $page_count > 2 )
	                {
	                	//paginate page numbers
	                    $output .= '<div class="page-number" style="height:40px; margin:0 auto; position:relative; width:220px; text-align:center;">';

	                    //print page 1
	                    $output .= self::get_page_number_link( 1, $page_current );


	                    //if more than 4 away from first, print ...
	                    if( $page_current > 4 )
	                    {
	                        $output .= '...';
	                    }


	                    //start at current - 2
	                    $i = $page_current - 2;
	                    //must be at least page 2
	                    if( $i < 2 )
                    	{
                    		$i = 2;
                    	}
	                    //print from (current - 2) up to (current + 2)
	                    for( $i ; $i < $page_count ; $i++ )
	                    {
	                    	if( $i > $page_current + 2 ) continue;
	                    	$output .= self::get_page_number_link( $i, $page_current );
	                    }

	                    //if more than 3 away from last print ...
	                    if( $page_current < ( $i - 3 ) )
	                    {
	                        $output .= '...';
	                    }

	                   	//print last page
	                    $output .= self::get_page_number_link( $page_count, $page_current );

	                    $output .= '</div>';//page-number
	                }
	                $output .= '</div>';//paginate-container
	            }
	        }
	        $output .= '</div>';//close the category div tag

	    }
	    else
	    {
	        //do nothing
	    }

	    // a bit redundanta and lengthy
		//if($error_report){$output .= '<pre>' . var_dump_return( $post_array ) . '</pre>';}

	    return $output;
	}
}

add_shortcode( 'include-post-by-id', Array(  'vi_include_post_by', 'include_post_by_id' ) );
add_shortcode( 'include-post-by-cat', Array( 'vi_include_post_by', 'include_post_by_cat' ) );


//enqueue scripts
add_action( 'wp_enqueue_scripts', Array( 'vi_include_post_by', 'enqueue_scripts' ) );
