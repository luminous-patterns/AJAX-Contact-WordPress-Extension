var iwac_sort_array = '';

jQuery( document ).ready( function() {

	if ( jQuery( '#post_type' ).length > 0 && jQuery( '#post_type' ).val() == 'iwacontactform' ) {

		jQuery( '#use_custom_body' ).click( function() {
			jQuery( this ).parent().siblings( 'div.custom-body-editor' ).toggleClass( 'hidden' );
		} );

		jQuery( '#iwacf_use_captcha' ).click( function() {
			jQuery( '#iwacf_captcha_type' ).iwacontactToggleDisabled()
		} );

		// Change Publish Metabox Stuff
		var publishbox = jQuery( 'div#submitdiv' );
		if ( publishbox.length > 0 ) {
			publishbox.find( 'h3 span' ).html(  );
		}

		// Check to make sure this page contains an edit form section
		if ( jQuery( '#formFields div.form-field' ).length > 0 ) {
			// Do some initialisation stuff
			enableAdd();
			enableDelete();
			enableFields();
			updateIcons();
			// Show the first element editor box
			showFieldEditor( '0', false );
			jQuery( '#iwacf_shortcode' ).click( function() {
				jQuery( this ).select();
			} );
			jQuery( "#fieldDisplays" ).sortable( {
				axis: "y",
				cursor: "move",
				placeholder: 'placeholder',
				forcePlaceholderSize: true,
				update: function() {
					updateDisplayOrder();
				}
			} );
			jQuery( "#fieldDisplays" ).disableSelection();
		}

		var settings_tabs = jQuery( 'ul.iwac-settings-tabs li' );
		var settings_pages = jQuery( 'div.iwac-settings-pages div.iwac-settings-page' );
		if ( settings_tabs.length > 0 ) {
			settings_tabs.click( function() {
				var match = /iwacs\-([a-z-]+)/.exec( jQuery( this ).attr( 'class' ) );
				var page_name = match[1];
				settings_pages.hide();
				jQuery( 'div.iwac-settings-pages div.iwacs-' + page_name ).show();
				settings_tabs.removeClass( 'selected' );
				jQuery( this ).addClass( 'selected' );
			} );
		}

		jQuery( 'input.iwac-lv-input' ).keyup( function() {
			var match = /(([2-9g-z]{1}[4-8t-x]{2}[0-3]{1})([0-6f-q]{3}[a-z]{1})([0-9a-g]{4})([4-7a-c]{2}[6-9b-g]{1}[2-6]{1}[2-4]{2}))/i.exec( jQuery( this ).val() );
			if ( match != null ) {
				jQuery( 'input.iwac-lv-button' ).removeAttr( 'disabled' );
				console.log( match[3] );
			}
			else {
				jQuery( 'input.iwac-lv-button' ).attr( 'disabled', 'disabled' );
			}
		} );

	}

} );

function updateDisplayOrder() {
	iwac_sort_array = jQuery( "#fieldDisplays" ).sortable( "toArray" );
	for ( i = 0; i < iwac_sort_array.length; i++ ) {
		var fieldID = parseInt( iwac_sort_array[i].replace( /fieldDisplay\_/, '' ) );
		jQuery( '#iwacf_displayorder_' + fieldID ).val( i + 1 );
	}
}

function showFieldEditor( fieldID, focus ) {
	jQuery( '#formFields div.form-field' ).hide().removeClass( 'active' );
	jQuery( '#formFields li.field-display' ).removeClass( 'selected' );
	jQuery( '#formFields #formField_' + fieldID ).show().addClass( 'active' );
	jQuery( '#formFields #fieldDisplay_' + fieldID ).addClass( 'selected' );
	if ( focus )
		jQuery( '#formFields #formField_' + fieldID + ' input.field-title' ).focus();
}

