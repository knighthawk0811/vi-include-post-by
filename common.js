/**
 * common.js
 * loaded in the footer
 *
 * @link
 * @version 0.4.200706
 */
 "use strict";





/**
 * aspect-ratio 2.0
 *
 * @link
 * @version 0.4.200706
 * @since 0.4.200706
 */
jQuery(document).ready(function()
{
	jQuery(".aspect-ratio").css('background-image', function(index){
		jQuery(this).find( 'img' ).css( "opacity", "0" );

		var first_image = jQuery(this).find( 'img' ).first();
		index = jQuery(first_image).attr( "src" );
		return  'url(' + index + ')';
	});
});