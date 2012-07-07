<?php

// Actions
add_action( 'add_meta_boxes',                              'iwacontact_add_meta' );
add_action( 'admin_head-post.php',                         'hide_iwacontact_form_publishing_actions' );
add_action( 'admin_head-post-new.php',                     'hide_iwacontact_form_publishing_actions' );
add_action( 'admin_menu',                                  'iwacontact_admin_menu' );
add_action( 'admin_menu',                                  'remove_iwacontact_submission_publish' );
add_action( 'manage_iwacontactform_posts_custom_column',   'iwacontact_custom_columns' );
add_action( 'save_post',                                   'iwacontact_save_meta' );

// Filters
add_filter( 'plugin_action_links',                         'iwacontact_add_settings_link',     10, 2 );
add_filter( 'manage_edit-iwacontactform_columns',          'iwacontact_columns' );
add_filter( 'post_row_actions',                            'iwacontact_row_actions',           10, 1 );
add_filter( 'parse_query',                                 'iwacontact_admin_posts_filter' );
add_filter( 'post_updated_messages',                       'iwacontact_updated_messages' );

/**
 * Add our meta box to the add/edit page screen
 * 
 * @since 1.0.0
 **/
function iwacontact_add_meta() {

	// Edit Contact Form Metaboxes (Normal)
	add_meta_box( 'iwajax_contact', __( 'Form Editor', 'iwacontact' ), 'iwacontact_box', 'iwacontactform', 'normal', 'core' );
	add_meta_box( 'iwajax_contact_email', __( 'Email Editor', 'iwacontact' ), 'iwacontact_email_box', 'iwacontactform', 'normal', 'core' );

	// Edit Contact Form Metaboxes (Side)
	add_meta_box( 'iwajax_contact_configuration', __( 'Form Configuration', 'iwacontact' ), 'iwacontact_config_box', 'iwacontactform', 'side', 'core' );
	add_meta_box( 'iwajax_contact_antispam', __( 'Form Anti-Spam', 'iwacontact' ), 'iwacontact_antispam_box', 'iwacontactform', 'side', 'core' );
	add_meta_box( 'iwajax_contact_shortcode', __( 'Form Shortcode', 'iwacontact' ), 'iwacontact_shortcode_box', 'iwacontactform', 'side', 'default' );
	add_meta_box( 'iwajax_contact_recent_submissions', __( 'Recent Submissions', 'iwacontact' ), 'iwacontact_recent_submissions', 'iwacontactform', 'side', 'low' );
	
	// Form Submission Metaboxes (Normal)
	// -- Submission tools coming in v2.1
	// add_meta_box( 'iwajax_submission_tools', __( 'Submission Tools' , 'iwacontact' ), 'iwacontact_submission_tools', 'iwacontactsubmission', 'normal', 'core' );
	add_meta_box( 'iwajax_submission_details', __( 'Submission Details' , 'iwacontact' ), 'iwacontact_submission_details', 'iwacontactsubmission', 'normal', 'core' );
	add_meta_box( 'iwajax_submission_email_body', __( 'Submission Email Body' , 'iwacontact' ), 'iwacontact_submission_email_body', 'iwacontactsubmission', 'normal', 'default' );

}

/**
 * Change the edit contact form admin messages
 * 
 * @since 2.0.0
 **/
function iwacontact_updated_messages( $messages ) {
	
	global $post, $post_ID;

	$messages['iwacontactform'] = array(
		0   => '',
		1   => __( 'Form saved.', 'iwacontact' ),
		2   => __( 'Custom field saved.', 'iwacontact' ),
		3   => __( 'Custom field deleted.', 'iwacontact' ),
		4   => __( 'Form saved.', 'iwacontact' ),
		5   => false,
		6   => __( 'Form saved.', 'iwacontact' ),
		7   => __( 'Form saved.', 'iwacontact' ),
		8   => __( 'Form saved.', 'iwacontact' ),
		9   => __( 'Form saved.', 'iwacontact' ),
		10  => __( 'Form saved.', 'iwacontact' )
	);

	return $messages;

}

/**
 * Echo the content for the submission email body metabox
 * 
 * @since 2.0.0
 **/
function iwacontact_submission_email_body() {
	
	global $ajaxcontact;

	$fields = get_post_custom();

	echo nl2br( $fields['_email_body'][0] );

}

/**
 * Echo the content for the anti-spam metabox
 * 
 * @since 2.0.0
 **/
