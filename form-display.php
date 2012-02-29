<?php

// Actions
add_action( 'init',                              'iwacontact_noajax_handler' );
add_action( 'wp_ajax_iwajax_submit',             'iwacontact_ajax_handler' );
add_action( 'wp_ajax_nopriv_iwajax_submit',      'iwacontact_ajax_handler' );

/**
 * Gets the contact form for the requested post ID
 *
 * @param integer $post_id The post ID
 * @return string The form html
 * @since 1.0.0
 **/
function iwacontact_get_contact_form( $post_id ) {
	
	global $ajaxcontact;
	$post = get_post( $post_id );
	$fields = get_post_custom( $post_id );
	$error = false;
	
	if ( key_exists( 'iwacontact_data', $fields ) ) {
		
		$form = '<form action="' . $_SERVER['REQUEST_URI'] . '" class="iwacontact" method="post"><ol class="iwacontactform">';
		
		$iwacontact_data = $fields['iwacontact_data'][0];
		
		$form_fields = preg_split( '/\;\;/', $iwacontact_data );
		
		$ordered_fields = array();
		
		foreach ( $form_fields as $field ) {
			$field_attrs = preg_split( '/\:\:/', $field );
			array_push( $ordered_fields, $field_attrs );
		}
		
		usort( $ordered_fields, "iwacontact_sort_fields" );
		
		$has_error = false;
		
		foreach ( $ordered_fields as $field ) {
			
			$field_vars = array(
				'element_id' => $field[0],
				'displayorder' => $field[1],
				'fieldtype' => $field[2],
				'fieldname' => $field[3],
				'fieldoptions' => iwacontact_parse_special( $field[4] ),
				'fieldrequired' => $field[5],
				'fieldvalidation' => $field[6]
			);
			
			$show_label = true;
			if ( key_exists( 7, $field ) ) {
				$field_vars = array_merge( $field_vars, array( 'fieldlabel' => $field[7] ) );
				$show_label = ( $field_vars['fieldlabel'] != '1' ) ? false : true;
			}
			
			$submitted_value = null;
			$field_error = null;
			
			if ( key_exists( $field_vars['element_id'], $_POST ) && key_exists( 'iwac_form_id', $_POST ) && $_POST['iwac_form_id'] == $post_id ) {
				
				$submitted_value = ( is_array( $_POST[$field_vars['element_id']] ) ) ? join( ',', $_POST[$field_vars['element_id']] ) : $_POST[$field_vars['element_id']];
				
				if ( $field_vars['fieldrequired'] == '1' && empty( $submitted_value ) ) {
					$field_error = '<span class="ajax-feedback error" style="display: inline;">' . __( "This field is required", 'iwacontact' ) . '</span>';
					$error = true;
				}
				elseif ( $field_vars['fieldvalidation'] == 'email' && !preg_match( '/^[A-Z0-9._%-]+@[A-Z0-9._%-]+.[A-Z]{2,4}$/i', $submitted_value ) ) {
					$field_error = '<span class="ajax-feedback error" style="display: inline;">' . __( "Please enter a valid email address", 'iwacontact' ) . '</span>';
					$error = true;
				}
				
			}
			
			$multiselect = '';
			$input_type = 'text';
			$field_id = $field_vars['element_id'];
			
			switch ( $field_vars['fieldtype'] ) {
				
				case 'password' :
					
					$input_type = 'password';
					//break;
				
				case 'input' :
					
					$class = 'input ';
					$class .= ( $field_vars['fieldrequired'] == '1' ) ? 'required-field ' : '';
					$class .= ( $field_vars['fieldvalidation'] != 'none' ) ? 'validate-' . $field_vars['fieldvalidation'] : '';
					$class = rtrim( $class );
					$default_value = ( $submitted_value != null ) ? $submitted_value : $field_vars['fieldoptions'];
					
					$form .= '<li>'
						. ( ( $show_label ) ? '<label for="' . $field_id . '">' . $field_vars['fieldname'] . '</label>' : '' )
						. '<input type="' . $input_type . '" name="' . $field_id . '" id="' . $field_id . '" value="' . $default_value . '" class="' . $class . '" />'
						. ( ( $field_error != null ) ? $field_error : '' )
						. '</li>';
					break;
				
				case 'textarea' :
					
					$class = '';
					$class .= ( $field_vars['fieldrequired'] == '1' ) ? 'required-field ' : '';
					$class .= ( $field_vars['fieldvalidation'] != '' ) ? 'validate-' . $field_vars['fieldvalidation'] : '';
					$class = rtrim( $class );
					$default_value = ( $submitted_value != null ) ? $submitted_value : $field_vars['fieldoptions'];
					
					$form .= '<li>'
						. ( ( $show_label ) ? '<label for="' . $field_id . '">' . $field_vars['fieldname'] . '</label>' : '' )
						. '<textarea name="' . $field_id . '" id="' . $field_id . '" rows="10" cols="50" class="' . $class . '">' . $default_value . '</textarea>'
						. ( ( $field_error != null ) ? $field_error : '' )
						. '</li>';
					break;
				
				case 'checkbox' :
					
					$class = '';
					$class .= ( $field_vars['fieldrequired'] == '1' ) ? 'required-field ' : '';
					$class = rtrim( $class );
					$checked = ( $field_vars['fieldoptions'] == 'true' || $submitted_value == 'true' ) ? 'checked' : '';
					
					$form .= '<li class="inline">'
						. '<input type="checkbox" name="' . $field_id . '" id="' . $field_id . '" value="true" class="' . $class . '" ' . $checked . ' />'
						. ( ( $show_label ) ? '<label for="' . $field_id . '" class="checkbox">&nbsp;' . $field_vars['fieldname'] . '</label>' : '' )
						. ( ( $field_error != null ) ? $field_error : '' )
						. '</li>';
					break;
				
				case 'sendcopy' :
					
					$checked = ( $field_vars['fieldoptions'] == 'true' || $submitted_value == 'true' ) ? 'checked' : '';
					
					$form .= '<li class="inline">'
						. '<input type="checkbox" name="send_copy" id="send_copy" value="true" ' . $checked . ' />'
						. ( ( $show_label ) ? '<label for="send_copy" class="checkbox">&nbsp;' . $field_vars['fieldname'] . '</label>' : '' )
						. '</li>';
					break;
				
				case 'multiselect' :
					$multiselect = ' multiple="multiple" size="4" ';
					$field_id = $field_id . '[]';
					$is_multiselect = 1;
					//break;
				
				case 'selectbox' :
					
					$class = '';
					$class .= ( $field_vars['fieldrequired'] == '1' ) ? 'required-field ' : '';
					$class = rtrim( $class );
					
					$form .= '<li class="inline">'
						. ( ( $show_label ) ? '<label for="' . $field_id . '">' . $field_vars['fieldname'] . '</label>' : '' )
						. '<select name="' . $field_id . '" id="' . $field_id . '" class="' . $class . '" ' . $multiselect . '>';
					$submitted_value = ( $is_multiselect ) ? preg_split( '/\,/', $submitted_value ) : $submitted_value;
					$options = preg_split( '/\r\n|\r|\n/', $field_vars['fieldoptions'] );
					foreach ( $options as $option ) {
						$selected = ( trim( $option ) == trim( $submitted_value ) || ( $is_multiselect && in_array( trim( $option ), $submitted_value ) ) ) ? 'selected="selected"' : '';
						$form .= "<option $selected>$option</option>";
					}
					$form .= '</select>'
						. ( ( $field_error != null ) ? $field_error : '' )
						. '</li>';
					break;
				
				case 'radio' :
					
					$class = '';
					$class = rtrim( $class );
					
					$form .= '<li class="inline">'
						. ( ( $show_label ) ? '<label for="' . $field_id . '">' . $field_vars['fieldname'] . '</label>' : '' );
					$default_value = ( $submitted_value != null ) ? $submitted_value : '';
					$options = preg_split( '/\n/', $field_vars['fieldoptions'] );
					foreach ( $options as $option ) {
						$selected = ( trim( $option ) == trim( $submitted_value ) ) ? 'checked="checked"' : '';
						$form .= "<input type='radio' name='$field_id' id='$field_id' value='$option' $selected> $option";
					}
					$form .= ( ( $field_error != null ) ? $field_error : '' )
						. '</li>';
					break;
				
				case 'hidden' :
					
					$default_value = ( $submitted_value != null ) ? $submitted_value : $field_vars['fieldoptions'];
					
					$form .= '<input type="hidden" name="' . $field_id . '" id="' . $field_id . '" value="' . $default_value . '" />';
					break;
				
				case 'readonly' :
					
					$class = 'input read-only';
					$default_value = ( $submitted_value != null ) ? $submitted_value : $field_vars['fieldoptions'];
					
					$form .= '<li>'
						. ( ( $show_label ) ? '<label for="' . $field_id . '">' . $field_vars['fieldname'] . '</label>' : '' )
						. '<input type="text" name="' . $field_id . '" id="' . $field_id . '" value="' . $default_value . '" class="' . $class . '" readonly="readonly" />'
						. ( ( $field_error != null ) ? $field_error : '' )
						. '</li>';
					break;
				
				case 'h1' :
					
					$class = '';
					$class = rtrim( $class );
					
					$form .= '<li class="inline">'
						. '<h1 class="' . $class . '">' . $field_vars['fieldname'] . '</h1>'
						. '</li>';
					break;
				
				case 'h2' :
					
					$class = '';
					$class = rtrim( $class );
					
					$form .= '<li class="inline">'
						. '<h2 class="' . $class . '">' . $field_vars['fieldname'] . '</h2>'
						. '</li>';
					break;
				
				case 'h3' :
					
					$class = '';
					$class = rtrim( $class );
					
					$form .= '<li class="inline">'
						. '<h3 class="' . $class . '">' . $field_vars['fieldname'] . '</h3>'
						. '</li>';
					break;
				
				case 'h4' :
					
					$class = '';
					$class = rtrim( $class );
					
					$form .= '<li class="inline">'
						. '<h4 class="' . $class . '">' . $field_vars['fieldname'] . '</h4>'
						. '</li>';
					break;
			}
		}
		
		$ajax_result = '';
		$ajax_display = '';
		$submit_disabled = '';
		
		if ( key_exists( 'iwac_form_id', $_POST ) && $_POST['iwac_form_id'] == $post_id && !$error ) {
			$ajax_result = __( "Your message has been sent successfully!", 'iwacontact' );
			$ajax_display = 'style="display: inline;"';
			$submit_disabled = 'disabled="disabled"';
		}
		$redirect = ( key_exists( 'iwacontact_redirect', $fields ) && trim( $fields['iwacontact_redirect'][0] ) != '' ) ? $fields['iwacontact_redirect'][0] : null;

		if ( iwacontact_needs_antibot_validation( 'recaptcha', $fields ) && iwacontact_recaptcha_available() )
			$form .= '<li>' . iwacontact_get_recaptcha_html() . '</li>';

		$form .= '<li class="buttons">'
			. '<input type="hidden" name="iwac_submitted" value="true" />'
			. '<button class="ajax-submit" type="submit" name="iwac_submit" ' . $submit_disabled . '><span>' . $fields['iwacontact_submit_value'][0] . '</span></button>'
			. "<span class='ajax-result' $ajax_display>$ajax_result</span>";

		if ( iwacontact_needs_antibot_validation( 'honeypot', $fields ) )
			$form .= '<input type="text" class="iwac_abval" name="iwac_abval" />'
				. '<input type="text" class="iwac_abval" name="iwac_abval_two" />';

		$form .= '<input type="hidden" name="iwac_adminajax" value="' . site_url( '/wp-admin/admin-ajax.php' ) . '">'
			. "<input type='hidden' name='iwac_form_id' value='$post_id' />"
			. "<input type='hidden' name='iwac_no_js' value='1' />"
			. "<input type='hidden' name='action' value='iwajax_submit' />"
			. '<img class="ajax-loading" src="' . plugin_dir_url( __FILE__ ) . 'images/ajax-loading.gif" alt="Loading ..." height="20" width="20" />'
			. '</li>'
			. '</ol><!-- .form --></form><!-- #contactform -->';
		
		return $form;
	}
}