function enableAdd() {
	// When the #addFormField button is clicked
	jQuery( 'input.addFieldButton' ).unbind();
	jQuery( 'input.addFieldButton' ).click( function() {
		// Get the ID of the last field that currently exists
		var lastFieldID = jQuery( '#formFields' ).children( 'div.form-field:last' ).attr( 'id' );
		// Strip out the formField_ part of the ID to return just the number
		lastFieldID = lastFieldID.replace( /formField\_/, '' );
		// Add 1 to the last ID number
		var newID = parseInt( lastFieldID ) + 1;
		// Clone the div.form-field template, append it to the #formFields div and fade it in
		jQuery( '#formFields' ).append( jQuery( '#newFormField' ).html() );
		jQuery( '#formFields' ).children( 'div.form-field:last' ).attr( 'id', 'formField_' + newID );
		jQuery( '#formFields' ).find( 'li.field-display:last' ).attr( 'id', 'fieldDisplay_' + newID );
		// Assign the new fields jQuery object to the newfield var
		var newfield = jQuery( '#formField_' + newID );
		var newdisplay = jQuery( '#fieldDisplay_' + newID );
		// Get the html contents of our new field in to the newhtml var
		var newfieldhtml = newfield.html();
		// Replace any _x's with our new ID (e.g. _2 )
		newfield.html( newfieldhtml.replace( /\_x/g, '_' + newID ) );
		jQuery( '#fieldDisplays' ).append( '<li id="fieldDisplay_' + newID + '" class="field-display">' + newdisplay.html().replace( /\_x/g, '_' + newID ) + '</li>' );
		newdisplay.remove();
		showFieldEditor( newID, true );
		// Set the default display order of this field
		jQuery( '#iwacf_displayorder_' + newID ).val( newID+1 );
		// Enable the delete button for this field
		enableAdd();
		enableDelete();
		enableFields();
		updateDisplayOrder();
		enableTooltips();
	} );
}

function enableFields() {
	jQuery( '#formFields li.field-display a' ).unbind();
	jQuery( '#formFields li.field-display a' ).click( function() {
		var fieldID = jQuery( this ).parent().attr( 'id' ).replace( /fieldDisplay\_/, '' );
		showFieldEditor( fieldID, true );
		return false;
	} );
	jQuery( '#formFields input.field-title' ).unbind();
	jQuery( '#formFields input.field-title' ).keyup( function() {
		var fieldID = jQuery( this ).attr( 'id' );
		var fieldID = fieldID.replace( /iwacf\_fieldname\_/, '' );
		if ( jQuery( this ).val() != '' ) {
			jQuery( '#formFields #fieldDisplay_' + fieldID + ' a' ).html( jQuery( this ).val() );
			var field_id = jQuery( this ).val();
			field_id = jQuery.trim( field_id.toLowerCase() );
			field_id = field_id.replace( /\ /g, '_' );
			field_id = field_id.replace( /[^a-z0-9-_]/g, '' );
			console.log (field_id);
			jQuery( this ).parent().parent().find( 'input.field-id' ).val( field_id );
		}
		else {
			jQuery( '#formFields #fieldDisplay_' + fieldID + ' a' ).html( 'Field ' + fieldID );
			jQuery( this ).parent().parent().find( 'input.field-id' ).val( 'field_' + fieldID );
		}
	} );
	jQuery( '#formFields .field-type, #formFields .field-validation' ).unbind();
	jQuery( '#formFields .field-type, #formFields .field-validation' ).change( function() {
		updateIcons();
	} );
	jQuery( '#formFields .field-required' ).unbind();
	jQuery( '#formFields .field-required' ).change( function() {
		var fieldID = jQuery( this ).attr( 'id' );
		var fieldID = fieldID.replace( /iwacf\_fieldrequired\_/, '' );
		jQuery( '#formFields #fieldDisplay_' + fieldID ).toggleClass( 'required' );
		console.log( fieldID );
		console.log( '#formFields #fieldDisplay_' + fieldID );
	} );
}