function iwacontact_antispam_box() {
	
	global $ajaxcontact;

	$fields = get_post_custom();

	$use_captcha_default = 'yes' == $ajaxcontact->get( 'use_captcha' ) ? true : false;

	$use_captcha = ( key_exists( '_use_captcha', $fields ) ) ? $fields['_use_captcha'][0] == '1' : $use_captcha_default;
	$captcha_type = ( key_exists( '_captcha_type', $fields ) ) ? $fields['_captcha_type'][0] : $ajaxcontact->get( 'captcha_type' );

	$use_captcha_checked = ( $use_captcha ) ? 'checked="checked"' : '';
	$captcha_type_disabled = ( !$use_captcha ) ? 'disabled="disabled"' : '';

	echo '<p>'
		. "<input type='checkbox' id='iwacf_use_captcha' name='iwacf_use_captcha' value='1' $use_captcha_checked /> "
		. '<label for="iwacf_use_captcha"><strong>'
		. __( 'Enable Anti-Bot validation', 'iwacontact' )
		. '</strong></label>'
		. '</p>';

	echo '<p class="captcha-type">'
		. '<label for="iwacf_captcha_type"><strong>'
		. __( 'Anti-Bot validation type', 'iwacontact' )
		. '</strong></label>'
		. "<select id='iwacf_captcha_type' name='iwacf_captcha_type' $captcha_type_disabled />"
		. '<option value="honeypot" ' . ( 'honeypot' == $captcha_type ? 'selected="selected"' : '' ) . '>' . __( 'Honeypot', 'iwacontact' ) . '</option>'
		. '<option value="recaptcha" ' . ( 'recaptcha' == $captcha_type ? 'selected="selected"' : '' ) . '>' . __( 'Google ReCAPTCHA', 'iwacontact' ) . '</option>'
		. '</select>'
		. '</p>';

}

/**
 * Echo the content for the email editor metabox
 * 
 * @since 2.0.0
 **/
function iwacontact_email_box() {
	
	global $ajaxcontact;

	$fields = get_post_custom();

	$custom_email = key_exists( '_use_custom_body', $fields ) && $fields['_use_custom_body'][0] == '1';
	$custom_email_body = ( key_exists( '_custom_body', $fields ) ) ? $fields['_custom_body'][0] : '';

	$checked = ( $custom_email ) ? 'checked="checked"' : '';

	echo '<p>'
		. __( 'You can specify a custom body for email notifications sent using this form.', 'iwacontact' )
		. '</p>';

	echo '<p>'
		. "<input type='checkbox' id='use_custom_body' name='iwacf_use_custom_body' value='1' $checked /> "
		. '<label for="use_custom_body"><strong>'
		. __( 'Use a custom email body', 'iwacontact' )
		. '</strong></label>'
		. '</p>';

	?>
		<div class="custom-body-editor <?php if ( !$custom_email ) echo 'hidden'; ?>">
			<p>
				<textarea name="iwacf_custom_body" rows="10" cols="50" class="large-text code"><?php echo $custom_email_body; ?></textarea>
			</p>
			<p class="howto">
				<?php _e( 'Include your field values by wrapping the <strong>Field ID</strong> in double percentage symbols.  E.g. to include the submitted value for a field with a Field ID of "your_name" you would use %%your_name%%', 'iwacontact' ); ?>
			</p>
		</div>
	<?php

}

/**
 * Echo the content for the form configuration metabox
 * 
 * @since 2.0.0
 **/
function iwacontact_config_box() {

	global $ajaxcontact;
	
	$fields = get_post_custom();

	wp_nonce_field( plugin_basename(__FILE__), 'iwajax_contact' );
	
	$iwacf_sendto = ( key_exists( 'iwacontact_sendto', $fields ) ) ? $fields['iwacontact_sendto'][0] : $ajaxcontact->get( 'default_to' );
	$iwacf_subject = ( key_exists( 'iwacontact_subject', $fields ) ) ? $fields['iwacontact_subject'][0] : $ajaxcontact->get( 'default_subject' );
	$iwacf_from = ( key_exists( 'iwacontact_from', $fields ) ) ? $fields['iwacontact_from'][0] : $ajaxcontact->get( 'default_from' );
	$iwacf_submit_value = ( key_exists( 'iwacontact_submit_value', $fields ) ) ? $fields['iwacontact_submit_value'][0] : $ajaxcontact->get( 'default_submit_value' );
	$iwacf_redirect = ( key_exists( 'iwacontact_redirect', $fields ) ) ? $fields['iwacontact_redirect'][0] : $ajaxcontact->get( 'default_redirect' );

	?>
	<p>
		<label for="iwacf_sendto" style="font-weight: bold;"><?php _e( 'Send Submissions To', 'iwacontact'); ?></label>
		<input type="text" id="iwacf_sendto" name="iwacf_sendto" class="widefat" value="<?php echo $iwacf_sendto; ?>" />
		<!-- <span class="iwacf-help help-sendto"></span> -->
	</p>
	<p>
		<label for="iwacf_from" style="font-weight: bold;"><?php _e( 'From Address', 'iwacontact'); ?></label>
		<input type="text" id="iwacf_from" name="iwacf_from" class="widefat" value="<?php echo $iwacf_from; ?>" />
		<!-- <span class="iwacf-help help-fromaddr"></span> -->
	</p>
	<p>
		<label for="iwacf_subject" style="font-weight: bold;"><?php _e( 'Subject Line', 'iwacontact'); ?></label>
		<input type="text" id="iwacf_subject" name="iwacf_subject" class="widefat" value="<?php echo $iwacf_subject; ?>" />
		<!-- <span class="iwacf-help help-subjectline"></span> -->
	</p>
	<p>
		<label for="iwacf_submit_value" style="font-weight: bold;"><?php _e( 'Submit Button Text', 'iwacontact'); ?></label>
		<input type="text" id="iwacf_submit_value" name="iwacf_submit_value" class="widefat" value="<?php echo $iwacf_submit_value; ?>" />
		<!-- <span class="iwacf-help help-submittext"></span> -->
	</p>
	<p>
		<label for="iwacf_redirect" style="font-weight: bold;"><?php _e( 'URL redirect on submission', 'iwacontact'); ?></label>
		<input type="text" id="iwacf_redirect" name="iwacf_redirect" class="widefat" value="<?php echo $iwacf_redirect; ?>" />
		<!-- <span class="iwacf-help help-urlredirect"></span> -->
	</p>
	<?php
	
}