/**
 * Insert AJAX Contact shortcode
 * 
 * The call back function for our custom
 * short code [insert_ajaxcontact]
 *
 * @param array $atts The attributes
 * @return string The contact form html
 * @since 1.0.0
 **/
function iwacontact_insert_ajaxcontact( $atts ) {
	$the_ID = ( is_array( $atts ) && key_exists( 'id', $atts ) ) ? $atts['id'] : get_the_ID();
	return iwacontact_get_contact_form( $the_ID );
}

add_shortcode( 'insert_ajaxcontact', 'iwacontact_insert_ajaxcontact' );

/**
 * AJAX handler
 * 
 * The AJAX handler function catches AJAX
 * post submissions and processes them.
 * 
 * @since 2.0.0
 **/
function iwacontact_ajax_handler() {
	
	if ( key_exists( 'iwac_submitted', $_POST ) && !key_exists( 'iwac_no_js', $_POST ) ) {

		try {
			$result = iwacontact_submission_handler( true );
			$response = array(
				'status'     => 'success',
				'redirect'   => null
			);
			if ( key_exists( 'redirect', $result ) && !is_null( $result['redirect'] ) )
				$response['redirect'] = $result['redirect'];
		}
		catch ( Exception $e ) {
			$response = array(
				'status'     => 'fail',
				'reason'     => $e->getMessage(),
				'error_code' => $e->getCode()
			);
		}

		print json_encode( $response );
		exit;

	}

}

