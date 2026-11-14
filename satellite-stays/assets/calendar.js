/**
 * Satellite Stays — guest availability calendar.
 *
 * Shows two months for the chosen accommodation. Nights with no free room
 * are greyed out. Guests tap their arrival day, then their departure day;
 * the form's check-in/check-out fields, the night count and the price
 * estimate update. The server re-checks everything when the form is sent.
 */
( function () {
	'use strict';

	var MONTHS = [ 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' ];
	var DAYS = [ 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun' ];
	var LONG_DAYS = [ 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' ];

	function ymd( d ) {
		return d.getFullYear() + '-' + String( d.getMonth() + 1 ).padStart( 2, '0' ) + '-' + String( d.getDate() ).padStart( 2, '0' );
	}
	function parse( s ) {
		var p = s.split( '-' );
		return new Date( +p[ 0 ], +p[ 1 ] - 1, +p[ 2 ], 12 );
	}
	function addDays( s, n ) {
		var d = parse( s );
		d.setDate( d.getDate() + n );
		return ymd( d );
	}
	function nightsBetween( a, b ) {
		return Math.round( ( parse( b ) - parse( a ) ) / 86400000 );
	}
	function nice( s ) {
		var d = parse( s );
		return LONG_DAYS[ d.getDay() ].slice( 0, 3 ) + ' ' + d.getDate() + ' ' + MONTHS[ d.getMonth() ].slice( 0, 3 );
	}
	function money( n ) {
		return 'UGX ' + Math.round( n ).toString().replace( /\B(?=(\d{3})+(?!\d))/g, ',' );
	}

	function Calendar( root ) {
		this.root = root;
		this.form = root.closest( 'form' );
		this.endpoint = root.getAttribute( 'data-endpoint' );
		this.type = this.form.querySelector( '[name="type_id"]' );
		this.inEl = this.form.querySelector( '[name="check_in"]' );
		this.outEl = this.form.querySelector( '[name="check_out"]' );
		this.data = null;
		this.free = {};
		this.start = null;
		this.end = null;
		this.month = null;

		var self = this;
		this.type.addEventListener( 'change', function () { self.load(); } );
		[ this.inEl, this.outEl ].forEach( function ( el ) {
			el.addEventListener( 'change', function () { self.syncFromInputs(); } );
		} );
		this.load();
	}

	Calendar.prototype.load = function () {
		var self = this;
		var id = this.type.value;
		if ( ! id ) {
			this.root.hidden = true;
			return;
		}
		this.root.hidden = false;
		this.root.innerHTML = '<p class="ss-cal-loading">Loading availability…</p>';
		fetch( this.endpoint + '?type=' + encodeURIComponent( id ) + '&days=93', { credentials: 'same-origin' } )
			.then( function ( r ) { if ( ! r.ok ) { throw new Error( r.status ); } return r.json(); } )
			.then( function ( data ) {
				self.data = data;
				self.free = {};
				data.nights.forEach( function ( n ) { self.free[ n.date ] = n.free; } );
				var first = parse( data.today );
				self.month = new Date( first.getFullYear(), first.getMonth(), 1, 12 );
				self.syncFromInputs();
			} )
			.catch( function () {
				self.root.innerHTML = '<p class="ss-cal-loading">Availability could not be loaded. You can still type your dates above.</p>';
			} );
	};

	Calendar.prototype.syncFromInputs = function () {
		this.start = this.inEl.value || null;
		this.end = this.outEl.value && this.start && this.outEl.value > this.start ? this.outEl.value : null;
		if ( this.start && this.data ) {
			var s = parse( this.start );
			var first = parse( this.data.today );
			if ( s >= first ) {
				this.month = new Date( s.getFullYear(), s.getMonth(), 1, 12 );
			}
		}
		this.render();
	};

	Calendar.prototype.isFree = function ( day ) {
		return ( this.free[ day ] || 0 ) > 0;
	};

	Calendar.prototype.rangeFree = function ( a, b ) {
		for ( var d = a; d < b; d = addDays( d, 1 ) ) {
			if ( ! ( d in this.free ) || ! this.isFree( d ) ) {
				return false;
			}
		}
		return true;
	};

	Calendar.prototype.pick = function ( day ) {
		if ( ! this.start || this.end || day <= this.start ) {
			if ( ! this.isFree( day ) ) {
				return;
			}
			this.start = day;
			this.end = null;
		} else {
			if ( ! this.rangeFree( this.start, day ) ) {
				// The stay would cross a fully booked night: start a new stay from this day instead.
				if ( this.isFree( day ) ) {
					this.start = day;
					this.end = null;
					this.message = 'Those dates cross a fully booked night, so we started a new stay from ' + nice( day ) + '. Now tap your departure day.';
					this.inEl.value = this.start;
					this.outEl.value = '';
				} else {
					this.message = 'Some nights in that range are fully booked. Choose a shorter stay or other dates.';
				}
				this.render();
				return;
			}
			if ( this.data.max_nights && nightsBetween( this.start, day ) > this.data.max_nights ) {
				this.message = 'Online bookings are for up to ' + this.data.max_nights + ' nights.';
				this.render();
				return;
			}
			this.end = day;
		}
		this.message = '';
		this.inEl.value = this.start;
		this.outEl.value = this.end || '';
		[ this.inEl, this.outEl ].forEach( function ( el ) {
			el.dispatchEvent( new Event( 'input', { bubbles: true } ) );
		} );
		this.render();
	};

	Calendar.prototype.monthGrid = function ( m ) {
		var self = this;
		var year = m.getFullYear();
		var month = m.getMonth();
		var first = new Date( year, month, 1, 12 );
		var offset = ( first.getDay() + 6 ) % 7;
		var count = new Date( year, month + 1, 0 ).getDate();
		var today = this.data.today;
		var html = '<div class="ss-cal-month"><p class="ss-cal-title">' + MONTHS[ month ] + ' ' + year + '</p><div class="ss-cal-grid" role="grid">';
		DAYS.forEach( function ( d ) { html += '<span class="ss-cal-dow" aria-hidden="true">' + d.charAt( 0 ) + '</span>'; } );
		for ( var i = 0; i < offset; i++ ) {
			html += '<span></span>';
		}
		for ( var day = 1; day <= count; day++ ) {
			var key = ymd( new Date( year, month, day, 12 ) );
			var known = key in self.free;
			var past = key < today;
			var full = known && ! self.isFree( key );
			var cls = [ 'ss-cal-day' ];
			var inRange = self.start && self.end && key > self.start && key < self.end;
			var canEnd = self.start && ! self.end && key > self.start;
			if ( key === self.start ) { cls.push( 'is-start' ); }
			if ( key === self.end ) { cls.push( 'is-end' ); }
			if ( inRange ) { cls.push( 'is-range' ); }
			if ( full ) { cls.push( 'is-full' ); }
			var disabled = past || ! known || ( full && ! canEnd );
			var label = LONG_DAYS[ parse( key ).getDay() ] + ' ' + day + ' ' + MONTHS[ month ] + ( past ? '' : full ? ', fully booked' : known ? ', available' : '' );
			html += '<button type="button" class="' + cls.join( ' ' ) + '" data-day="' + key + '"' + ( disabled ? ' disabled' : '' ) +
				' aria-label="' + label + '"' + ( key === self.start || key === self.end ? ' aria-pressed="true"' : '' ) + '>' + day + '</button>';
		}
		return html + '</div></div>';
	};

	Calendar.prototype.render = function () {
		if ( ! this.data ) {
			return;
		}
		var self = this;
		var second = new Date( this.month.getFullYear(), this.month.getMonth() + 1, 1, 12 );
		var firstMonth = parse( this.data.today );
		var lastKey = this.data.nights[ this.data.nights.length - 1 ].date;
		var canPrev = this.month > new Date( firstMonth.getFullYear(), firstMonth.getMonth(), 1, 12 );
		var canNext = ymd( new Date( second.getFullYear(), second.getMonth() + 1, 1, 12 ) ) <= lastKey;

		var summary;
		if ( this.start && this.end ) {
			var n = nightsBetween( this.start, this.end );
			summary = '<strong>' + nice( this.start ) + ' to ' + nice( this.end ) + '</strong>, ' + n + ( n === 1 ? ' night' : ' nights' ) +
				( this.data.price ? ', about <strong>' + money( this.data.price * n ) + '</strong>' : ', price confirmed by the resort' );
		} else if ( this.start ) {
			summary = 'Arrival <strong>' + nice( this.start ) + '</strong>. Now tap your departure day.';
		} else {
			summary = 'Tap your arrival day.';
		}

		this.root.innerHTML =
			'<div class="ss-cal-head"><p class="ss-cal-summary" aria-live="polite">' + summary + '</p>' +
			'<div class="ss-cal-nav"><button type="button" class="ss-cal-prev" aria-label="Previous month"' + ( canPrev ? '' : ' disabled' ) + '>‹</button>' +
			'<button type="button" class="ss-cal-next" aria-label="Next month"' + ( canNext ? '' : ' disabled' ) + '>›</button></div></div>' +
			( this.message ? '<p class="ss-cal-msg" role="alert">' + this.message + '</p>' : '' ) +
			'<div class="ss-cal-months">' + this.monthGrid( this.month ) + this.monthGrid( second ) + '</div>' +
			'<p class="ss-cal-key"><span class="ss-cal-swatch is-free"></span> Available <span class="ss-cal-swatch is-full"></span> Fully booked <span class="ss-cal-swatch is-pick"></span> Your stay</p>';

		this.root.querySelectorAll( '.ss-cal-day' ).forEach( function ( b ) {
			b.addEventListener( 'click', function () { self.pick( b.getAttribute( 'data-day' ) ); } );
		} );
		var prev = this.root.querySelector( '.ss-cal-prev' );
		var next = this.root.querySelector( '.ss-cal-next' );
		prev.addEventListener( 'click', function () { self.month = new Date( self.month.getFullYear(), self.month.getMonth() - 1, 1, 12 ); self.render(); } );
		next.addEventListener( 'click', function () { self.month = new Date( self.month.getFullYear(), self.month.getMonth() + 1, 1, 12 ); self.render(); } );
	};

	function boot() {
		if ( ! window.fetch ) {
			return;
		}
		document.querySelectorAll( '[data-ss-cal]' ).forEach( function ( el ) { new Calendar( el ); } );
	}
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}
}() );
