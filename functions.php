<?php

// Actions
add_action( 'init',              'iwacontact_init' );
add_action( 'admin_init',        'iwacontact_admin_init' );

/**
 * General Init
 * 
 * @since 1.0.0
 **/
function iwacontact_init() {

	register_post_type( 'iwacontactform',
		array(
			'labels' => array(
				'all_items'           => __( 'All Forms', 'iwacontact' ),
			    'name'                => __( 'Forms', 'iwacontact' ),
			    'singular_name'       => __( 'Form', 'iwacontact' ),
			    'add_new'             => __( 'Add New Form', 'iwacontact' ),
			    'add_new_item'        => __( 'Add New Form', 'iwacontact' ),
				'edit'                => __( 'Edit', 'iwacontact' ),
			    'edit_item'           => __( 'Edit Form', 'iwacontact' ),
			    'new_item'            => __( 'New Form', 'iwacontact' ),
				'view'                => __( 'View', 'iwacontact' ),
			    'view_item'           => __( 'View Form', 'iwacontact' ),
			    'search_items'        => __( 'Search Forms', 'iwacontact' ),
			    'not_found'           => __( 'No forms found', 'iwacontact' ),
			    'not_found_in_trash'  => __( 'No forms found in Trash', 'iwacontact' ),
			    'menu_name'           => __( 'Contact Forms', 'iwacontact' )
			),
			'description' => __( 'AJAX Contact Forms', 'iwacontact' ),
			'show_ui' => true,
			'query_var' => false,
			'show_in_nav_menus' => false,
			'publicly_queryable' => false,
			'exclude_from_search' => true,
			'rewrite' => false,
			'capability_type' => 'post',
			'has_archive' => false,
			'hierarchical' => false,
			'menu_position' => 20,
			'menu_icon' => plugin_dir_url( __FILE__ ) . 'images/post-type-icon.png',
			'supports' => array( 'title' )
		)
	);

	register_post_type( 'iwacontactsubmission',
		array(
			'labels' => array(
				'all_items'           => __( 'All Submissions', 'iwacontact' ),
			    'name'                => __( 'Submissions', 'iwacontact' ),
			    'singular_name'       => __( 'Submission', 'iwacontact' ),
			    'add_new'             => __( 'Add New', 'iwacontact' ),
			    'add_new_item'        => __( 'Add New Submission', 'iwacontact' ),
				'edit'                => __( 'Edit', 'iwacontact' ),
			    'edit_item'           => __( 'Edit Submission', 'iwacontact' ),
			    'new_item'            => __( 'New Submission', 'iwacontact' ),
				'view'                => __( 'View', 'iwacontact' ),
			    'view_item'           => __( 'View Submission', 'iwacontact' ),
			    'search_items'        => __( 'Search Submissions', 'iwacontact' ),
			    'not_found'           => __( 'No submissions found', 'iwacontact' ),
			    'not_found_in_trash'  => __( 'No submissions found in Trash', 'iwacontact' )
			),
			'description' => __( 'AJAX Contact Form Submissions', 'iwacontact' ),
			'show_ui' => true,
			'query_var' => false,
			'show_in_menu' => false,
			'show_in_nav_menus' => false,
			'publicly_queryable' => false,
			'exclude_from_search' => true,
			'rewrite' => false,
			'capability_type' => 'post',
			'has_archive' => false,
			'hierarchical' => false,
			'menu_position' => 20,
			'menu_icon' => plugin_dir_url( __FILE__ ) . 'images/post-type-icon.png',
			'supports' => array( 'title' )
		)
	);

	wp_register_style( 'ajax-contact-css', plugin_dir_url( __FILE__ ) . 'css/ajax-contact.css' );
	wp_register_script( 'ajax-contact', plugin_dir_url( __FILE__ ) . 'js/ajax-contact.js', array( 'jquery' ) );
	
	wp_enqueue_style( 'ajax-contact-css' );
	wp_enqueue_script( 'ajax-contact' );
	wp_localize_script( 'ajax-contact', 'objectL10n', array(
		'validationError' => __( "There was an error processing your request", 'iwacontact' ),
		'requiredField' => __( "This field is required", 'iwacontact' ),
		'enterValidAddress' => __( "Please enter a valid email address", 'iwacontact' ),
		'success' => __( "Your message has been sent successfully!", 'iwacontact' )
	) );
	
}

/**
 * Admin init
 * 
 * @since 1.0.0
 **/
function iwacontact_admin_init() {
	wp_register_style( 'ajax-contact-admin-css', plugin_dir_url( __FILE__ ) . 'css/ajax-contact-admin.css' );
	wp_register_script( 'ajax-contact-admin', plugin_dir_url( __FILE__ ) . 'js/ajax-contact-admin.js', array( 'jquery' ) );
	wp_enqueue_style( 'ajax-contact-admin-css' );
	wp_enqueue_script( 'ajax-contact-admin' );
}

/**
 * Sort form fields
 * 
 * Custom sorting function that works with usort()
 * to sort custom field arrays by a child var's value
 * 
 * @since 1.1.0
 **/
function iwacontact_sort_fields( $a, $b ) {
	if ( $a[1] == $b[1] ) return 0;
	return ( $a[1] < $b[1] ) ? -1 : 1;
}