/**
 * NO AJAX handler
 * 
 * The NO AJAX handler function catches non-AJAX
 * post submissions and processes them.
 * 
 * @since 2.0.0
 **/
function iwacontact_noajax_handler() {
	
	if ( key_exists( 'iwac_submitted', $_POST ) && key_exists( 'iwac_no_js', $_POST ) ) {
		
		try {
			$result = iwacontact_submission_handler();
			if ( key_exists( 'redirect', $result ) && !is_null( $result['redirect'] ) ) {
				wp_redirect( $result['redirect'] );
				exit;
			}
		}
		catch ( Exception $e ) {
			
		}
		
	}

}

/**
 * Get ReCAPTCHA html
 * 
 * @return string ReCAPTCHA HTML
 * @since 2.0.0
 */
function iwacontact_get_recaptcha_html() {
	global $ajaxcontact;
	require_once( 'inc/recaptcha.php' );
	return recaptcha_get_html( $ajaxcontact->get( 'recaptcha_api_pub_key' ) );
}

/**
 * Get ReCAPTCHA html
 * 
 * @param string $challenge ReCAPTCHA Challenge
 * @param string $response ReCAPTCHA Response
 * @throws Exception if captcha is invalid
 * @since 2.0.0
 */
function iwacontact_validate_recaptcha( $challenge, $response ) {
	global $ajaxcontact;
	require_once( 'inc/recaptcha.php' );
	$result = recaptcha_check_answer( $ajaxcontact->get( 'recaptcha_api_priv_key' ), $_SERVER['REMOTE_ADDR'], $challenge, $response );
	if ( !$result->is_valid ) {
		throw new Exception( __( "Invalid CAPTCHA, please try again.", 'iwacontact' ) );
	}
}

