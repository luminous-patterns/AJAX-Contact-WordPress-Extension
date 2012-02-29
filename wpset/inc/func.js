
jQuery( document ).ready( function() {
	
	var container_selector = 'div.iwajaxwpset-tabbed-settings';
	
	if ( jQuery( container_selector ).length > 0 ) {
		jQuery( container_selector ).each( function() {
			var these_settings = jQuery( this );
			var settings_tabs = these_settings.find( 'ul.settings-tabs li.tab');
			var settings_pages = these_settings.find( 'div.settings-sections div.group' );
			if ( settings_tabs.length > 0 ) {
				settings_tabs.click( function() {
					var match = /group\-([a-z-]+)/.exec( jQuery( this ).attr( 'class' ) );
					var page_name = match[1];
					settings_pages.addClass( 'hidden' );
					these_settings.find( 'div.settings-sections div.group-' + page_name ).removeClass( 'hidden' );
					settings_tabs.removeClass( 'selected' );
					jQuery( this ).addClass( 'selected' );
				} );
			}
		} );
	}
	
	
	jQuery( container_selector + ' div.settings-sections div.group input.clear-image-attachment' ).click( function( e ) {
		e.preventDefault();
		var the_attachment_area = jQuery( this ).parent();
		the_attachment_area.find( 'input.attachment-id-input' ).val( '' );
		the_attachment_area.find( 'div.image-container' ).html( '' );
		the_attachment_area.find( 'input.clear-image-attachment' ).addClass( 'hidden' );
	} );
	
	jQuery( container_selector + ' div.settings-sections div.group input.set-image-attachment' ).click( function( e ) {
		e.preventDefault();
		var the_group = jQuery( this ).parents( 'div.group' );
		var the_attachment_area = jQuery( this ).parent();
		var the_post_id = the_group.find( 'input.post-id' ).val();
		var the_ajax_url = the_group.find( 'input.ajax-url' ).val();
		var the_vars = {
			action : the_attachment_area.find( 'input.ajax-action' ).val(),
			post_id : the_post_id
		};
		jQuery.post(
			the_ajax_url,
			the_vars,
			function( html ) {
				the_attachment_area.append( html );
				
				jQuery( the_attachment_area ).find( 'li.image-attachment' ).click( function( e ) {
					e.preventDefault();
					the_attachment_area.find( 'input.attachment-id-input' ).val( jQuery( this ).find( 'input.attachment-id' ).val() );
					the_attachment_area.find( 'div.image-container' ).html( jQuery( this ).find( 'img' ).clone( false ) );
					the_attachment_area.find( 'input.clear-image-attachment' ).removeClass( 'hidden' );
					jQuery( this ).parents( 'div.image-attachment-selector' ).fadeOut( function( e ) {
						jQuery( this ).remove();
					} );
				} );
				
				jQuery( the_attachment_area ).find( 'input.cancel-set-attachment' ).click( function( e ) {
					e.preventDefault();
					jQuery( this ).parents( 'div.image-attachment-selector' ).fadeOut( function( e ) {
						jQuery( this ).remove();
					} );
				} );
				
				jQuery( the_attachment_area ).find( 'input.upload-new-image' ).click( function( e ) {
					e.preventDefault();
					jQuery( this ).parents( 'div.image-attachment-selector' ).fadeOut( function( e ) {
						jQuery( this ).remove();
					} );
					var post_id_extra = ( the_post_id != '0' ) ? 'post_id=' + the_post_id + '&' : '';
					tb_show( '', 'media-upload.php?' + post_id_extra + 'type=image&TB_iframe=1' );
				} );
				
				jQuery( the_attachment_area ).find( 'input.search-submit' ).click( function( e ) {
					e.preventDefault();
					var the_search_term = jQuery( this ).siblings( 'input.search-input' ).val();
					var search_action = jQuery( this ).siblings( 'input.search-action' ).val();
					var the_attachment_list = the_attachment_area.find( 'div.image-attachment-list > ul' );
					var the_search_vars = {
						action : search_action,
						post_id : the_post_id
					};
					if ( the_search_term != '' )
						the_search_vars.search_term = the_search_term;
					jQuery.post(
						the_ajax_url,
						the_search_vars,
						function( html ) {
							the_attachment_list.replaceWith( html );
						}
					);
				} );
				
			}
		);
	} );
	
} );
