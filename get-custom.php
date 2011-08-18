<?php
/**
 * @package Get_Custom_Field_Values
 * @author Scott Reilly
 * @version 3.3.1
 */
/*
Plugin Name: Get Custom Field Values
Version: 3.3.1
Plugin URI: http://coffee2code.com/wp-plugins/get-custom-field-values/
Author: Scott Reilly
Author URI: http://coffee2code.com
Description: Use widgets, shortcodes, and/or template tags to easily retrieve and display custom field values for posts or pages.

Compatible with WordPress 2.8+, 2.9+, 3.0+, 3.1+, 3.2+.

=>> Read the accompanying readme.txt file for instructions and documentation.
=>> Also, visit the plugin's homepage for additional information and updates.
=>> Or visit: http://wordpress.org/extend/plugins/get-custom-field-values/

TODO:
	* Create hooks to allow disabling shortcode, shortcode builder, and widget support
	* When shortcode builder is used and user specifies limit of 1, omit the between attribute. (maybe)
	* Use WP_Query when possible
	* Facilitate conditional output, maybe via c2c_get_custom_if() where text is only output if post
	  has the custom field AND it equals a specified value (or one of an array of possible values)
	  echo c2c_get_custom_if( 'size', array( 'XL', 'XXL' ), 'Sorry, this size is out of stock.' );
	* Introduce a 'format' shortcode attribute and template tag argument.  Defines the output format for each
	  matching custom field, i.e. c2c_get_custom(..., $format = 'Size %key% has %value%' in stock.')
	* Support specifying $field as array or comma-separated list of custom fields.
	* Create args array alternative template tag: c2c_custom_field( $field, $args = array() ) so features
	  can be added and multiple arguments don't have to be explicitly provided.  Perhaps transition c2c_get_custom()
	  in plugin v4.0 and detect args.
	  function c2c_get_custom( $field, $args = array() ) {
	    if ( ! empty( $args ) && ! is_array( $args ) ) // Old style usage
	      return c2c_old_get_custom( $field, ... ); // Or: $args = c2c_get_custom_args_into_array( ... );
	    // Do new handling here.
	  }
	* Support retrieving custom fields for one or more specific post_types
	  c2c_get_custom( 'colors', array( 'post_type' => array( 'pants', 'shorts' ) ) )
	* Support name filters to run against found custom fields
	  c2c_get_custom( 'colors', array( 'filters' => array( 'strtoupper', 'make_clickable' ) ) )
	* Since it's shifting to args array, might as well support 'echo'

*/

/*
Copyright (c) 2004-2011 by Scott Reilly (aka coffee2code)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy,
modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR
IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

include( dirname( __FILE__ ) . '/get-custom.widget.php' );
include( dirname( __FILE__ ) . '/get-custom.shortcode.php' );

if ( ! function_exists( 'c2c_get_custom' ) ) :
/**
 * Template tag for use inside "the loop" to display custom field value(s) for the current post
 *
 * @param string $field The name/key of the custom field
 * @param string $before (optional) The text to display before all the custom field value(s), if any are present (defaults to '')
 * @param string $after (optional) The text to display after all the custom field value(s), if any are present (defaults to '')
 * @param string $none (optional) The text to display in place of the field value should no field values exist; if defined as '' and no field value exists, then nothing (including no `$before` and `$after`) gets displayed.
 * @param string $between (optional) The text to display between multiple occurrences of the custom field; if defined as '', then only the first instance will be used.
 * @param string $before_last (optional) The text to display between the next-to-last and last items listed when multiple occurrences of the custom field; `$between` MUST be set to something other than '' for this to take effect.
 * @return string The formatted string
 */
function c2c_get_custom( $field, $before='', $after='', $none='', $between='', $before_last='' ) {
	return c2c__format_custom( $field, (array) get_post_custom_values( $field ), $before, $after, $none, $between, $before_last );
}
endif;


