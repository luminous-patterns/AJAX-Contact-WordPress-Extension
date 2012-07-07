<?php
/*
Plugin Name: AJAX Contact
Plugin URI: http://www.mycetophorae.com/wordpress-extensions/ajax-contact/
Description: Use this plugin to be easily able to add a AJAX Contact forms to pages, posts and widget areas
Author: Callan Milne
Version: 2.1.0
Author URI: http://www.mycetophorae.com
Copyright 2011 Callan Milne

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

load_plugin_textdomain( 'iwacontact', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

/**
 * Include our slightly custom WPSet library
 **/
include_once( 'wpset/wpset.inc.php' );

/**
 * Include our custom settings
 **/
include_once( 'ajax-settings.php' );

/**
 * Include some other mixed functions
 **/
include_once( 'functions.php' );

/**
 * Include the code for the admin UI
 **/
include_once( 'form-admin.php' );

/**
 * Include the code for the form display and submission processing
 **/
include_once( 'form-display.php' );

/**
 * Include the code for our widget
 **/
include_once( 'form-widget.php' );