/**
 * Echo the content for the form shortcode metabox
 * 
 * @since 2.0.0
 **/
function iwacontact_shortcode_box() {

	global $ajaxcontact;
	?>
	<p>
		<input type="text" id="iwacf_shortcode" class="widefat" value="[insert_ajaxcontact id=<?php the_ID(); ?>]" readonly="readonly" /> 
	</p>
	<p>
		<em><?php _e( 'Use the shortcode to add this form to any post or page', 'iwacontact' ); ?></em>
	</p>
	<?php

}

/**
 * Echo the content for the recent submissions metabox
 * 
 * @since 2.0.0
 **/
function iwacontact_recent_submissions() {

	global $ajaxcontact, $post;

	$submissions = get_posts( array(
		'post_type'     => 'iwacontactsubmission',
		'post_status'   => 'publish',
		'meta_key'      => '_form_id',
		'meta_value'    => $post->ID,
		'limit'         => 5
	) );

	if ( count( $submissions ) < 1 ) {
		echo '<p class="no-results">'
			. '<em>' . __( 'No submissions have been made using this form yet.', 'iwacontact' ) . '</em>'
			. '</p>';
		return;
	}

	echo '<ul class="recent-submissions">';

	foreach ( $submissions as $submission ) {
		$ts = strtotime( $submission->post_date );
		$date = sprintf( __( '%1$s at %2$s', 'iwacontact' ), date( get_option( 'date_format' ), $ts ), date( get_option( 'time_format' ), $ts ) );
		echo '<li class="submission">'
			. '<a href="' . get_edit_post_link( $submission->ID ) . '">'
			. get_the_title( $submission->ID )
			. '</a>'
			. '<span class="date">' . $date . '</span>'
			. '</li>';
	}

	echo '</ul>';

}

/**
 * Echo the content for the edit form metabox
 * 
 * @since 1.0.0
 **/
