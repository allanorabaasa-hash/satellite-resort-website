/**
 * Satellite Stays — keeps check-out after check-in and shows the number of
 * nights. The server checks the same rules; this only makes the form easier.
 */
( function () {
	'use strict';

	function addDays( ymd, n ) {
		var d = new Date( ymd + 'T12:00:00' );
		d.setDate( d.getDate() + n );
		return d.toISOString().slice( 0, 10 );
	}

	function init( form ) {
		var inEl = form.querySelector( '[name="check_in"]' );
		var outEl = form.querySelector( '[name="check_out"]' );
		var note = form.querySelector( '.ss-nights' );
		if ( ! inEl || ! outEl ) {
			return;
		}

		function update() {
			if ( inEl.value ) {
				var earliest = addDays( inEl.value, 1 );
				outEl.min = earliest;
				if ( ! outEl.value || outEl.value < earliest ) {
					outEl.value = earliest;
				}
			}
			if ( note ) {
				if ( inEl.value && outEl.value && outEl.value > inEl.value ) {
					var nights = Math.round( ( new Date( outEl.value ) - new Date( inEl.value ) ) / 86400000 );
					note.textContent = nights + ( nights === 1 ? ' night' : ' nights' );
				} else {
					note.textContent = '';
				}
			}
		}

		inEl.addEventListener( 'change', update );
		outEl.addEventListener( 'change', update );
		update();
	}

	function boot() {
		document.querySelectorAll( 'form[data-ss-dates]' ).forEach( init );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}
}() );
