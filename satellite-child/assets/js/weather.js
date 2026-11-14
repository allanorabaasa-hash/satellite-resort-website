/**
 * Satellite Holiday Resort — "Kabale right now" weather badge.
 * Uses the free Open-Meteo API (no key, no cookies). If it can't load,
 * the badge simply stays hidden.
 */
( function () {
	'use strict';

	// WMO weather codes → plain words.
	var WORDS = {
		0: 'clear sky', 1: 'mostly clear', 2: 'partly cloudy', 3: 'cloudy',
		45: 'misty', 48: 'misty',
		51: 'light drizzle', 53: 'drizzle', 55: 'heavy drizzle',
		61: 'light rain', 63: 'rain', 65: 'heavy rain',
		80: 'rain showers', 81: 'rain showers', 82: 'heavy showers',
		95: 'thunderstorms', 96: 'thunderstorms', 99: 'thunderstorms'
	};

	function boot() {
		var el = document.querySelector( '.sat-weather[data-lat]' );
		if ( ! el || ! window.fetch ) {
			return;
		}
		var url = 'https://api.open-meteo.com/v1/forecast?latitude=' + encodeURIComponent( el.getAttribute( 'data-lat' ) ) +
			'&longitude=' + encodeURIComponent( el.getAttribute( 'data-lon' ) ) +
			'&current=temperature_2m,weather_code,is_day&timezone=Africa%2FKampala';
		fetch( url )
			.then( function ( r ) { if ( ! r.ok ) { throw new Error(); } return r.json(); } )
			.then( function ( d ) {
				var c = d.current;
				if ( ! c || typeof c.temperature_2m !== 'number' ) {
					return;
				}
				var words = WORDS[ c.weather_code ] || '';
				el.querySelector( '.sat-weather-text' ).textContent = 'Kabale right now: ' + Math.round( c.temperature_2m ) + '°C' + ( words ? ', ' + words : '' );
				el.classList.toggle( 'is-night', c.is_day === 0 );
				el.hidden = false;
			} )
			.catch( function () {} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}
}() );