function iwacontact_box() {
	
	global $ajaxcontact;
	
	$fields = get_post_custom();

	if ( key_exists( '_iwacontact_data', $fields ) ) $fields['iwacontact_data'] = $fields['_iwacontact_data'];
	$iwacontact_data = ( key_exists( 'iwacontact_data', $fields ) ) ? $fields['iwacontact_data'][0] : '';
	
	?>
	<input type="hidden" id="iwacontact_data" name="iwacontact_data" value="<?php echo $iwacontact_data; ?>" />
	<div id="formFields">
		<?php
		echo '<div class="fields-listing">'
			. '<h2 class="fields-title">'
			. __( 'Form Fields', 'iwacontact' )
			. '<input type="button" value="' . __( 'Add New Field', 'iwacontact' ) . '" class="addFieldButton rightbutton" />'
			. '</h2>';
		if ( preg_match( '/\;\;/', $iwacontact_data ) ) {
			
			$form_fields = preg_split( '/\;\;/', $iwacontact_data );
			
			$ordered_fields = array();
			
			foreach ( $form_fields as $field ) {
				$field_attrs = preg_split( '/\:\:/', $field );
				array_push( $ordered_fields, $field_attrs );
			}
			
			usort( $ordered_fields, "iwacontact_sort_fields" );
			$count = 0;
			$form_fields_html = '';
			$field_display_html = '';
			
			foreach ( $ordered_fields as $field ) {
				
				$new_field_attrs = array(
					'fieldid' => $field[0],
					'displayorder' => $field[1],
					'fieldtype' => $field[2],
					'fieldname' => $field[3],
					'fieldoptions' => $field[4],
					'fieldrequired' => $field[5],
					'fieldvalidation' => $field[6]
				);
				
				if ( key_exists( 7, $field ) )
					$new_field_attrs = array_merge( $new_field_attrs, array( 'fieldlabel' => $field[7] ) );
				
				$form_fields_html .= iwacontact_get_new_contact_field( $count, $new_field_attrs );
				$field_display_html .= iwacontact_get_new_contact_display( $count, $new_field_attrs );
				$count++;
				
			}
			echo '<ul id="fieldDisplays">' . $field_display_html . '</ul>'
				. '</div><!-- .fields-listing -->'
				. $form_fields_html;
		}
		else {
			echo '<ul id="fieldDisplays">' . iwacontact_get_new_contact_display( '0', array( 'displayorder' => 1 ) ) . '</ul>'
				. '</div><!-- .fields-listing -->'
				. iwacontact_get_new_contact_field( '0', array( 'displayorder' => 1 ) );
		}
		?>
	</div>
	<div id="newFormField" style="display: none;">
		<?php
			echo iwacontact_get_new_contact_display( 'x' );
			echo iwacontact_get_new_contact_field( 'x' );
		?>
	</div>
	<div class="iwacf-tooltip tooltip-default">
		<p><?php _e( 'Enter the default value for this field or leave empty for no default value.', 'iwacontact' ); ?></p>
		<p><?php _e( 'You may also use [[the_title]] or [[the_id]] to include the current post/page title or ID in the default field value.', 'iwacontact' ); ?></p>
	</div>
	<div class="iwacf-tooltip tooltip-options">
		<p><?php _e( 'Enter the options the user will be asked to select from, one option per line.', 'iwacontact' ); ?></p>
		<p><?php _e( 'You may also use [[the_title]] or [[the_id]] to include the current post/page title or ID in an option.', 'iwacontact' ); ?></p>
	</div>
	<div class="iwacf-tooltip tooltip-sendto">
		<p><?php _e( 'The email address to send form submissions to', 'iwacontact' ); ?></p>
	</div>
	<div class="iwacf-tooltip tooltip-fromaddr">
		<p><?php _e( 'The email address submissions will appear to have been sent from', 'iwacontact' ); ?></p>
	</div>
	<div class="iwacf-tooltip tooltip-subjectline">
		<p><?php _e( 'The subject line of form emails sent using this form', 'iwacontact' ); ?></p>
	</div>
	<div class="iwacf-tooltip tooltip-submittext">
		<p><?php _e( 'The submit button text for the form submit button', 'iwacontact' ); ?></p>
	</div>
	<div class="iwacf-tooltip tooltip-urlredirect">
		<p><?php _e( 'If you would like the user to be redirected to another url (e.g. Thank-you page) when they have submitted the form, enter the URL here', 'iwacontact' ); ?></p>
	</div>
	<div class="iwacf-tooltip tooltip-shortcode">
		<p><?php _e( 'Copy and paste this short code in to the body of any page or post to embed this form in that location', 'iwacontact' ); ?></p>
		<p><?php _e( 'Don\'t forget to use http:// if external to this website', 'iwacontact' ); ?></p>
	</div>
	<?php
	
}

/**
 * Echo the content for the submission tools metabox
 * Coming in v2.1
 */
// function iwacontact_submission_tools() {
	
// 	? >
// 		<ul class="iwacontact-submission-buttons">
// 			<li><a href="#" class="button">< ? php _e( 'Mark as unread', 'iwacontact' ); ? ></a></li>
// 			<li><a href="#" class="button">< ? php _e( 'Re-send', 'iwacontact' ); ? ></a></li>
// 			<li><a href="#" class="button">< ? php _e( 'Forward', 'iwacontact' ); ? ></a></li>
// 			<li><a href="#" class="button">< ? php _e( 'Delete', 'iwacontact' ); ? ></a></li>
// 		</ul> 
// 	< ? php

// }

/**
 * Echo the content for the submission details metabox
 * 
 * @since 2.0.0
 */
