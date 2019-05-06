/**
 * Admin Settings Js.
 *
 * @package WP Intranet Security
 */

(function ($) {
	'use strict';

	jQuery( document ).ready(
		function () {

			jQuery('.select2-dropdown').select2({
				width: '400px'
			});

			jQuery( '#add-new-wpis-form-button' ).click(
				function () {
					jQuery( '#new-wpis-form' ).show();
					jQuery( '#update-wpis-form' ).hide();
				}
			);

			jQuery( '#cancel-new-login-form' ).click(
				function () {
					jQuery( '#new-wpis-form' ).hide();
					jQuery( '#update-wpis-form' ).show();
				}
			);

			jQuery( '#cancel-update-login-form' ).click(
				function () {
					jQuery( '#update-wpis-form' ).hide();
				}
			);

			if (jQuery( '.wpis-click-to-copy-btn' ).get( 0 )) {

				var clipboard = new Clipboard( '.wpis-click-to-copy-btn' );

				clipboard.on(
					'success', function (e) {
						var elem      = e.trigger;
						var className = elem.getAttribute( 'class' );
						jQuery( '#copied-text-message-' + className ).text( 'Copied' ).fadeIn();
						jQuery( '#copied-text-message-' + className ).fadeOut( 'slow' );
					}
				);
			}

			if (jQuery( '.wpis-copy-to-clipboard' ).get( 0 )) {
				var clipboard_link = new Clipboard( '.wpis-copy-to-clipboard' );

				clipboard_link.on(
					'success', function (e) {
						var elem = e.trigger;
						var id   = elem.getAttribute( 'id' );
						jQuery( '#copied-' + id ).text( 'Copied' ).fadeIn();
						jQuery( '#copied-' + id ).fadeOut( 'slow' );
					}
				);
			}

			jQuery( '#new-user-expiry-time' ).change(
				function () {
					var value = jQuery( this ).val();
					showDatePicker( value, 'new' );
				}
			);

			jQuery( '#update-user-expiry-time' ).change(
				function () {
					var value = jQuery( this ).val();
					showDatePicker( value, 'update' );
				}
			);

			jQuery( 'a.tlwp-rating-link' ).click(
				function () {
					jQuery.post( data.admin_ajax_url, {action: 'tlwp_rated'} );
					jQuery( this ).parent().text( jQuery( this ).data( 'rated' ) );
				}
			);

			jQuery( 'a.tlwp-rating-link-header' ).click(
				function () {
					jQuery.post( data.admin_ajax_url, {action: 'tlwp_reivew_header'} );
					jQuery( this ).parent().text( jQuery( this ).data( 'rated' ) );
				}
			);

			function showDatePicker(value, datePickerClass) {

				var customDatePickerClass = '';
				var customDatePickerID    = '';
				if ( 'new' === datePickerClass ) {
					customDatePickerClass = '.new-custom-date-picker';
					customDatePickerID    = '#new-custom-date-picker';
				} else {
					customDatePickerClass = '.update-custom-date-picker';
					customDatePickerID    = '#update-custom-date-picker';
				}

				if (value === 'custom_date') {
					var tomorrowDate = new Date( new Date().getTime() + 24 * 60 * 60 * 1000 );
					jQuery( customDatePickerClass ).datepicker(
						{
							dateFormat: 'yy-mm-dd',
							minDate: tomorrowDate
						}
					);
					jQuery( customDatePickerID ).show();
				} else {
					jQuery( customDatePickerID ).hide();
				}
			}

		}
	);

})( jQuery );