if ( ! function_exists( 'c2c_get_current_custom' ) ) :
/**
 * Template tag for use on permalink (aka single) page templates for posts and pages
 *
 * @param string $field The name/key of the custom field
 * @param string $before (optional) The text to display before all the custom field value(s), if any are present (defaults to '')
 * @param string $after (optional) The text to display after all the custom field value(s), if any are present (defaults to '')
 * @param string $none (optional) The text to display in place of the field value should no field values exist; if defined as '' and no field value exists, then nothing (including no `$before` and `$after`) gets displayed.
 * @param string $between (optional) The text to display between multiple occurrences of the custom field; if defined as '', then only the first instance will be used.
 * @param string $before_last (optional) The text to display between the next-to-last and last items listed when multiple occurrences of the custom field; `$between` MUST be set to something other than '' for this to take effect.
 * @return string The formatted string
 */
function c2c_get_current_custom( $field, $before='', $after='', $none='', $between='', $before_last='' ) {
	if ( ! ( is_single() || is_page() ) )
		return;

	global $wp_query;
	$post_obj = $wp_query->get_queried_object();
	$post_id = $post_obj->ID;
	return c2c__format_custom( $field, (array) get_post_custom_values( $field, $post_id ), $before, $after, $none, $between, $before_last );
}
endif;


if ( ! function_exists( 'c2c_get_post_custom' ) ) :
/**
 * Template tag for use when you know the ID of the post you're interested in
 *
 * @param int $post_id Post ID
 * @param string $field The name/key of the custom field
 * @param string $before (optional) The text to display before all the custom field value(s), if any are present (defaults to '')
 * @param string $after (optional) The text to display after all the custom field value(s), if any are present (defaults to '')
 * @param string $none (optional) The text to display in place of the field value should no field values exist; if defined as '' and no field value exists, then nothing (including no `$before` and `$after`) gets displayed.
 * @param string $between (optional) The text to display between multiple occurrences of the custom field; if defined as '', then only the first instance will be used.
 * @param string $before_last (optional) The text to display between the next-to-last and last items listed when multiple occurrences of the custom field; `$between` MUST be set to something other than '' for this to take effect.
 * @return string The formatted string
 */
function c2c_get_post_custom( $post_id, $field, $before='', $after='', $none='', $between='', $before_last='' ) {
	return c2c__format_custom( $field, (array) get_post_custom_values( $field, $post_id ), $before, $after, $none, $between, $before_last );
}
endif;


if ( ! function_exists( 'c2c_get_random_custom' ) ) :
/**
 * Template tag for use to retrieve a random custom field value
 *
 * @param string $field The name/key of the custom field
 * @param string $before (optional) The text to display before all the custom field value(s), if any are present (defaults to '')
 * @param string $after (optional) The text to display after all the custom field value(s), if any are present (defaults to '')
 * @param string $none (optional) The text to display in place of the field value should no field values exist; if defined as '' and no field value exists, then nothing (including no `$before` and `$after`) gets displayed.
 * @param int $limit (optional) The limit to the number of custom fields to retrieve. Use 0 to indicate no limit.
 * @param string $between (optional) The text to display between multiple occurrences of the custom field; if defined as '', then only the first instance will be used.
 * @param string $before_last (optional) The text to display between the next-to-last and last items listed when multiple occurrences of the custom field; `$between` MUST be set to something other than '' for this to take effect.
 * @return string The formatted string
 */