function iwacontact_submission_details() {
	
	global $ajaxcontact, $post;
	
	$fields = get_post_custom();

	if ( key_exists( 'markas', $_GET ) && 'unread' == $_GET['markas'] ) update_post_meta( $post->ID, '_read_before', '0' );
	else update_post_meta( $post->ID, '_read_before', '1' );

	$form_data = unserialize( unserialize( $fields['_form_data'][0] ) );

	$form = get_post( $fields['_form_id'][0] );
	$form_edit_url = admin_url( 'post.php?post=' . $form->ID . '&action=edit' );
	$form_title = $form->post_title;

	?>
		<table class="form-table headers">
			<tbody>
				<tr valign="top">
					<th scope='row'><strong><?php _e( 'Submitted on', 'iwacontact' ); ?></strong></th>
					<td><?php echo get_the_date( '', $post->ID ); ?> at <?php echo get_the_time( '', $post->ID ); ?></td>
				</tr>
				<tr valign="top">
					<th scope='row'><strong><?php _e( 'Contact Form', 'iwacontact' ); ?></strong></th>
					<td>
						<?php echo $form_title; ?>
						<div class="small">
							( <a href="<?php echo $form_edit_url; ?>"><?php _e( 'Edit Form', 'iwacontact' ); ?></a> | <a href="<?php echo admin_url( 'edit.php?post_type=iwacontactsubmission&form_id=' . $form->ID ); ?>"><?php _e( 'View Submissions', 'iwacontact' ); ?></a> )
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	<?php

	print '<table class="form-table"><tbody>';

	foreach ( $form_data as $field ) {
		$fname = $field['fieldname'];
		$fval = ( $field['fieldtype'] != 'textarea' ) ? $field['submittedvalue'] : nl2br( $field['submittedvalue'] );
		print "<tr valign='top'>"
			. "<th scope='row'><strong>$fname</strong></th>"
			. "<td>$fval</td>"
			. "</tr>";
	}

	print '</tbody></table>';

}

/**
 * Remove contact submission publish metabox
 * 
 * @since 2.0.0
 */
function remove_iwacontact_submission_publish() {
	remove_meta_box( 'submitdiv', 'iwacontactsubmission', 'side' ); 
}

/**
 * Hide contact form publishing actions
 * 
 * @since 2.0.0
 */
function hide_iwacontact_form_publishing_actions() {

    global $post;

    if ( 'iwacontactform' == $post->post_type ) {
        echo '<style type="text/css">'
        	. '#misc-publishing-actions, #minor-publishing-actions{ display: none; }'
        	. '</style> ';
    }

}

/**
 * Save the iwacontact meta box data on save_post action
 *
 * @param integer $post_id The post ID
 * @since 1.0.0
 **/
function iwacontact_save_meta( $post_id ) {
	
	if ( !key_exists( 'iwajax_contact', $_POST ) )
		return $post_id;
	
	if ( !wp_verify_nonce( $_POST['iwajax_contact'], plugin_basename(__FILE__) ) )
		return $post_id;
	
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
		return $post_id;
	
	if ( 'page' == $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_page', $post_id ) )
			return $post_id;
	}
	
	$form_fields = array(); // Form fields associative array
	$field_names = array(); // Field names array
	
	foreach ( $_POST as $key => $value ) {
		
		if ( preg_match( '/^iwacf\_[a-z]+\_[0-9]+$/', $key ) ) {
			
			$attrs = preg_split( '/\_/', $key );
			
			$field_no = $attrs[2];
			
			if ( $attrs[1] == 'fieldname' )
				array_push( $field_names, $value );
			
			$array_item = array( $attrs[1] => $value );
			
			if ( !key_exists( $field_no, $form_fields ) || !is_array( $form_fields[$field_no] ) )
				$form_fields[$field_no] = array();
			
			$form_fields[$field_no] = array_merge( $form_fields[$field_no], $array_item );
			
		}
	}
	
	$iwacontact_data = array();
	$iwacontact_data_str = '';
	$count = 0;
	
	foreach ( $form_fields as $field ) {
		
		$count++;
		
		$safe_field_name = iwacontact_get_field_id( $field['fieldname'] );
		
		$displayorder = ( key_exists( 'displayorder', $field ) ) ? $field['displayorder'] : $count;
		$fieldoptions = ( key_exists( 'fieldoptions', $field ) ) ? $field['fieldoptions'] : '';
		$fieldrequired = ( key_exists( 'fieldrequired', $field ) ) ? $field['fieldrequired'] : '';
		
		$field_data = array(
			$safe_field_name,
			$displayorder,
			$field['fieldtype'],
			$field['fieldname'],
			$fieldoptions,
			$fieldrequired,
			$field['fieldvalidation'],
			$field['fieldlabel']
		);
		
		$field = join( '::', $field_data );
		
		array_push( $iwacontact_data, $field );
		
	}
	
	$iwacontact_data_str = join( ';;', $iwacontact_data );
	
	update_post_meta( $post_id, 'iwacontact_data',          $iwacontact_data_str );
	update_post_meta( $post_id, 'iwacontact_sendto',        trim( $_POST['iwacf_sendto'] ) );
	update_post_meta( $post_id, 'iwacontact_subject',       trim( $_POST['iwacf_subject'] ) );
	update_post_meta( $post_id, 'iwacontact_from',          trim( $_POST['iwacf_from'] ) );
	update_post_meta( $post_id, 'iwacontact_submit_value',  trim( $_POST['iwacf_submit_value'] ) );
	update_post_meta( $post_id, 'iwacontact_redirect',      trim( $_POST['iwacf_redirect'] ) );

	update_post_meta( $post_id, '_use_custom_body',         ( key_exists( 'iwacf_use_custom_body', $_POST ) && '1' == $_POST['iwacf_use_custom_body'] ) ? '1' : '' );
	update_post_meta( $post_id, '_custom_body',             trim( $_POST['iwacf_custom_body'] ) );

	update_post_meta( $post_id, '_use_captcha',             ( key_exists( 'iwacf_use_captcha', $_POST ) && '1' == $_POST['iwacf_use_captcha'] ) ? '1' : '' );
	update_post_meta( $post_id, '_captcha_type',            trim( $_POST['iwacf_captcha_type'] ) );
	
}

