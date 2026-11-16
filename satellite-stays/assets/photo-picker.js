/**
 * Satellite Stays: "Choose photo" button on the Accommodation screen.
 * Opens the WordPress Media Library and stores the chosen photo's file name.
 */
( function ( $ ) {
	'use strict';
	$( function () {
		var $wrap = $( '.ss-photo-field' );
		if ( ! $wrap.length || ! window.wp || ! wp.media ) {
			return;
		}
		var $input = $wrap.find( 'input[name="image"]' );
		var $preview = $wrap.find( '.ss-photo-preview' );
		var $img = $preview.find( 'img' );
		var $choose = $wrap.find( '.ss-photo-choose' );
		var $remove = $wrap.find( '.ss-photo-remove' );
		var frame;

		$choose.on( 'click', function ( e ) {
			e.preventDefault();
			if ( ! frame ) {
				frame = wp.media( {
					title: 'Choose a photo for this accommodation',
					button: { text: 'Use this photo' },
					library: { type: 'image' },
					multiple: false
				} );
				frame.on( 'select', function () {
					var a = frame.state().get( 'selection' ).first().toJSON();
					var src = ( a.sizes && a.sizes.medium ) ? a.sizes.medium.url : a.url;
					$input.val( a.filename );
					$img.attr( 'src', src );
					$preview.prop( 'hidden', false );
					$remove.prop( 'hidden', false );
					$choose.text( 'Change photo' );
				} );
			}
			frame.open();
		} );

		$remove.on( 'click', function ( e ) {
			e.preventDefault();
			$input.val( '' );
			$img.attr( 'src', '' );
			$preview.prop( 'hidden', true );
			$remove.prop( 'hidden', true );
			$choose.text( 'Choose photo' );
		} );
	} );
}( jQuery ) );
