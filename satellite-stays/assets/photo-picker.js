/**
 * Satellite Stays: choose an accommodation photo from the Media Library.
 */
( function ( $ ) {
	'use strict';
	$( function () {
		var $field = $( '.ss-photo-field' );
		if ( ! $field.length || ! window.wp || ! wp.media ) {
			return;
		}
		var $input = $field.find( 'input[name="image"]' );
		var $preview = $field.find( '.ss-photo-preview' );
		var $choose = $field.find( '.ss-photo-choose' );
		var $remove = $field.find( '.ss-photo-remove' );
		var frame;

		$choose.on( 'click', function ( e ) {
			e.preventDefault();
			if ( ! frame ) {
				frame = wp.media( {
					title: 'Choose a photo',
					button: { text: 'Use this photo' },
					library: { type: 'image' },
					multiple: false
				} );
				frame.on( 'select', function () {
					var a = frame.state().get( 'selection' ).first().toJSON();
					var file = ( a.url || '' ).split( '/' ).pop();
					var thumb = a.sizes && ( a.sizes.medium || a.sizes.large || a.sizes.full );
					$input.val( file );
					$preview.prop( 'hidden', false ).find( 'img' ).attr( 'src', thumb ? thumb.url : a.url );
					$choose.text( 'Change photo' );
					$remove.prop( 'hidden', false );
				} );
			}
			frame.open();
		} );

		$remove.on( 'click', function ( e ) {
			e.preventDefault();
			$input.val( '' );
			$preview.prop( 'hidden', true ).find( 'img' ).attr( 'src', '' );
			$choose.text( 'Choose photo' );
			$remove.prop( 'hidden', true );
		} );
	} );
}( jQuery ) );
