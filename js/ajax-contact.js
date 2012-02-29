jQuery( document ).ready( function() {
	jQuery( '.iwacontact' ).iwacontactForm();
} );


jQuery.fn.iwacontactForm = function() {
	return this.each( function() {
		the_form = new iwacontactConstruct( jQuery( this ) );
		the_form.init();
	} );
}

function iwacontactConstruct( jQuery_obj ) {
	return {

		_dom_elem: null,

		init: function() {
			
			this._dom_elem = jQuery_obj;
			
			var contactForm = this._dom_elem;
			var contactObj = this;
			
			// Remove the iwac_no_js field
			contactForm.find( 'input[name=iwac_no_js]' ).remove();

			// Make sure the honeypot anti-bot validation fields are hidden
			contactForm.find( 'input.iwac_abval' ).css( {
				width: '1px !important',
				height: '1px !important',
				padding: '0px !important',
				border: 'none !important',
			} );

			// Bind our ajax submit function to our form submit action
			this._dom_elem.submit( function( e ) {

				// Prevent form from submitting
				e.preventDefault();

				// Clear any errors from the last submission
				contactObj._clearFormError();

				// Validate the form submission
				if ( !contactObj._validateSubmission() ) {
					contactObj._displayFormError( "There was an error processing your request" );
					return false;
				}
				
				// Submit the form
				contactObj._submitForm( contactForm.find( 'input[name=iwac_adminajax]' ).val(), contactForm.find( ':input').serialize() );
				
				// Return false so the browser does not submit the form twice
				return false;

			} );

		},

		_validateSubmission: function() {

			var contactForm = this._dom_elem;
			var contactObj = this;
			var validationError = false;
			var firstError = true;

			contactForm.find( ':input' ).each( function( i ) {

				jQuery( this ).removeClass( 'error' );
				jQuery( this ).parent().children( '.ajax-feedback' ).remove();
				
				if ( jQuery( this ).hasClass( 'required-field' ) && ( jQuery( this ).val() == '' || jQuery( this ).val() == null ) ) {
					validationError = true;
					jQuery( this ).addClass( 'error' );
					jQuery( this ).after( '<span class="ajax-feedback error">This field is required</span>' );
					jQuery( this ).parent().children( '.ajax-feedback' ).fadeIn();
				}
				else if ( jQuery( this ).hasClass( 'validate-email' ) && !jQuery( this ).val().match( /^[A-Z0-9._%-]+@[A-Z0-9._%-]+.[A-Z]{2,4}$/i ) ) {
					validationError = true;
					jQuery( this ).addClass( 'error' );
					jQuery( this ).after( '<span class="ajax-feedback error">Please enter a valid email address</span>' );
					jQuery( this ).parent().children( '.ajax-feedback' ).fadeIn();
				}
				
				// Focus on the first error field
				if ( validationError && firstError ) {
					this.focus();
					firstError = false;
				}
				
			} );

			return validationError ? false : true;

		},

		_submitForm: function( submitURL, postData ) {
			var contactForm = this._dom_elem;
			var contactObj = this;
			contactObj._startLoading();
			jQuery.ajax( {
				type: 'POST',
				data: postData,
				url: submitURL,
				dataType: 'text json',
				complete: function() {
					contactObj._stopLoading();
				},
				success: function( json ) {
					if ( json.status == 'success' ) {
						contactForm.find( '.buttons .ajax-result' ).html( "Your message has been sent successfully!" );
						contactForm.find( '.buttons .ajax-result' ).fadeIn();
						contactForm.find( ':input' ).attr( 'disabled', 'disabled' );
						contactForm.find( '.buttons input[name=iwac_submit]' ).addClass( 'disabled' );
						if ( json.redirect != null ) window.location = json.redirect;
					}
					else if ( json.status == 'fail' ) {
						contactObj._displayFormError( json.reason );
						// Reload ReCAPTCHA if it's attached to this form
						if ( contactForm.find( 'table.recaptchatable' ).length > 0 )
							Recaptcha.reload();
					}
					else console.log( json );
				}
			} );
		},

		_displayFormError: function( message ) {
			this._dom_elem.find( '.buttons .ajax-result' ).addClass( 'error' );
			this._dom_elem.find( '.buttons .ajax-result' ).html( message );
			this._dom_elem.find( '.buttons .ajax-result' ).fadeIn();
		},

		_clearFormError: function() {
			this._dom_elem.find( '.buttons .ajax-result' ).hide();
			this._dom_elem.find( '.buttons .ajax-result' ).removeClass( 'error' );
		},

		_startLoading: function() {
			this._dom_elem.find( '.ajax-loading' ).show();
		},

		_stopLoading: function() {
			this._dom_elem.find( '.ajax-loading' ).hide();
		}

	}
}