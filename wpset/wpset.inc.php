<?php
	
/**
 * WPSet - Easy WP Settings
 * v1.0.0
 *
 * Website URL:
 * www.mycetophorae.com/wordpress-extensions/wpset-developer-library/
 *
 * WPSet - WordPress settings helper
 * Copyright (C) 2011 Callan Milne
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * @package WPSet
 * @since 1.0.0
 **/

if ( !class_exists( 'IWAJAX_WPSet' ) ) {
	
	class IWAJAX_WPSet {
		
		/**
		 * Settings.
		 *
		 * The array of settings definitions.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var array
		 **/
		private $_settings;
		
		/**
		 * Settings groups.
		 *
		 * The array of settings groups definitions.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var array
		 **/
		private $_settings_groups;
		
		/**
		 * The settings.
		 *
		 * The array of settings.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var array
		 **/
		private $_the_settings;
		
		/**
		 * Attributes.
		 *
		 * This object's attributes.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var array
		 **/
		private $_attrs;
		
		/**
		 * Boolean field types.
		 *
		 * List of boolean field types.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var array
		 **/
		private $_bool_types = array( 'checkbox' );
		
		/**
		 * Multiple option field types.
		 *
		 * List of field types that have multiple options.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var array
		 **/
		private $_multi_types = array( 'radio', 'selectbox', 'multiselect', 'checklist' );
		
		/**
		 * Create a new WPSet object.
		 *
		 * @since 1.0.0
		 * @access public
		 * @param array|string $attrs The attributes to create a new WPSet object with
		 **/
		public function __construct( $attrs = '' ) {
			
			$defaults = array(
				
				// Both
				'prefix' => '',
				'title' => '', /*Required*/
				'type' => 'page',
				
				// Both (Advanced)
				'inc_url' => $this->_getCurrentDirectoryUrl() . '/inc',
				'inc_css_filename' => 'style.css',
				'inc_js_filename' => 'func.js',
				
				// Pages Only
				'capability' => 'manage_options',
				'menu_title' => '',
				'menu_slug' => '',
				'options_group' => '',
				'options_name' => '',
				
				// Metaboxes Only
				'autosave' => false,
				'context' => 'normal',
				'name' => '',
				'post_type' => 'page',
				'priority' => 'default'
				
			);
			
			$this->_attrs = wp_parse_args( $attrs, $defaults );
			
			if ( $this->_attrs['prefix'] === '' ) {
				$this->_attrs['field_prefix'] = md5( 'iwajax_wpset_' . $this->_getCurrentWPDir() . '_' . $this->_attrs['name'] );
				$this->_attrs['prefix'] = $this->_attrs['field_prefix'] . '_';
			}
			else $this->_attrs['field_prefix'] = $this->_attrs['prefix'] . 'values';
			
			if ( $this->_attrs['menu_title'] === '' ) $this->_attrs['menu_title'] = $this->_attrs['title'];
			if ( $this->_attrs['menu_slug'] === '' ) $this->_attrs['menu_slug'] = $this->_attrs['field_prefix'];
			if ( $this->_attrs['options_group'] === '' ) $this->_attrs['options_group'] = $this->_attrs['field_prefix'] . '_options';
			if ( $this->_attrs['options_name'] === '' ) $this->_attrs['options_name'] = $this->_attrs['field_prefix'];
			if ( $this->_attrs['name'] === '' ) $this->_attrs['name'] = preg_replace( '/[-_]$/', '', $this->_attrs['prefix'] );
			
			switch ( $this->_attrs['type'] ) {
				
				case 'page':
					add_action( 'admin_init', array( &$this, 'register' ) );
					add_action( 'admin_menu', array( &$this, 'addOptionsPage' ) );
					break;
				
				case 'metabox':
					add_action( 'add_meta_boxes', array( &$this, 'register' ) );
					add_action( 'save_post', array( &$this, 'saveMeta' ) );
					break;
				
			}
			
			add_action( 'admin_init', array( &$this, 'adminInit' ) );
			add_action( 'wp_ajax_' . $this->_prefix( 'img-attach-html' ), array( &$this, 'printImageAttachSelectHtml' ) );
			add_action( 'wp_ajax_' . $this->_prefix( 'img-attach-list-html' ), array( &$this, 'printImageAttachListHtml' ) );
			
			if ( !is_array( get_option( $this->_attrs['field_prefix'] ) ) )
				add_option( $this->_attrs['field_prefix'], array() );
			
		}
		
		/**
		 * Filter settings options.
		 *
		 * Converts comma separated string, array or associative array in to associative array.
		 *
		 * @since 1.0.0
		 * @access private
		 * @param array|string $options The options to filter.
		 * @return array The new options array.
		 **/
		private function _filterSettingOptions( $options ) {
			
			$options_array = array();
			
			if ( is_string( $options ) ) {
				$tmp_options_array = split( ',', $options );
				foreach ( $tmp_options_array as $option )
					$options_array[$option] = $option;
			}
			
			if ( is_array( $options ) ) {
				if ( count( array_filter( array_keys( $options ), 'is_string' ) ) == count( $options ) )
					$options_array = $options;
				else foreach ( $options as $option ) $options_array[$option] = $option;
			}
			
			return $options_array;
		}
		
		/**
		 * Get current directory url.
		 *
		 * @since 1.0.0
		 * @access private
		 * @return string The public url to the current directory.
		 **/
		private function _getCurrentDirectoryUrl() {
			return home_url( $this->_getCurrentWPDir() );
		}
		
		/**
		 * Get current WordPress directory.
		 *
		 * @since 1.0.0
		 * @access private
		 * @return string The current directory path relative to WordPress.
		 **/
		private function _getCurrentWPDir() {
			return str_replace( ABSPATH, '', dirname( __FILE__ ) );
		}
		
		/**
		 * Get image attachment list html.
		 *
		 * Returns the html necessary to display the attachment selection list.
		 * 
		 * @since 1.0.0
		 * @access private
		 * @param array $image_attachments An array of WordPress post objects.
		 * @return string The selection area html.
		 **/
		private function _getImageAttachListHtml( $image_attachments ) {
			$html = '<ul>';
			if ( is_array( $image_attachments ) && count( $image_attachments ) > 0 ) {
				foreach ( $image_attachments as $image ) {
					$img_src = wp_get_attachment_thumb_url( $image->ID );
					$html .= '<li class="image-attachment">'
						. "<img src='$img_src'>"
						. '<span class="title">' . $image->post_title . '</span>'
						. '<span class="date">Uploaded ' . $image->post_date . '</span>'
						. '<input type="hidden" value="' . $image->ID . '" class="attachment-id" />'
						. '</li>';
				}
			}
			else {
				$html .= '<li class="no-results">No images found, try uploading one.</li>';
			}
			$html .= '</ul>';
			return $html;
		}
		
		/**
		 * Get image attachment selection html.
		 *
		 * Returns the html necessary to display the attachment selection area.
		 * 
		 * @since 1.0.0
		 * @access private
		 * @param string|integer The post ID to get attachments relative to.
		 * @return string The selection area html.
		 **/
		private function _getImageAttachSelectHtml( $post_parent = 0 ) {
			
			$args = array( 'post_type' => 'attachment', 'numberposts' => 10, 'post_status' => null, 'post_mime_type' => 'image', 'post_parent' => $post_parent ); 
			$image_attachments = get_posts( $args );
			
			$settings_action = $this->_prefix( 'img-attach-html' );
			$search_action = $this->_prefix( 'img-attach-list-html' );
			
			$html = '<div class="image-attachment-selector">'
				. '<div class="top-bar"><p class="search-box">'
				. '<input type="text" class="search-input" value="" />'
				. '<input type="hidden" class="search-action" value="' . $search_action . '" />'
				. '<input type="button" class="button-secondary search-submit" value="Search" />'
				. '</p></div><div class="image-attachment-list">'
				. $this->_getImageAttachListHtml( $image_attachments )
				. '</div><div class="bottom-bar">'
				. '<input type="button" class="button-secondary cancel-set-attachment" value="Cancel" />'
				. '<input type="button" class="button-secondary upload-new-image" value="Upload New" />'
				. '<input type="hidden" name="settings-type" value="' . $this->_attrs['type'] . '" />'
				. '<input type="hidden" name="settings-action" value="' . $settings_action . '" />'
				. '</div></div>';
			
			return $html;
			
		}
		
		/**
		 * Get settings html.
		 *
		 * Get the settings area html ready for placing in metabox or admin page.
		 * 
		 * @since 1.0.0
		 * @access private
		 * @return string The settings area html.
		 **/
		private function _getSettingsHtml( $post = null ) {
			
			if ( count( $this->_settings_groups ) > 0 ) {
				
				$tabs_html = '';
				$sections_html = '';
				
				$count = 0;
				
				foreach ( $this->_settings_groups as $group ) {
					
					$settings_names = $group['settings'];
					$group_title = $group['title'];
					$group_description = ( $group['description'] !== '' ) ? '<span class="small">'.$group['description'].'</span>' : '';
					$html_class = 'group-' . $group['name'] . ' ' . $group['html_class'];
					
					$post_id = ( $post !== null ) ? $post->ID : '0';
					
					$tab_class = 'tab ' . $html_class
						. ( ( $count == 0 ) ? ' selected' : '' );
					$tabs_html .= "<li class='$tab_class'>$group_title</li>";
					
					$section_class = 'group ' . $html_class . ' stuffbox'
						. ( ( $count > 0 ) ? ' hidden' : '' );
					$sections_html .= "<div class='$section_class'>"
						. "<h3>$group_title $group_description</h3>"
						. "<input type='hidden' class='post-id' value='$post_id' />"
						. '<input type="hidden" class="ajax-url" value="' . admin_url( 'admin-ajax.php' ) . '" />'
						. '<table class="form-table">';
					
					foreach ( $settings_names as $setting_name ) {
						
						$setting = $this->_settings[$setting_name];
						
						$field_name = ( 'page' == $this->_attrs['type'] ) ? $this->_attrs['options_name'] : $this->_attrs['field_prefix'];
						$field_name .= '[' . $setting['name'] . ']';
						$field_title = $setting['title'];
						$field_value = $this->get( $setting['name'], null, false );
						$field_value = ( false === $field_value ) ? $setting['default'] : $field_value;
						$field_html = '';

						$tr_class = '';
						if ( is_array( $setting['class'] ) ) $tr_class .= implode( ' ', $setting['class'] );
						else if ( $setting['class'] != '' ) $tr_class .= ' ' . $setting['class'];
						
						$options = ( $setting['options'] !== '' ) ? $this->_filterSettingOptions( $setting['options'] ) : null;
						
						switch ( $setting['type'] ) {
							
							case 'input':
								$field_html .= "<input type='text' name='$field_name' id='$field_name' class='regular-text' value='$field_value' />";
								break;
							
							case 'textarea':
								$field_html .= "<textarea name='$field_name' id='$field_name' class='large-text' rows='3'>$field_value</textarea>";
								break;
							
							case 'multiselect':
								$field_html .= "<select multiple='multiple' name='" . $field_name . "[]' id='" . $field_name . "[]' class='regular-text'>";
								foreach ( $options as $k => $v ) {
									$selected = ( ( is_array( $field_value ) && in_array( $k, $field_value ) ) || ( is_string( $field_value ) && in_array( $k, split( ',', $field_value ) ) ) ) ? 'selected="selected"' : '';
									$field_html .= "<option value='$k' $selected>$v</option>";
								}
								$field_html .= '</select>';
								break;
							
							case 'selectbox':
								$field_html .= "<select name='" . $field_name . "[]' id='" . $field_name . "[]' class='regular-text'>";
								foreach ( $options as $k => $v ) {
									$selected = ( ( is_array( $field_value ) && in_array( $k, $field_value ) ) || ( is_string( $field_value ) && in_array( $k, split( ',', $field_value ) ) ) ) ? 'selected="selected"' : '';
									$field_html .= "<option value='$k' $selected>$v</option>";
								}
								$field_html .= '</select>';
								break;
							
							case 'checklist':
								foreach ( $options as $k => $v ) {
									$checked = ( ( is_array( $field_value ) && in_array( $k, $field_value ) ) || ( is_string( $field_value ) && in_array( $k, split( ',', $field_value ) ) ) ) ? 'checked="checked"' : '';
									$field_html .= "<label><input type='checkbox' name='" . $field_name . "[]' id='" . $field_name . "[]' value='$k' $checked /> $v</label>";
								}
								break;
							
							case 'radio':
								foreach ( $options as $k => $v ) {
									$checked = ( in_array( $k, split( ',', $field_value ) ) ) ? 'checked="checked"' : '';
									$field_html .= "<label><input type='radio' name='" . $field_name . "' id='" . $field_name . "' value='$k' $checked /> $v</label>";
								}
								break;
							
							case 'image':
								$the_action = $this->_prefix( 'img-attach-html' );
								$the_image = '';
								if ( $field_value > 0 )
									$the_image = '<img src="'
										. wp_get_attachment_thumb_url( $field_value )
										. '" />';
								$cimg_hidden = ( $field_value > 0 ) ? '' : 'hidden';
								$field_html .= "<div class='image-container'>$the_image</div>";
								$field_html .= "<input type='hidden' id='$field_name' name='$field_name' class='attachment-id-input' value='$field_value' />";
								$field_html .= "<input type='hidden' class='ajax-action' value='$the_action' />";
								$field_html .= "<input type='button' class='button-secondary set-image-attachment' value='Set Image' />";
								$field_html .= "<input type='button' class='button-secondary clear-image-attachment $cimg_hidden' value='Clear Image' />";
								break;
							
							case 'hidden':
								
								$field_html .= '';
								break;
							
						}
						
						$description = ( '' != $setting['description'] ) ? '<span class="description">' . $setting['description'] . '</span>' : '';
						$sections_html .= "<tr class=''><th scope='row'>"
							. "<label for='$field_name'>$field_title</label></th>"
							. "<td>$field_html $description</td></tr>";
						
					}
					
					$save_button = '<p class="submit">'
						. '<input type="submit" class="button-primary" value="Save Changes" />'
						. '</p>';
					
					$sections_html .= '</table>'
						. ( ( $this->_attrs['type'] != 'metabox' ) ? $save_button : '' )
						. '</div>';
					
					$count++;
					
				}
				
				return '<div class="iwajaxwpset-tabbed-settings">'
					. '<ul class="settings-tabs">' . $tabs_html . '</ul>'
					. '<div class="settings-sections">' . $sections_html . '</div>'
					. '</div>';
				
			}
			else return '<span class="iwajaxwpset-error no-settings">No settings to display</span>';
			
		}
		
		/**
		 * Load settings from WordPress.
		 *
		 * @since 1.0.0
		 * @access private
		 * @param string $post_id A post_id if this is a metabox type.
		 **/
		private function _loadSettings( $post_id = null ) {
			
			$wpset_settings = array();
			switch ( $this->_attrs['type'] ) {
				case 'page':
					$options = get_option( $this->_attrs['options_name'] );
					foreach( $this->_settings as $key => $array )
						$wpset_settings[$key] = ( is_array( $options ) && key_exists( $key, $options ) ) ? $options[$key] : null;
					break;
				case 'metabox':
					if ( !is_array( $this->_the_settings ) || $this->_the_settings['the_post_id'] !== $post_id ) {
						$post_id = ( $post_id === null ) ? get_the_ID() : $post_id;
						$custom_fields = get_post_custom( $post_id );
						$wpset_settings = array( 'the_post_id' => $post_id );
						foreach ( $custom_fields as $key => $value ) {
							if ( strstr( $key, $this->_attrs['prefix'] ) !== false ) {
								$new_key = substr( $key, strlen( $this->_attrs['prefix'] ) );
								$wpset_settings[$new_key] = $value[0];
							}
						}
					}
					break;
			}
			$this->_the_settings = $wpset_settings;
			
		}
		
		/**
		 * Prefix a string.
		 *
		 * @since 1.0.0
		 * @access private
		 * @param string $name A string (usually key) to prefix.
		 * @return string Prefixed string.
		 **/
		private function _prefix( $name ) {
			return $this->_attrs['prefix'] . $name;
		}
		
		/**
		 * Update setting.
		 *
		 * @since 1.0.0
		 * @access private
		 * @param string $key The setting to update.
		 * @param string $value The new value.
		 * @param string $post_id The post_id if this is a metabox type.
		 **/
		private function _updateSetting( $key, $value, $post_id = null ) {
			
			$setting_key = $this->_prefix( $key );
			switch ( $this->_attrs['type'] ) {
				case 'page':
					$options = get_option( $this->_attrs['options_name'] );
					$options[$setting_key] = $value;
					return update_option( $this->_attrs['options_name'], $options );
					break;
				case 'metabox':
					return update_post_meta( $post_id, $setting_key, $value );
					break;
			}
			
		}
		
		/**
		 * Update settings.
		 *
		 * Update settings from $_POST or similar array.
		 * 
		 * @since 1.0.0
		 * @access private
		 * @param string $from The array to load from, default is $_POST.
		 * @param string $post_id The post_id if this is a metabox type.
		 **/
		private function _updateSettings( $from = null, $post_id = null ) {
			
			$from = ( $from === null ) ? $_POST : $from;
			foreach ( $from[$this->_attrs['field_prefix']] as $k => $v )
				$this->_updateSetting( $k, $v, $post_id );
			
		}
		
		/**
		 * Add setting.
		 * 
		 * @since 1.0.0
		 * @access public
		 * @param array|string $args The arguments to create a new setting with
		 **/
		public function add( $args = '' ) {
			$defaults = array(
				'allow_html' => false,
				'class' => '',
				'default' => '',
				'description' => '',
				'group' => '',
				'label' => '',
				'name' => '', /*Required*/
				'options' => '',
				'title' => '', /*Required*/
				'type' => 'input'
			);
			$r = wp_parse_args( $args, $defaults );
			if ( $r['label'] === '' ) $r['label'] = $r['title'];
			$this->_settings_groups[$r['group']]['settings'][] = $r['name'];
			$this->_settings[$r['name']] = $r;
		}
		
		/**
		 * Add settings group.
		 * 
		 * @since 1.0.0
		 * @access public
		 * @param array|string $args The arguments to create a new settings group with
		 **/
		public function addGroup( $args = '' ) {
			$defaults = array(
				'description' => '',
				'html_class' => '',
				'name' => '', /*Required*/
				'title' => '' /*Required*/
			);
			$r = wp_parse_args( $args, $defaults );
			if ( $r['html_class'] === '' ) $r['html_class'] = $r['name'];
			$this->_settings_groups[$r['name']] = $r;
			$this->_settings_groups[$r['name']]['settings'] = array();
		}
		
		/**
		 * Add options page.
		 *
		 * Add our new options page to WordPress
		 * Called by WordPress on the admin_menu action for page types.
		 * 
		 * @since 1.0.0
		 * @access public
		 **/
		public function addOptionsPage() {
			add_options_page( $this->_attrs['title'], $this->_attrs['menu_title'], $this->_attrs['capability'], $this->_attrs['menu_slug'], array( &$this, 'printPageHtml' ) );
		}
		
		/**
		 * Admin init.
		 *
		 * Add our stylesheet and javascript to the WordPress admin_init
		 * 
		 * @since 1.0.0
		 * @access public
		 **/
		public function adminInit() {
			// wp_enqueue_style( $this->_prefix( 'wpset-css' ), $this->_attrs['inc_url'] . '/' . $this->_attrs['inc_css_filename'] );
			// wp_enqueue_style( 'thickbox' );
			// wp_enqueue_script( $this->_prefix( 'wpset-js' ), $this->_attrs['inc_url'] . '/' . $this->_attrs['inc_js_filename'], array( 'jquery', 'media-upload', 'thickbox' ) );
			wp_enqueue_style( 'iwajax_wpset-css', $this->_attrs['inc_url'] . '/' . $this->_attrs['inc_css_filename'] );
			wp_enqueue_style( 'thickbox' );
			wp_enqueue_script( 'iwajax_wpset-js', $this->_attrs['inc_url'] . '/' . $this->_attrs['inc_js_filename'], array( 'jquery', 'media-upload', 'thickbox' ) );
		}
		
		/**
		 * Get setting.
		 *
		 * $post_id not required for metabox settings if used inside the loop.
		 * 
		 * @since 1.0.0
		 * @access public
		 * @param string $key The setting name.
		 * @param string $post_id If getting metabox settings by post_id.
		 **/
		public function get( $key, $post_id = null, $return_default = true ) {
			
			$this->_loadSettings( $post_id );
			if ( !is_array( $this->_the_settings ) ) throw new Exception( 'No settings' );
			if ( key_exists( $key, $this->_the_settings ) && null !== $this->_the_settings[$key] ) return $this->_the_settings[$key];
			else {
				if ( $return_default ) return $this->_settings[$key]['default'];
				else return false;
			} 
			
		}
		
		/**
		 * Print image attachment list html.
		 *
		 * Prints the html necessary to display the attachment selection list and exits.
		 * This function is a wp_ajax callback.
		 * 
		 * @since 1.0.0
		 * @access public
		 **/
		public function printImageAttachListHtml() {
			
			$search_term = ( key_exists( 'search_term', $_POST ) ) ? $_POST['search_term'] : null;
			if ( $search_term !== null ) $search_term = trim( $search_term );
			
			$post_parent = ( key_exists( 'post_id', $_POST ) ) ? $_POST['post_id'] : '';
			
			$args = array( 'post_type' => 'attachment', 'numberposts' => -1, 'post_status' => null, 'post_mime_type' => 'image', 'post_parent' => $post_parent ); 
			$image_attachments = get_posts( $args );
			
			if ( is_array( $image_attachments ) && $search_term !== null ) {
				$searched_attachments = array();
				foreach ( $image_attachments as $attachment ) {
					$add_attachment = false;
					foreach ( split( ' ', $search_term ) as $keyword ) {
						if ( stripos( $attachment->post_title, $keyword ) !== false ) {
							$add_attachment = true;
							break 1;
						}
					}
					if ( $add_attachment )
						$searched_attachments[] = $attachment;
				}
				$image_attachments = $searched_attachments;
			}
			
			print $this->_getImageAttachListHtml( $image_attachments );
			exit();
			
		}
		
		/**
		 * Print image attachment select html.
		 *
		 * Prints the html necessary to display the attachment selection html and exits.
		 * This function is a wp_ajax callback.
		 * 
		 * @since 1.0.0
		 * @access public
		 **/
		public function printImageAttachSelectHtml() {
			
			switch ( $this->_attrs['type'] ) {
				case 'page':
					print $this->_getImageAttachSelectHtml();
					break;
				case 'metabox':
					$post_parent = ( key_exists( 'post_id', $_POST ) ) ? $_POST['post_id'] : '';
					print $this->_getImageAttachSelectHtml( $post_parent );
					break;
			}
			exit();
			
		}
		
		/**
		 * Print settings metabox html.
		 *
		 * Prints the html for our settings metabox if this is a metabox type.
		 * 
		 * @since 1.0.0
		 * @access public
		 **/
		public function printMetaboxHtml( $post, $metabox ) {
			print wp_nonce_field( 'update_settings', $this->_attrs['name'], true, false )
				. $this->_getSettingsHtml( $post );
		}
		
		/**
		 * Print settings page html.
		 *
		 * Prints the html for our settings page if this is a page type.
		 * 
		 * @since 1.0.0
		 * @access public
		 **/
		public function printPageHtml() {
			print '<div class="wrap">'
				. '<h2>' . $this->_attrs['title'] . '</h2>'
				. '<div class="iwajaxwpset-settings-page">'
				. '<form method="post" action="options.php">'
				. '<div class="metabox-holder">'
				. $this->_getSettingsHtml();
			settings_fields( $this->_attrs['options_group'] );
			print '</div></form></div></div>';
		}
		
		/**
		 * Register.
		 *
		 * Called on various actions depending on settings type.
		 * Adds our metabox to WordPress if this is a metabox type.
		 * Registers our setting to WordPress if this is a page type.
		 * 
		 * @since 1.0.0
		 * @access public
		 **/
		public function register() {
			
			switch ( $this->_attrs['type'] ) {
				
				// Register page
				case 'page':
					register_setting( $this->_attrs['options_group'], $this->_attrs['options_name'], array( &$this, 'validate' ) );
					break;
				
				// Register metabox
				case 'metabox':
					add_meta_box( $this->_prefix( $this->_attrs['name'] ), $this->_attrs['title'], array( &$this, 'printMetaboxHtml' ), $this->_attrs['post_type'], $this->_attrs['context'], $this->_attrs['priority'], array( 'name' => $this->_attrs['name'] ) );
					break;
				
			}
			
		}
		
		/**
		 * Save meta.
		 *
		 * The callback function added to the save_post hook that saves our metabox settings.
		 * 
		 * @since 1.0.0
		 * @access public
		 * @param array $post_id The post_id to save the data to.
		 **/
		public function saveMeta( $post_id ) {
			
			if ( !key_exists( $this->_attrs['name'], $_POST ) || !wp_verify_nonce( $_POST[$this->_attrs['name']], 'update_settings' ) ||
				( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) && $this->_attrs['autosave'] ) ||
				( key_exists( 'post_type', $_POST ) && $this->_attrs['post_type'] != $_POST['post_type'] ) ||
				( key_exists( 'post_type', $_POST ) && $this->_attrs['post_type'] == $_POST['post_type'] && !current_user_can( 'edit_page', $post_id ) ) )
				return $post_id;
			
			$this->_updateSettings( $_POST, $post_id );
			
		}
		
		/**
		 * Set setting value.
		 *
		 * $post_id not required for metabox settings if used inside the loop.
		 * 
		 * @since 1.0.0
		 * @access public
		 * @param string $key The setting to update.
		 * @param string $value The new value.
		 * @param string $post_id The post_id if this is a metabox type and not in the loop.
		 **/
		public function set( $key, $value, $post_id = null ) {
			$post_id = ( null !== $post_id ) ? $post_id : get_the_ID();
			return $this->_updateSetting( $key, $value, $post_id );
		}
		
		/**
		 * Validate.
		 *
		 * The function to validate our settings saved if this is a page type.
		 * 
		 * @since 1.0.0
		 * @access public
		 * @param array $input The submission data to validate.
		 **/
		public function validate( $input ) {

			foreach ( $this->_settings as $setting_name => $attrs ) {
				
				$key = $setting_name;
				if ( in_array( $attrs['type'], $this->_bool_types ) )
					$input[$key] = ( $input[$key] == 1 ) ? 1 : 0;
				else if ( in_array( $attrs['type'], $this->_multi_types ) ) {
					$input[$key] = ( is_array( $input[$key] ) ) ? join( ',', $input[$key] ) : $input[$key];
					$input[$key] = ( !key_exists( $key, $input ) || is_null( $input[$key] ) || $input[$key] === false ) ? '' : $input[$key];
				}
				else if ( $setting['allow_html'] === false )
					$input[$key] = wp_filter_nohtml_kses( $input[$key] );
				
			}

			return $input;
			
		}
		
	}
	
}