/**
 * Return a new field in the admin edit contact form box
 *
 * @param integer $sequence The sequential number for this contact field
 * @param array $data The data to pre-populate this field with (if any)
 * @since 1.0.0
 **/
function iwacontact_get_new_contact_display( $sequence, $data = array() ) {
	$fieldname = ( key_exists( 'fieldname', $data ) ) ? $data['fieldname'] : __( 'New Field', 'iwacontact' );
	$fieldtype = ( key_exists( 'fieldtype', $data ) ) ? $data['fieldtype'] : 'input';
	$required = ( key_exists( 'fieldrequired', $data ) && '1' == $data['fieldrequired'] ) ? 'required' : '';
	return "<li id='fieldDisplay_$sequence' class='field-display icon-$fieldtype $required'>"
		. "<a href='#'>$fieldname</a>"
		. '<span class="required">' . __( 'Required', 'iwacontact' ) . '</span>'
		. '</li>';
}

/**
 * Return a new *hidden* field editor dialog in the admin edit contact form box
 *
 * @param integer $sequence The sequential number for this contact field
 * @param array $data The data to pre-populate this field with (if any)
 * @since 1.0.0
 **/
function iwacontact_get_new_contact_field( $sequence, $data = array() ) {
	
	global $iwacontactv;
	
	$fieldname = ( key_exists( 'fieldname', $data ) ) ? $data['fieldname'] : __( 'New Field', 'iwacontact' );
	$fieldid = ( key_exists( 'fieldid', $data ) ) ? $data['fieldid'] : 'new_field';
	$fieldtype = ( key_exists( 'fieldtype', $data ) ) ? $data['fieldtype'] : 'input';
	$fieldoptions = ( key_exists( 'fieldoptions', $data ) ) ? $data['fieldoptions'] : '';
	$displayorder = ( key_exists( 'displayorder', $data ) ) ? $data['displayorder'] : '';
	$fieldrequired = ( key_exists( 'fieldrequired', $data ) ) ? $data['fieldrequired'] : '';
	$fieldvalidation = ( key_exists( 'fieldvalidation', $data ) ) ? $data['fieldvalidation'] : 'none';
	$fieldlabel = ( key_exists( 'fieldlabel', $data ) ) ? $data['fieldlabel'] : '1';
	$default_label_label = __( 'Default Value <span class="iwacf-help help-default"></span>', 'iwacontact' );
	switch ( $field_type ) {
		case 'input': //break;
		case 'password': //break;
		case 'textarea': //break;
		case 'hidden': //break;
		case 'readonly':
			$default_label_label = __( 'Default Value <span class="iwacf-help help-default"></span>', 'iwacontact' );
			break;
		case 'radio': //break;
		case 'selectbox': //break;
		case 'multiselect':
			$default_label_label = __( 'Options (1 per line) <span class="iwacf-help help-options"></span>', 'iwacontact' );
			break;
		case 'checkbox': //break;
		case 'sendcopy': //break;
		case 'h1': //break;
		case 'h2': //break;
		case 'h3': //break;
		case 'h4':
		default:
			$default_label_label = __( 'Default Value <span class="iwacf-help help-default"></span>', 'iwacontact' );
			break;
	}
	
	return "<div id='formField_$sequence' class='form-field'>"
		. '<h2>' . __( 'Field Editor', 'iwacontact' ) . '</h2>'
		. '<p>'
		. "<label for='iwacf_fieldname_$sequence'>" . __( 'Field Title', 'iwacontact' ) . "</label>"
		. "<input id='iwacf_fieldname_$sequence' name='iwacf_fieldname_$sequence' type='text' value='$fieldname' class='field-title widefat' />"
		. '</p>'
		. '<p>'
		. "<label for='iwacf_fieldid_$sequence'>" . __( 'Field ID', 'iwacontact' ) . "</label>"
		. "<input id='iwacf_fieldid_$sequence' name='iwacf_fieldid_$sequence' type='text' value='$fieldid' class='field-id widefat' readonly='readonly' />"
		. '</p>'
		. '<p>'
		. "<label for='iwacf_fieldtype_$sequence'>" . __( 'Field Type', 'iwacontact' ) . "</label>"
		. "<select id='iwacf_fieldtype_$sequence' name='iwacf_fieldtype_$sequence' class='field-type widefat'>"
		. iwacontact_fieldtype_options( $fieldtype )
		. '</select>'
		. '</p>'
		. '<p class="iwacf_fieldoptions">'
		. "<label for='iwacf_fieldoptions_$sequence'>$default_label_label</label>"
		. "<textarea id='iwacf_fieldoptions_$sequence' name='iwacf_fieldoptions_$sequence' rows='3' class='widefat'>$fieldoptions</textarea>"
		. '</p>'
		. '<p>'
		. "<label for='iwacf_displayorder_$sequence'>" . __( 'Display Order', 'iwacontact' ) . "</label>"
		. "<input id='iwacf_displayorder_$sequence' name='iwacf_displayorder_$sequence' type='text' value='$displayorder' class='widefat' />"
		. '</p>'
		. '<p>'
		. "<label for='iwacf_fieldvalidation_$sequence'>" . __( 'Validator', 'iwacontact' ) . "</label>"
		. "<select id='iwacf_fieldvalidation_$sequence' name='iwacf_fieldvalidation_$sequence' class='field-validation widefat'>"
		. '<option value="none" ' . ( ( $fieldvalidation == 'none' ) ? 'selected="selected"' : '' ) . '>No Validation</option>'
		. '<option value="email" ' . ( ( $fieldvalidation == 'email' ) ? 'selected="selected"' : '' ) . '>Email Address Validation</option>'
		. '</select>'
		. '</p>'
		. '<p class="border-bottom border-top">'
		. "<input type='checkbox' id='iwacf_fieldrequired_$sequence' name='iwacf_fieldrequired_$sequence' value='1' " . ( ( $fieldrequired == '1' ) ? 'checked="checked"' : '' ) . ' class="iwacf-checkbox field-required" />'
		. "<label for='iwacf_fieldrequired_$sequence'>" . __( 'Field required', 'iwacontact' ) . "</label>"
		. '</p>'
		. '<p class="border-bottom">'
		. "<input type='checkbox' id='iwacf_fieldlabel_$sequence' name='iwacf_fieldlabel_$sequence' value='1' " . ( ( $fieldlabel == '1' ) ? 'checked="checked"' : '' ) . ' class="iwacf-checkbox" />'
		. "<label for='iwacf_fieldlabel_$sequence'>" . __( 'Show label', 'iwacontact' ) . "</label>"
		. '</p>'
		. '<p>'
		. "<input type='button' id='removeField_$sequence' class='deleteFieldButton' value='" . __( 'Delete Field', 'iwacontact' ) . "' />"
		// . '<input type="button" class="addFieldButton" value="' . __( 'Add New Field', 'iwacontact' ) . '" />'
		. '</p>'
		. '</div>';
	
}