/**
 * trim() all values in an array
 * 
 * @param string &$value The array node value
 * @since 1.1.0
 **/
function iwacontact_trim_value( &$value ) {
  $value = trim( $value );
}

/**
 * Parses the provided string and replaces any tags
 * wrapped in [[ ]] with relevant post meta
 *
 * @param string $string The string to parse
 * @uses iwacontact_parse_special()
 * @since 1.5.0
 **/
function iwacontact_parse_special( $string ) {
	
	// If this is a multi-line string, split on new lines
	if ( preg_match( '/\r\n|\r|\n/', $string ) && $lines = preg_split( '/\r\n|\r|\n/', $string ) ) {
		
		$result = array();
		
		foreach ( $lines as $line ) {
			
			// Re-call this function to parse each line
			array_push( $result, iwacontact_parse_special( trim( $line ) ) );
			
		}
		
		return join( "\n", $result );
		
	}
	
	// Split this string in to single words
	if ( $words = preg_split( '/\ /', $string ) ) {
		
		$result = array();
		
		foreach ( $words as $word ) {
			
			if ( preg_match( '/^\[\[([a-z0-9-_.]+)\]\]$/i', $word, $matches ) ) {
				
				$requested_resource = $matches[1];
				switch( strtolower( $requested_resource ) ) {
					
					case 'the_id':
						array_push( $result, get_the_ID() );
						break;
					
					case 'the_title':
						array_push( $result, get_the_title() );
						break;
					
				}
				
			}
			else {
				
				array_push( $result, $word );
				
			}
			
		}
		
		return join( ' ', $result );
		
	}
	
	return false;
	
}

/**
 * Get field ID
 * 
 * Parses the provided string field name and turns it in to
 * a string safe to be used in HTML id and name attributes
 *
 * @param string $field_name The field name to generate an ID for
 * @return string HTML id for the element
 * @since 1.6.0
 **/
function iwacontact_get_field_id( $field_name ) {
	$field_id = mb_strtolower( trim( $field_name ) ); // Clean leading/trailing white space and make the string lower case
	$field_id = preg_replace( '/\ /', '_', $field_id ); // Replace any spaces with underscores (_)
	$field_id = preg_replace( "/[^a-zA-Z0-9-_]/", "", $field_id ); // Remove any non alphanumeric characters
	return $field_id;
}

/**
 * Get submission count for form
 * 
 * @param integer $post_id The form (post) ID
 * @return integer The number of submissions
 * @since 2.0.0
 */
function iwacontact_get_submission_count( $post_id ) {
	return count( get_posts( array(
		'post_type'     => 'iwacontactsubmission',
		'post_status'   => 'publish',
		'meta_key'      => '_form_id',
		'meta_value'    => $post_id
	) ) );
}

/**
 * Populate a string with completed field values
 * i.e. Replaces all %%field_id%%'s with the corresponding submitted value
 * 
 * @param string $string The haystack
 * @param array $completed_fields The completed form data
 * @return string The original string with replacements made
 * @since 2.0.0
 */
function iwacontact_replace_values( $string, $completed_fields ) {
	foreach ( $completed_fields as $field ) {
		$string = str_replace( '%%' . $field['element_id'] . '%%', $field['submittedvalue'], $string );
	}
	return $string;
}

/**
 * Check if ReCAPTCHA is available
 * 
 * @return boolean True if ReCAPTCHA pub/priv keys are specified
 * @since 2.0.0
 */
function iwacontact_recaptcha_available() {
	global $ajaxcontact;
	return !in_array( '', array( $ajaxcontact->get( 'recaptcha_api_pub_key' ), $ajaxcontact->get( 'recaptcha_api_priv_key' ) ) );
}

/**
 * Check if this form needs anti-bot validation
 * 
 * @param string $antibot_validation_type The anti-bot validation type to match
 * @param array $form_custom The form custom fields array
 * @return boolean True if form needs validation
 * @since 2.0.0
 */
function iwacontact_needs_antibot_validation( $antibot_validation_type, $form_custom ) {
	global $ajaxcontact;
	$use_captcha_default = 'yes' == $ajaxcontact->get( 'use_captcha' ) ? true : false;
	$is_default = ( 
		!key_exists( '_use_captcha', $form_custom )
		&& $antibot_validation_type == $ajaxcontact->get( 'captcha_type' ) 
		&& 'yes' == $use_captcha_default );
	$is_defined = ( 
		key_exists( '_use_captcha', $form_custom ) 
		&& '1' == $form_custom['_use_captcha'][0] 
		&& key_exists( '_captcha_type', $form_custom ) 
		&& $antibot_validation_type == $form_custom['_captcha_type'][0] );
	return $is_default || $is_defined;
}

/**
 * Gets the number of fields in a contact form
 *
 * @param integer $post_id The post ID
 * @since 2.0.0
 **/
function iwacontact_get_field_count( $post_id ) {
	
	$post = get_post( $post_id );
	$fields = get_post_custom( $post_id );
	$error = false;
	
	if ( key_exists( 'iwacontact_data', $fields ) ) {
		$iwacontact_data = $fields['iwacontact_data'][0];
		$form_fields = preg_split( '/\;\;/', $iwacontact_data );
		return count( $form_fields );
	}
	
	return 0;

}