/**
 * Contact form submission handler
 * 
 * @param boolean $is_ajax Is this an AJAX submission?
 * @throws Exception when submission is invalid
 * @return array Success information
 * @since 2.0.0
 */
function iwacontact_submission_handler( $is_ajax = false ) {
	
	global $ajaxcontact;

	$fields = get_post_custom( $_POST['iwac_form_id'] );
	
	$use_captcha_default = 'yes' == $ajaxcontact->get( 'use_captcha' ) ? true : false;

	// Anti-bot validation
	if ( iwacontact_needs_antibot_validation( 'honeypot', $fields ) ) {
		if ( ( key_exists( 'iwac_abval', $_POST ) && !empty( $_POST['iwac_abval'] ) ) || ( key_exists( 'iwac_abval_two', $_POST ) && !empty( $_POST['iwac_abval_two'] ) ) ) 
			throw new Exception( 'Anti-bot validation failed' );
	}

	if ( iwacontact_needs_antibot_validation( 'recaptcha', $fields ) && iwacontact_recaptcha_available() ) {
		iwacontact_validate_recaptcha( $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"] );
	}

	$first_line = sprintf( __( 'There has been a new contact form submission at %1$s', 'iwacontact' ), get_bloginfo( 'name' ) );

	$email_body = $first_line . "\n\n";
	
	$redirect = ( key_exists( 'iwacontact_redirect', $fields ) && trim( $fields['iwacontact_redirect'][0] ) != '' && key_exists( 'iwac_no_js', $_POST ) && $_POST['iwac_no_js'] == '1' ) ? $fields['iwacontact_redirect'][0] : null;
	
	$iwacontact_data = $fields['iwacontact_data'][0];
	
	$form_fields = preg_split( '/\;\;/', $iwacontact_data );
	
	$ordered_fields = array();
	$completed_fields = array();
	
	foreach ( $form_fields as $field ) {
		$field_attrs = preg_split( '/\:\:/', $field );
		array_push( $ordered_fields, $field_attrs );
	}
	
	usort( $ordered_fields, "iwacontact_sort_fields" );
	
	foreach ( $ordered_fields as $field ) {
		
		$field_vars = array(
			'element_id' => $field[0],
			'displayorder' => $field[1],
			'fieldtype' => $field[2],
			'fieldname' => $field[3],
			'fieldoptions' => $field[4],
			'fieldrequired' => $field[5],
			'fieldvalidation' => $field[6]
		);

		$field_name = $field_vars['fieldname'];
		$submitted_value = ( is_array( $_POST[$field_vars['element_id']] ) ) ? implode( ', ', $_POST[$field_vars['element_id']] ) : trim( $_POST[$field_vars['element_id']] );
		
		if ( '1' == $field_vars['fieldrequired'] && !is_array( $submitted_value ) && '' == $submitted_value )
			throw new Exception( sprintf( __( 'You left a required field <span>%1$s</strong> empty', 'iwacontact' ), $field_name ), 1 );
		elseif ( 'email' == $field_vars['fieldvalidation'] && !preg_match( '/^[A-Z0-9._%-]+@[A-Z0-9._%-]+.[A-Z]{2,4}$/i', $submitted_value ) ) 
			throw new Exception( sprintf( __( 'Invalid email address provided for <span>%1$s</strong>', 'iwacontact' ), $field_name ), 1 );
		
		$email_body .= "\n\n$field_name:\n" . $submitted_value;
		$field_vars['submittedvalue'] = $submitted_value;
		$completed_fields[] = $field_vars;
		
	}
	
	$from = iwacontact_replace_values( $fields['iwacontact_from'][0], $completed_fields );
	$email_to = $fields['iwacontact_sendto'][0];
	$subject = iwacontact_replace_values( $fields['iwacontact_subject'][0], $completed_fields );
	
	// Specify from and reply-to email addresses
	$headers = 'From: ' . get_bloginfo( 'name' ) . " <$from>" . "\r\n" . 'Reply-To: ' . $from;

	if ( key_exists( '_use_custom_body', $fields ) && '1' == $fields['_use_custom_body'][0] )
		$email_body = iwacontact_replace_values( $fields['_custom_body'][0], $completed_fields );

	// Filter the email subject and body
	$subject = apply_filters( 'iwacontact_email_subject', $subject, $completed_fields );
	$email_body = apply_filters( 'iwacontact_email_body', $email_body, $completed_fields );

	// Insert a submission post for this submission
	$submission_id = wp_insert_post( array(
		'post_author'    => 1,
	    'post_title'     => $subject,
	    'post_status'    => 'publish',
	    'post_type'      => 'iwacontactsubmission'
	) );

	// Set submission details using post meta
	update_post_meta( $submission_id, '_form_id',         $_POST['iwac_form_id'] );
	update_post_meta( $submission_id, '_read_before',     '0' );
	update_post_meta( $submission_id, '_sent',            '0' );
	update_post_meta( $submission_id, '_copy_sent',       '0' );
	update_post_meta( $submission_id, '_mail_to',         $email_to );
	update_post_meta( $submission_id, '_mail_from',       $from );
	update_post_meta( $submission_id, '_mail_subject',    $subject );
	update_post_meta( $submission_id, '_form_data',       serialize( $completed_fields ) );
	update_post_meta( $submission_id, '_email_body',      $email_body );
	
	// Send email using wp_mail()
	if ( wp_mail( $email_to, $subject, $email_body, $headers ) )
		update_post_meta( $submission_id, '_sent', '1' );
	
	// Send copy
	if ( key_exists( 'send_copy', $_POST ) && $_POST['send_copy'] == 'true' ) {
		$subject = 'Your email to ' . get_bloginfo( 'name' );
		$headers = 'From: ' . get_bloginfo( 'name' ) . " <$from>";
		if ( wp_mail( $email, $subject, $email_body, $headers ) )
			update_post_meta( $submission_id, '_copy_sent', '1' );
	}
	
	return array(
		'success' => true,
		'redirect' => $redirect
	);

}