/**
 * Return field type select options
 * 
 * @param string $selected_option The selected option
 * @return string Select options html
 * @since 2.0.0
 */
function iwacontact_fieldtype_options( $selected_option ) {
	
	global $iwacontactv;
	
	$field_type_groups = array(
		
		__( 'Normal Fields', 'iwacontact' )   => array(
			'input'       => array( __( 'Text Input', 'iwacontact' ),               true ),
			'password'    => array( __( 'Text Input (Password)', 'iwacontact' ),    true ),
			'textarea'    => array( __( 'Text Input (Multi-Line)', 'iwacontact' ),  true ),
			'readonly'    => array( __( 'Text Input (Read-Only)', 'iwacontact' ),   true ),
			'selectbox'   => array( __( 'Select Box', 'iwacontact' ),               true ),
			'multiselect' => array( __( 'Select Box (Multiple)', 'iwacontact' ),    true ),
			'checkbox'    => array( __( 'Check Box', 'iwacontact' ),                true ),
			'radio'       => array( __( 'Radio Buttons', 'iwacontact' ),            true ),
			'hidden'      => array( __( 'Hidden Field', 'iwacontact' ),             true ),
			'file'        => array( __( 'File Upload', 'iwacontact' ),              false )
		),
		
		// 'Special Fields'  => array(
		// 	'firstname'   => array( __( 'First Name', 'iwacontact' ),               false ),
		// 	'lastname'    => array( __( 'Last Name', 'iwacontact' ),                false ),
		// 	'fullname'    => array( __( 'Full Name', 'iwacontact' ),                false ),
		// 	'emailaddr'   => array( __( 'Email Address', 'iwacontact' ),            false ),
		// 	'sendcopy'    => array( __( 'Send Copy Checkbox', 'iwacontact' ),       false )
		// ),
		
		__( 'Headings', 'iwacontact' )        => array(
			'h1'          => array( __( 'Heading 1', 'iwacontact' ),                true ),
			'h2'          => array( __( 'Heading 2', 'iwacontact' ),                true ),
			'h3'          => array( __( 'Heading 3', 'iwacontact' ),                true ),
			'h4'          => array( __( 'Heading 4', 'iwacontact' ),                true )
		)

	);
	
	$result = '';
	
	foreach ( $field_type_groups as $group_name => $fields ) {

		$result .= "<optgroup label='$group_name'>";
		
		foreach ( $fields as $option => $attrs ) {
			$selected = ( $option == $selected_option ) ? 'selected="selected"' : '';
			$disabled = ( !$attrs[1] ) ? 'disabled="disabled"' : '';
			$result .= '<option value="' . $option . '" ' . $selected . ' ' . $disabled . '>' . $attrs[0] . '</option>';
		}

		$result .= '</optgroup>';

	}
	
	return $result;
	
}