function c2c_get_random_custom( $field, $before='', $after='', $none='', $limit=1, $between=', ', $before_last='' ) {
	global $wpdb;
	$search_passworded_posts = false;  // Change this if you want
	
	$sql = "SELECT postmeta.meta_value FROM $wpdb->postmeta as postmeta 
				LEFT JOIN $wpdb->posts AS posts ON (posts.ID = postmeta.post_id)
				WHERE postmeta.meta_key = %s AND postmeta.meta_value != ''
				AND posts.post_status = 'publish' ";
	if ( $search_passworded_posts )
		$sql .= "AND posts.post_password = '' ";
	$sql .= 'ORDER BY rand() LIMIT %d';

	if ( $limit > 1 )
		$value = $wpdb->get_col( $wpdb->prepare( $sql, $field, $limit ) );
	else
		$value = (array) $wpdb->get_var( $wpdb->prepare( $sql, $field, $limit ) );

	return c2c__format_custom( $field, $value, $before, $after, $none, $between, $before_last );
}
endif;


if ( ! function_exists( 'c2c_get_random_post_custom' ) ) :
/**
 * Template tag for use to retrieve random custom field value(s) from a post when you know the ID of the post you're interested in
 *
 * @param int $post_id Post ID
 * @param string $field The name/key of the custom field
 * @param int $limit (optional) The limit to the number of custom fields to retrieve. Use 0 to indicate no limit.
 * @param string $before (optional) The text to display before all the custom field value(s), if any are present (defaults to '')
 * @param string $after (optional) The text to display after all the custom field value(s), if any are present (defaults to '')
 * @param string $none (optional) The text to display in place of the field value should no field values exist; if defined as '' and no field value exists, then nothing (including no `$before` and `$after`) gets displayed.
 * @param string $between (optional) The text to display between multiple occurrences of the custom field; if defined as '', then only the first instance will be used.
 * @param string $before_last (optional) The text to display between the next-to-last and last items listed when multiple occurrences of the custom field; `$between` MUST be set to something other than '' for this to take effect.
 * @return string The formatted string
 */
function c2c_get_random_post_custom( $post_id, $field, $limit=1, $before='', $after='', $none='', $between='', $before_last='' ) {
	$cfields = (array) get_post_custom_values( $field, $post_id );
	shuffle( $cfields );
	$limit = intval( $limit );
	if ( $limit != 0 && count( $cfields ) > $limit )
		$cfields = array_slice( $cfields, 0, $limit );
	return c2c__format_custom( $field, $cfields, $before, $after, $none, $between, $before_last );
}
endif;


if ( ! function_exists( 'c2c_get_recent_custom' ) ) :
/**
 * Template tag for use outside "the loop" and applies for custom fields regardless of post
 *
 * @param string $field The name/key of the custom field
 * @param string $before (optional) The text to display before all the custom field value(s), if any are present (defaults to '')
 * @param string $after (optional) The text to display after all the custom field value(s), if any are present (defaults to '')
 * @param string $none (optional) The text to display in place of the field value should no field values exist; if defined as '' and no field value exists, then nothing (including no `$before` and `$after`) gets displayed.
 * @param string $between (optional) The text to display between multiple occurrences of the custom field; if defined as '', then only the first instance will be used.
 * @param string $before_last (optional) The text to display between the next-to-last and last items listed when multiple occurrences of the custom field; `$between` MUST be set to something other than '' for this to take effect.
 * @param int $limit (optional) The limit to the number of custom fields to retrieve. Use 0 to indicate no limit.
 * @param bool $unique (optional) Boolean ('true' or 'false') to indicate if each custom field value in the results should be unique (defaults to "false")
 * @param string $order (optional) Indicates if the results should be sorted in chronological order ('ASC') (the earliest custom field value listed first), or reverse chronological order ('DESC') (the most recent custom field value listed first).
 * @param bool $include_pages (optional) Boolean ('true' or 'false') to indicate if pages should be included when retrieving recent custom values; default is 'true'
 * @param bool $show_pass_post (optional) Boolean ('true' or 'false') to indicate if password protected posts should be included when retrieving recent custom values; default is 'false'
 * @return string The formatted string
 */