function updateIcons() {
	jQuery( '#formFields .field-type, #formFields .field-validation' ).each( function() {
		if ( jQuery( this ).hasClass( 'field-validation' ) ) {
			if ( jQuery( this ).val() != 'none' ) {
				var newicon = 'icon-' + jQuery( this ).val();
				var fieldID = jQuery( this ).attr( 'id' ).replace( /iwacf\_fieldvalidation\_/, '' );
				changeIcon( fieldID, newicon );
			}
		}
		else {
			switch ( jQuery( this ).val() ) {
				case 'input': //break;
				case 'password': //break;
				case 'textarea': //break;
				case 'hidden': //break;
				case 'readonly':
					jQuery( this ).parent().parent().find( 'p.iwacf_fieldoptions' ).removeClass( 'hidden' );
					jQuery( this ).parent().parent().find( 'p.iwacf_fieldoptions label' ).html( 'Default Value <span class="iwacf-help help-default"></span>' );
					break;
				case 'sendcopy': //break;
				case 'checkbox': //break;
				case 'h1': //break;
				case 'h2': //break;
				case 'h3': //break;
				case 'h4':
					jQuery( this ).parent().parent().find( 'p.iwacf_fieldoptions' ).addClass( 'hidden' );
					break;
				case 'radio': //break;
				case 'selectbox': //break;
				case 'multiselect':
					jQuery( this ).parent().parent().find( 'p.iwacf_fieldoptions' ).removeClass( 'hidden' );
					jQuery( this ).parent().parent().find( 'p.iwacf_fieldoptions label' ).html( 'Options (1 per line) <span class="iwacf-help help-options"></span>' );
					break;
			}
			var newicon = 'icon-' + jQuery( this ).val();
			var fieldID = jQuery( this ).attr( 'id' ).replace( /iwacf\_fieldtype\_/, '' );
			changeIcon( fieldID, newicon );
		}
	} );
	enableTooltips();
}

function changeIcon( fieldID, icon ) {
	jQuery( '#fieldDisplays #fieldDisplay_' + fieldID ).removeClass( 'icon-input icon-password icon-radio icon-checkbox icon-selectbox icon-multiselect icon-textarea icon-email icon-hidden icon-sendcopy icon-h1 icon-h2 icon-h3 icon-h4' ).addClass( icon ) ;
}

function enableTooltips() {
	hideTooltips();
	jQuery( 'span.iwacf-help' ).unbind();
	jQuery( 'span.iwacf-help' ).mouseenter( function( e ) {
		hideTooltips();
		var element = jQuery( this );
		var regex = /help\-([a-z0-9]+)/i;
		var match = regex.exec( element.attr( 'class' ) );
		var tooltip = match[1];
		var offset = jQuery( this ).closest( '.postbox' ).offset();
		var positionX = e.pageX - offset.left;
		var positionY = e.pageY - offset.top;
		console.log( 'x' + tooltip + 'y' + positionX + 'y' + positionY );
		showTooltip( tooltip, positionX, positionY );
	} );
	jQuery( 'span.iwacf-help' ).mouseleave( function() {
		var element = jQuery( this );
		var regex = /help\-([a-z0-9]+)/i;
		var match = regex.exec( element.attr( 'class' ) );
		var tooltip = match[1];
		hideTooltip( tooltip );
	} );
}

function showTooltip( tooltip, x, y ) {
	var element = jQuery( 'div.iwacf-tooltip.tooltip-' + tooltip );
	element.css( { 'top': y, 'left': x } );
	element.fadeIn();
}

function hideTooltip( tooltip ) {
	var element = jQuery( 'div.iwacf-tooltip.tooltip-' + tooltip );
	element.fadeOut();
}

function hideTooltips() {
	jQuery( 'div.iwacf-tooltip' ).hide();
}

function enableDelete() {
	// When a delete button is clicked
	if ( jQuery( '.deleteFieldButton' ).length == 2 )
		jQuery( '.deleteFieldButton:first' ).attr( 'disabled', 'disabled' ).addClass( 'disabled' );
	else if ( jQuery( '.deleteFieldButton' ).length == 3 )
		jQuery( '.deleteFieldButton:first' ).removeAttr( 'disabled' ).removeClass( 'disabled' );
	jQuery( '.deleteFieldButton' ).unbind();
	jQuery( '.deleteFieldButton' ).click( function() {
		// Get the ID of this delete button
		var deleteID = jQuery( this ).attr( 'id' )
		// Get the ID number of the form field this remove button belongs to
		var fieldSequence = deleteID.replace( /removeField\_/, '' );
		// Find this form field, fade it out and when thats finished, remove it from the page
		jQuery( '#formFields #fieldDisplays #fieldDisplay_' + fieldSequence ).remove();
		jQuery( '#formFields #formField_' + fieldSequence ).remove();
		showFieldEditor( jQuery( '#formFields div.form-field:first' ).attr( 'id' ).replace( /formField\_/, '' ), true );
		enableDelete();
		updateDisplayOrder();
	} );
}

( function( $ ) {
    $.fn.iwacontactToggleDisabled = function() {
        return this.each( function() {
            this.disabled = !this.disabled;
        } );
    };
} ) ( jQuery );