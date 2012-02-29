<?php

	// AJAX Contact Options Page
	$ajaxcontact = new IWAJAX_WPSet( array(
		'prefix'        => 'ajaxcontact',
		'title'         => __( 'AJAX Contact Settings', 'iwacontact' ),
		'type'          => 'page',
		'capability'    => 'manage_options',
		'menu_title'    => __( 'AJAX Contact', 'iwacontact' ),
		'menu_slug'     => 'ajaxcontact',
		'options_group' => 'ajaxcontact_options',
		'options_name'  => 'ajaxcontact'
	) );

	// General Options
	$ajaxcontact->addGroup( array(
		'name'          => 'general',
		'title'         => __( 'General', 'iwacontact' )
	) );

	$httphost = str_replace( 'www.', '', $_SERVER['HTTP_HOST'] );

	$ajaxcontact->add( array(
		'group'         => 'general',
		'name'          => 'default_from',
		'title'         => __( 'Default From Address', 'iwacontact' ),
		'default'       => 'no-reply@' . $httphost
	) );

	$ajaxcontact->add( array(
		'group'         => 'general',
		'name'          => 'default_to',
		'title'         => __( 'Default To Address', 'iwacontact' ),
		'default'       => 'webmaster@' . $httphost
	) );

	$ajaxcontact->add( array(
		'group'         => 'general',
		'name'          => 'default_subject',
		'title'         => __( 'Default Subject', 'iwacontact' ),
		'default'       => __( 'New contact form submission!', 'iwacontact' )
	) );

	$ajaxcontact->add( array(
		'group'         => 'general',
		'name'          => 'default_submit_value',
		'title'         => __( 'Default Submit Button Text', 'iwacontact' ),
		'default'       => __( 'Send Enquiry', 'iwacontact' )
	) );

	$ajaxcontact->add( array(
		'group'         => 'general',
		'name'          => 'default_redirect',
		'title'         => __( 'Default URL redirect on submission', 'iwacontact' )
	) );

	// Spam Prevention Options
	$ajaxcontact->addGroup( array(
		'name'          => 'captcha',
		'title'         => __( 'Spam Prevention', 'iwacontact' )
	) );

	$ajaxcontact->add( array(
		'group'         => 'captcha',
		'name'          => 'captcha_type',
		'title'         => __( 'Default Anti-bot Validation', 'iwacontact' ),
		'type'          => 'selectbox',
		'options'       => array(
			'none'      => 'None',
			'honeypot'  => __( 'Honeypot', 'iwacontact' ),
			'recaptcha' => __( 'Google ReCAPTCHA', 'iwacontact' )
		),
		'default'       => 'honeypot'
	) );

	$ajaxcontact->add( array(
		'group'         => 'captcha',
		'name'          => 'use_captcha',
		'title'         => __( 'Enable by default', 'iwacontact' ),
		'type'          => 'checklist',
		'options'       => array(
			'yes'       => __( 'Yes, enable Anti-bot Validation by default on new forms', 'iwacontact' )
		),
		'default'       => 'yes'
	) );

	$ajaxcontact->add( array(
		'group'         => 'captcha',
		'name'          => 'recaptcha_api_pub_key',
		'title'         => __( 'ReCAPTCHA Public Key', 'iwacontact' ),
		'description'   => sprintf( __( 'Enter your ReCAPTCHA Public Key here.  If you don\'t already have one, <a href="%1$s" target="_blank">click here to get one</a>', 'iwacontact' ), 'http://www.google.com/recaptcha/whyrecaptcha' )
	) );

	$ajaxcontact->add( array(
		'group'         => 'captcha',
		'name'          => 'recaptcha_api_priv_key',
		'title'         => __( 'ReCAPTCHA Private Key', 'iwacontact' ),
		'description'   => __( 'Enter your ReCAPTCHA Private Key here.', 'iwacontact' )
	) );

?>