function c2c_get_recent_custom( $field, $before='', $after='', $none='', $between=', ', $before_last='', $limit=1, $unique=false, $order='DESC', $include_pages=true, $show_pass_post=false ) {
	global $wpdb;
	$limit = intval( $limit );
	if ( empty( $between ) )
		$limit = 1;
	if ( $order != 'ASC' )
		$order = 'DESC';

	$sql = "SELECT ";
	if ( $unique )
		$sql .= "DISTINCT ";
	$sql .= "meta_value FROM $wpdb->posts AS posts, $wpdb->postmeta AS postmeta ";
	$sql .= "WHERE posts.ID = postmeta.post_id AND postmeta.meta_key = %s ";
	$sql .= "AND posts.post_status = 'publish' AND ( posts.post_type = 'post' ";
	if ( $include_pages )
		$sql .= "OR posts.post_type = 'page' ";
	$sql .= ') ';
	if ( !$show_pass_post )
		$sql .= "AND posts.post_password = '' ";
	$sql .= "AND postmeta.meta_value != '' ";
	$sql .= "ORDER BY posts.post_date $order";
//die("((".$limit."))");
	if ( $limit > 0 )
		$sql .= ' LIMIT %d';
	$results = array(); $values = array();
	$results = $wpdb->get_results( $wpdb->prepare( $sql, $field, $limit ) );
	if ( !empty( $results ) )
		foreach ( $results as $result ) { $values[] = $result->meta_value; };
	return c2c__format_custom( $field, $values, $before, $after, $none, $between, $before_last );
}
endif;


if ( ! function_exists( 'c2c__format_custom' ) ) :
/**
 * Helper function
 *
 * @param string $field The name/key of the custom field
 * @param array $meta_values Array of custom field values
 * @param string $before (optional) The text to display before all the custom field value(s), if any are present (defaults to '')
 * @param string $after (optional) The text to display after all the custom field value(s), if any are present (defaults to '')
 * @param string $none (optional) The text to display in place of the field value should no field values exist; if defined as '' and no field value exists, then nothing (including no `$before` and `$after`) gets displayed.
 * @param string $between (optional) The text to display between multiple occurrences of the custom field; if defined as '', then only the first instance will be used.
 * @param string $before_last (optional) The text to display between the next-to-last and last items listed when multiple occurrences of the custom field; `$between` MUST be set to something other than '' for this to take effect.
 * @return string The formatted string
 */
function c2c__format_custom( $field, $meta_values, $before='', $after='', $none='', $between='', $before_last='' ) {
	$values = array();
	if ( empty( $between ) )
		$meta_values = array_slice( $meta_values, 0, 1 );
	if ( ! empty( $meta_values ) )
		foreach ( $meta_values as $meta ) {
			$sanitized_field = preg_replace( '/[^a-z0-9_]/i', '', $field );
			$meta = apply_filters( "the_meta_$sanitized_field", $meta );
			$values[] = apply_filters( 'the_meta', $meta );
		}

	if ( empty( $values ) )
		$value = '';
	else {
		$values = array_map( 'trim', $values );
		if ( empty( $before_last ) )
			$value = implode( $values, $between );
		else {
			switch ( $size = sizeof( $values ) ) {
				case 1:
					$value = $values[0];
					break;
				case 2:
					$value = $values[0] . $before_last . $values[1];
					break;
				default:
					$value = implode( array_slice( $values, 0, $size-1 ), $between ) . $before_last . $values[$size-1];
			}
		}
	}
	if ( empty( $value ) ) {
		if ( empty( $none ) )
			return;
		$value = $none;
	}
	return $before . $value . $after;
}
endif;

add_filter( 'the_meta', 'do_shortcode' );

// Some filters you may wish to perform: (these are filters typically done to 'the_content' (post content))
//add_filter('the_meta', 'convert_chars');
//add_filter('the_meta', 'wptexturize');

// Other optional filters (you would need to obtain and activate these plugins before trying to use these)
//add_filter('the_meta', 'c2c_hyperlink_urls', 9);
//add_filter('the_meta', 'text_replace', 2);
//add_filter('the_meta', 'textile', 6);

?>