/**
 * Add Settings link to plugins page
 * 
 * @since 2.0.0
 */
function iwacontact_add_settings_link( $links, $file ) {
	static $this_plugin;
	if ( !$this_plugin ) $this_plugin = dirname( plugin_basename(__FILE__) ) . '/ajax-contact.php';
	if ( $file == $this_plugin ) {
		$settings_link = '<a href="' . get_bloginfo( 'wpurl' ) . '/wp-admin/options-general.php?page=ajaxcontact">Settings</a>';
		array_unshift( $links, $settings_link );
	}
	return $links;
}

/**
 * Add contact form edit post columns
 * 
 * @param array $columns The columns
 * @return array The new columns
 * @since 1.6.0
 */
function iwacontact_columns( $columns ) {
	
	$columns['submissions'] = __( 'Submissions', 'iwacontact' );
	$columns['fields'] = __( 'Fields', 'iwacontact' );
	$columns['shortcode'] = __( 'Short code', 'iwacontact' );
	return $columns;

}

/**
 * Echo the values for our contact form edit post columns
 * 
 * @param string $column The column to echo output for
 * @since 1.6.0
 */
function iwacontact_custom_columns( $column ) {

	global $post;
	
	switch ( $column ) {
		
		case 'shortcode':
			echo '[insert_ajaxcontact id=' . $post->ID . ']';
			break;
		
		case 'fields':
			echo iwacontact_get_field_count( $post->ID );
			break;
		
		case 'submissions':
			echo iwacontact_get_submission_count( $post->ID );
			break;
		
	}
	
}

/**
 * Filter for contact form and submission edit post row actions
 * 
 * @param array $actions The post row actions
 * @return array The new actions
 * @since 2.0.0
 */
function iwacontact_row_actions( $actions ) {

	global $post;

    if ( in_array( get_post_type(), array( 'iwacontactform', 'iwacontactsubmission' ) ) ) {
        unset( $actions['view'] );
        unset( $actions['inline hide-if-no-js'] );
    }

    if ( 'iwacontactform' == get_post_type() ) {
    	$submissions_text = sprintf( __( 'View Submissions (%1$d)', 'iwacontact' ), iwacontact_get_submission_count( $post->ID ) );
    	$actions['submissions'] = '<a href="' . admin_url( 'edit.php?post_type=iwacontactsubmission&form_id=' . $post->ID ) . '">' . $submissions_text . '</a>';
    }

    return $actions;

}

/**
 * Add form_id filter parameter to form submissions list
 * 
 * @param object $query The WP_Query object
 * @since 2.0.0
 */
function iwacontact_admin_posts_filter( $query ) {

    global $pagenow;

    if ( is_admin() && 'edit.php' == $pagenow && key_exists( 'form_id', $_GET ) && '' != trim( $_GET['form_id'] ) ) {
    	$query->query_vars['meta_key'] = '_form_id';
    	$query->query_vars['meta_value'] = $_GET['form_id'];
    }

}

/**
 * Add submissions link to contact form admin menu item
 * 
 * @since 2.0.0
 */
function iwacontact_admin_menu() {
	add_submenu_page( 'edit.php?post_type=iwacontactform', __( 'Submissions', 'iwacontact' ), __( 'Submissions', 'iwacontact' ), 'manage_options', 'edit.php?post_type=iwacontactsubmission' );
}