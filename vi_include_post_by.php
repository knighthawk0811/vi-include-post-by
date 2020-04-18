<?php
/*
Plugin Name: VI: Include Post By
Plugin URI: http://neathawk.com
Description: Ability to include posts inside other posts/pages, etc, with a shortcode.
Version: 0.4.200411
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
    # Reusable Functions
    # Shortcode Functions (are plugin territory)
    --------------------------------------------------------------*/

    /*--------------------------------------------------------------
    # Instructions
    --------------------------------------------------------------*/
    /*
    [include-post-by-id
	    id="123"
	    display="title,meta,thumbnail,content,excerpt,more,footer,all"
	    class="custom-class-name"
	    link="true"
	    moretext="Continue Reading"
    ]
	    id = post to be shown
	    display = display options CSV, order counts
	    link = whether the title/thubmnail are links to the post
	    moretext = edit the text of the read-more link


    [include-post-by-cat
        cat="123"
        order="DESC"
        orderby="date"
        paginate=true
        perpage="5"
        offset="0"
	    display="title,meta,thumbnail,content,excerpt,more,footer,all"
	    class="custom-class-name"
	    container="custom-class-name"
	    link="true"
        moretext="Continue Reading"
    ]
	    cat = category to be shown
	    order = sort order
	    orderby = what to sort by
	    paginate = true/false
	    perpage = items per page. -1 = all
	    offset = how many posts to skip, useful if you are combining multiple includes
	    display = from include-post-by-id
	    class= custom-class-name used in the internal element
	    container= custom-class-name used in the wrapper element
	    link = from include-post-by-id
	    moretext = from include-post-by-id
	//*/

    /*--------------------------------------------------------------
    # TODO
    --------------------------------------------------------------*/

    //all:  put pageination style into CSS classes

    //include_by_cat
    //only do "get_post" once then programatically do the offset and page rather than query the DB again
    //put the desired posts into a new array and delete the old array
    //then you should be able to carry on as usual
    //??? does this help performance considering that many users will not continue on to further pages?


    /*--------------------------------------------------------------
    # Reusable Functions
    --------------------------------------------------------------*/

	/**
	 * return the thumbnail URL as a string
	 *
	 * @version 0.3.200415
	 * @since 0.1.181213
	 * @todo custom ID is not returning the post properly for some reason.
	 *		result is going to the current page/post after returning.
	 *		following procedures then have the wrong post data
	 */
	private static function get_thumbnail_url($input_post = null)
	{
		//return value
		$the_post_thumbnail_url = '';

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
					//is this a proper post type?
					if( 'post' === get_post_type($query2->post) || 'page' === get_post_type($query2->post) )
					{
						//already have a thumbnail? use that one
						if(has_post_thumbnail($query2->post->ID))
						{
							ob_start();
							the_post_thumbnail_url('full');
							$the_post_thumbnail_url = ob_get_contents();
							ob_end_clean();
						}
						else
						{
							//no thumbnail set, then grab the first image
							ob_start();
							ob_end_clean();
							$matches = array();
							$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $query2->post->post_content, $matches);
							$the_post_thumbnail_url = $matches[1][0];

							//set a default image inside the theme folder
							if(empty($the_post_thumbnail_url))
							{
								$the_post_thumbnail_url = get_stylesheet_directory_uri() ."/image/default_thumbnail.png";
							}
						}
					}
				}
				// Restore original Post Data
				wp_reset_postdata();
			}
		}
		elseif( is_object($input_post) )
		{
			//is this a proper post type?
			if( 'post' === get_post_type($query2->post) || 'page' === get_post_type($query2->post) )
			{
				//already have a thumbnail? use that one
				if(has_post_thumbnail($query2->post->ID))
				{
					ob_start();
					the_post_thumbnail_url('full');
					$the_post_thumbnail_url = ob_get_contents();
					ob_end_clean();
				}
				else
				{
					//no thumbnail set, then grab the first image
					ob_start();
					ob_end_clean();
					$matches = array();
					$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $query2->post->post_content, $matches);
					$the_post_thumbnail_url = $matches[1][0];

					//set a default image inside the theme folder
					if(empty($the_post_thumbnail_url))
					{
						$the_post_thumbnail_url = get_stylesheet_directory_uri() ."/image/default_thumbnail.png";
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
	 * @version 0.3.200415
	 * @since 0.3.191007
	 */
	private static function get_thumbnail($post = NULL, $link = true, $class = '')
	{
        if( $link )
        {
            echo( '<a class="post-thumbnail" href="' . esc_url( get_permalink() ) . '" >' );
            echo( ' ' . vi_include_post_by::get_thumbnail_tag($post, $class) );
            echo( '</a>' );
        }
        else
        {
            echo( '<div class="post-thumbnail">' );
            echo( ' ' . vi_include_post_by::get_thumbnail_tag($post, $class) );
            echo( '</div>' );
        }
        //gotta fix things after getting the thumbnail
    	//$the_posts->reset_postdata();//setup the current post
    }

	/**
	 * get_title
	 *
	 * @version 0.3.191007
	 * @since 0.3.191007
	 */
	private static function get_title($link = true)
	{
		if( $link )
		{
			the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
		}
		else
		{
			the_title( '<h2 class="entry-title">', '</h2>' );
		}
	}

	/**
	 * posted_on
	 * copied from twentytwenty
	 *
	 * @version 0.1.181213
	 * @since 0.1.181213
	 */
	private static function posted_on()
	{
		$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
		if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
			$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
		}

		$time_string = sprintf( $time_string,
			esc_attr( get_the_date( 'c' ) ),
			esc_html( get_the_date() ),
			esc_attr( get_the_modified_date( 'c' ) ),
			esc_html( get_the_modified_date() )
		);

		$posted_on = sprintf(
			/* translators: %s: post date. */
			esc_html_x( 'Posted on %s', 'post date', 'vi_include_post_by' ),
			'<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
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
	private static function posted_by()
	{
		$byline = sprintf(
			/* translators: %s: post author. */
			esc_html_x( 'by %s', 'post author', 'vi_include_post_by' ),
			'<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
		);

		echo '<span class="byline"> ' . $byline . '</span>'; // WPCS: XSS OK.
	}

	/**
	 * get_meta
	 *
	 * @version 0.3.191007
	 * @since 0.3.191007
	 */
	private static function get_meta()
	{
        echo( '<div class="entry-meta">' );
        vi_include_post_by::posted_on();
        vi_include_post_by::posted_by();
        echo( '</div>' );
	}

	/**
	 * get_content
	 *
	 * @version 0.3.191007
	 * @since 0.3.191007
	 */
	private static function get_content()
	{
        echo( '<div class="entry-content">' );
        the_content();
        echo( '</div>' );
	}

	/**
	 * get_excerpt
	 *
	 * @version 0.3.191007
	 * @since 0.3.191007
	 */
	private static function get_excerpt()
	{
        echo( '<div class="entry-content">' );
        the_excerpt();
        echo( '</div>' );
	}

	/**
	 * get_more
	 *
	 * @version 0.3.191007
	 * @since 0.3.191007
	 */
	private static function get_more($more_text = "Continue Reading")
	{
        echo( '<a class="read-more" href="' . esc_url( get_permalink() ) . '">' . $more_text . '</a>' );
	}

	/**
	 * category_list
	 *
	 * @version 0.1.181213
	 * @since 0.1.181213
	 */
	private static function category_list()
	{
		if ( 'post' === get_post_type() ) {
			/* translators: used between list items, there is a space after the comma */
			$categories_list = get_the_category_list( esc_html__( ', ', 'vi_include_post_by' ) );
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
	private static function get_footer()
	{
        echo( '<div class="entry-footer">' );
        vi_include_post_by::category_list();
        echo( '</div>' );
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
	    	'display' => 'all',
	    	'link' => true,
	    	'class' => '',
	    	'moretext' => 'Continue Reading'
	    ), $attr ) );

	    //remove spaces, and build array
	    $display_option_input = explode(',', str_replace(' ', '', $display));

	    $more_text = sanitize_text_field( $moretext );

	    if ( $link === 'false' ) $link = false; // just to be sure...


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

        			echo( '<div class="article include-post-by ' . $class . '">' );

	                //do each display in the order in which it was given by the user
				    foreach( $display_option_input as $key => &$value )
				    {
				        switch( $value )
				        {
				            case 'title':
				                vi_include_post_by::get_title($link);
				                break;
				            case 'meta':
				                vi_include_post_by::get_meta();
				                break;
				            case 'thumbnail':
				                vi_include_post_by::get_thumbnail($the_posts, $link, $class);
				                break;
				            case 'content':
				                vi_include_post_by::get_content();
				                break;
				            case 'excerpt':
				                vi_include_post_by::get_excerpt();
				                break;
				            case 'more':
				                vi_include_post_by::get_more($more_text);
				                break;
				            case 'footer':
				                vi_include_post_by::get_footer();
				                break;
				            case 'all':
				            	//default ordering
				                vi_include_post_by::get_title($link);
				                vi_include_post_by::get_meta();
				                vi_include_post_by::get_thumbnail($the_posts, $link, $class);
				                vi_include_post_by::get_content();
				                vi_include_post_by::get_footer();
				                break;
				            default:
				                //any other values are garbage in
				                $value = null;
				                unset($display_option_input[$key]);
				        }
				    }

        			echo( '</div>' );//article

        			//echo( ' <div style="display:none;">' );
        			//var_dump( $display_option_input );
        			//echo( '</div>');


	                /***********************
	                output build complete
	                ***********************/
	            }
	            wp_reset_postdata();
	        }

	        //obstream to $output
	        $output = ob_get_contents();
	        ob_end_clean();
	    }
	    //return $output
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
	    $input_array =  shortcode_atts( array(
	    	'cat' => NULL,
	    	'order' => 'DESC',
	    	'orderby' => 'date',
	    	'pageinate' => true,
	    	'paginate' => true,
	    	'perpage' => 5,
	    	'offset' => 0,
	    	'display' => 'all',
	    	'class' => '',
	    	'container' => '',
	    	'link' => true,
	    	'moretext' => 'Continue Reading'
	    ), $attr );
		$intput_string = implode($input_array);
	    extract( $input_array );


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
	            $perpage = 5;
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

	    	$class = sanitize_text_field( $class );
	    	$class = get_category( $cat )->slug . ' ' . $class;

	    	$container = sanitize_text_field( $container );
	    	$container = get_category( $cat )->slug . ' ' . $container;

	        //count all posts
	        $post_count = 0;
	        $transient_name = 'vi_' . md5( $intput_string ) . '_c';
	        if( false === ( $post_count = get_transient( $transient_name ) ) )
	        {
	            // It wasn't there, so regenerate the data and save the transient
	            $args = array(
	                'posts_per_page'   => -1,
	                'offset'           => 0,
	                'category'         => "$cat",
	                'orderby'          => "$orderby",
	                'order'            => "$order",
	                'post_type'        => 'post',
	                'post_status'      => 'publish',
	                );
	            $post_count = count( get_posts( $args ) );
	            set_transient( $transient_name, $post_count, 10 * MINUTE_IN_SECONDS );
	        }
	        //get content for just the current page of posts
	        $transient_name = 'vi_' . md5( $intput_string ) . '_' . $page_current;
	        if( false === ( $post_array = get_transient( $transient_name ) ) )
	        {
	            // It wasn't there, so regenerate the data and save the transient
	            $args = array(
	                'posts_per_page'   => $perpage,
	                'offset'           => $offset,
	                'category'         => "$cat",
	                'orderby'          => "$orderby",
	                'order'            => "$order",
	                'post_type'        => 'post',
	                'post_status'      => 'publish',
	                );
	            $post_array = get_posts( $args );
	            set_transient($transient_name, $post_array, 10 * MINUTE_IN_SECONDS );
	        }

	        //display content
        	$output .= '<div class="include-post-by-container ' . $container . '">';
	        if(is_array( $post_array ) && count( $post_array ) > 0)
	        {
	            foreach( $post_array as $item )
	            {
	                //call site_include_post_by_id();
	                $args = array(
	                    'id'       =>"$item->ID",
	                    'display'   =>"$display",
	                    'moretext'   =>"$moretext",
	                    'link'   =>"$link",
	                    'class' => "$class"
	                    );
	                $output .= vi_include_post_by::include_post_by_id( $args );
	            }


				//pagination
	            if( $paginate )
	            {
	            	$output .= '<div class="paginate-container">';

	                //paginate link back to previous/newer content
	                if( $page_current > 1 )
	                {
	                    $page_previous = $page_current - 1;
	                    $url_var = '?pn=';
	                    if( $page_previous <= 1 )
                    	{
                    		$url_var = '';
                    	}
                    	else
                		{
                			$url_var .= $page_previous;
                		}
	                    $output .= '<a class="previous" style="clear:left;float:left;" href="' . esc_url( get_permalink() ) . $url_var . '" title="Previous Page">Previous Page</a>';
	                }
	                //paginate link to next/older content
	                if( count( $post_array ) == $perpage )
	                {
	                    //is a link even needed?
	                    if( false === ( $post_array_next = get_transient( 'cat_page_' . str_ireplace( ',','_',$cat ) . '__' . ( $page_current + 1 ) ) ) )
	                    {
	                        // It wasn't there, so regenerate the data and save the transient
	                        $args = array(
	                            'posts_per_page'   => $perpage,
	                            'offset'           => ($page_current + 1) * $perpage,
	                            'category'         => "$cat",
	                            'orderby'          => "$orderby",
	                            'order'            => "$order",
	                            'post_type'        => 'post',
	                            'post_status'      => 'publish',
	                            );
	                        $post_array_next = get_posts($args);
	                        set_transient( 'cat_page_' . str_ireplace( ',','_',$cat ) . '__' . ( $page_current + 1 ), $post_array_next, 10 * MINUTE_IN_SECONDS );
	                    }
	                    $count = count( $post_array_next );
	                    if( count( $count ) > 0 )
	                    {
	                        $output .= '<a class="next" style="clear:right;float:right;" href="' . esc_url( get_permalink() ) . '?pn=' . ( $page_current + 1 ) . '" title="Next Page">Next Page</a>';
	                    }
	                }
	                //paginate page numbers
	                if( $post_count > $perpage )
	                {
	                    $output .= '<div class="page-number" style="height:40px; margin:0 auto; position:relative; width:220px; text-align:center;">';
	                    $page_count = intval( ceil( $post_count / $perpage ) );
	                    $i = 1;
	                    $step = 0;
	                    if( $page_count > 4 && $page_current > 1 )
	                    {
	                        $i = $page_current - 1;
	                    }
	                    if( $i > 1 )
	                    {
	                        $link_extra_style = ' border:1px solid rgba(0,0,0,0); ';
	                        $output .= '<a style="display:inline-block; margin:3px; min-width:20px; padding:0 3px; background:rgba(0,0,0,0.25); ' . $link_extra_style . '" href="' . esc_url(get_permalink()) . '?pn=1" title="Page 1">1</a>';
	                        if( $i > 2 )
	                        {
	                            $output .= '...';
	                        }
	                    }
	                    for( $i; $i <= $page_count; $i++ )
	                    {
	                        $step++;
	                        if( $step < 4 || $i == $page_count )
	                        {
	                            if( $i == $page_count && $step > 3 )
	                            {
	                                $output .= '...';
	                            }
	                            if( $i == $page_current )
	                            {
	                                $link_extra_style = ' border:1px solid #000000; ';
	                            }
	                            else
	                            {
	                                $link_extra_style = ' border:1px solid rgba(0,0,0,0); ';
	                            }
	                            $output .= '<a style="display:inline-block; margin:3px; min-width:20px; padding:0 3px; background:rgba(0,0,0,0.25); ' . $link_extra_style . '" href="' . esc_url(get_permalink()) . '?pn=' . $i . '" title="Page ' . $i . '">' . $i . '</a>';
	                        }
	                    }
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

	    return $output;
	}
}

add_shortcode( 'include-post-by-id', Array(  'vi_include_post_by', 'include_post_by_id' ) );
add_shortcode( 'include-post-by-cat', Array( 'vi_include_post_by', 'include_post_by_cat' ) );
