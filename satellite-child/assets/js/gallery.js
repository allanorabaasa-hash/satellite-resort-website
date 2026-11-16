/**
 * Satellite Holiday Resort — sliding gallery.
 *
 * Turns each ".sat-reel" (a row of image blocks) into a gallery that slides on
 * its own, with previous/next arrows, swipe and keyboard support, and a
 * full-screen view when a photo is clicked. Autoplay pauses while the visitor
 * hovers, touches or tabs into it, and is off for people who ask their device
 * to reduce motion.
 */
( function () {
	'use strict';

	var INTERVAL = 4500;
	var reduceMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	function button( cls, label, path ) {
		var b = document.createElement( 'button' );
		b.type = 'button';
		b.className = cls;
		b.setAttribute( 'aria-label', label );
		b.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="' + path + '"/></svg>';
		return b;
	}
	var LEFT = 'M15.4 4.6 8 12l7.4 7.4-1.4 1.4L5.2 12l8.8-8.8z';
	var RIGHT = 'M8.6 19.4 16 12 8.6 4.6 10 3.2l8.8 8.8L10 20.8z';

	/* ---------- full-screen view, shared by every reel ---------- */
	var box, boxImg, boxCap, boxItems = [], boxIndex = 0, lastFocus = null;

	function buildBox() {
		box = document.createElement( 'div' );
		box.className = 'sat-lightbox';
		box.setAttribute( 'role', 'dialog' );
		box.setAttribute( 'aria-modal', 'true' );
		box.setAttribute( 'aria-label', 'Photo viewer' );
		box.hidden = true;
		box.innerHTML = '<figure><img alt=""><figcaption></figcaption></figure>';
		boxImg = box.querySelector( 'img' );
		boxCap = box.querySelector( 'figcaption' );

		var close = button( 'sat-lightbox-close', 'Close', 'M18.3 5.7 12 12l6.3 6.3-1.4 1.4L10.6 13.4 4.3 19.7l-1.4-1.4L9.2 12 2.9 5.7l1.4-1.4 6.3 6.3 6.3-6.3z' );
		var prev = button( 'sat-lightbox-prev', 'Previous photo', LEFT );
		var next = button( 'sat-lightbox-next', 'Next photo', RIGHT );
		close.addEventListener( 'click', closeBox );
		prev.addEventListener( 'click', function () { openBox( boxItems, boxIndex - 1 ); } );
		next.addEventListener( 'click', function () { openBox( boxItems, boxIndex + 1 ); } );
		box.append( close, prev, next );

		box.addEventListener( 'click', function ( e ) {
			if ( e.target === box ) {
				closeBox();
			}
		} );
		document.addEventListener( 'keydown', function ( e ) {
			if ( box.hidden ) {
				return;
			}
			if ( 'Escape' === e.key ) {
				closeBox();
			} else if ( 'ArrowLeft' === e.key ) {
				openBox( boxItems, boxIndex - 1 );
			} else if ( 'ArrowRight' === e.key ) {
				openBox( boxItems, boxIndex + 1 );
			} else if ( 'Tab' === e.key ) {
				// keep focus inside the viewer
				var f = box.querySelectorAll( 'button' );
				if ( e.shiftKey && document.activeElement === f[ 0 ] ) {
					e.preventDefault();
					f[ f.length - 1 ].focus();
				} else if ( ! e.shiftKey && document.activeElement === f[ f.length - 1 ] ) {
					e.preventDefault();
					f[ 0 ].focus();
				}
			}
		} );
		document.body.appendChild( box );
	}

	function openBox( items, n ) {
		if ( ! box ) {
			buildBox();
		}
		boxItems = items;
		boxIndex = ( n + items.length ) % items.length;
		var img = items[ boxIndex ].querySelector( 'img' );
		var cap = items[ boxIndex ].querySelector( 'figcaption' );
		boxImg.src = img.currentSrc || img.src;
		boxImg.alt = img.alt;
		boxCap.textContent = cap ? cap.textContent : '';
		if ( box.hidden ) {
			lastFocus = document.activeElement;
			box.hidden = false;
			document.documentElement.classList.add( 'sat-lightbox-open' );
			box.querySelector( '.sat-lightbox-close' ).focus();
		}
	}

	function closeBox() {
		box.hidden = true;
		document.documentElement.classList.remove( 'sat-lightbox-open' );
		if ( lastFocus ) {
			lastFocus.focus();
		}
	}

	/* ---------- the sliding reel ---------- */
	function init( reel ) {
		var items = Array.prototype.slice.call( reel.querySelectorAll( '.sat-reel-item' ) );
		if ( ! items.length ) {
			return;
		}
		reel.setAttribute( 'role', 'region' );
		reel.setAttribute( 'aria-roledescription', 'carousel' );
		reel.setAttribute( 'aria-label', 'Photo gallery' );
		reel.tabIndex = 0;

		items.forEach( function ( item, n ) {
			item.setAttribute( 'role', 'group' );
			item.setAttribute( 'aria-roledescription', 'slide' );
			item.setAttribute( 'aria-label', ( n + 1 ) + ' of ' + items.length );
			var img = item.querySelector( 'img' );
			img.tabIndex = 0;
			img.setAttribute( 'role', 'button' );
			img.setAttribute( 'aria-label', 'Open photo: ' + img.alt );
			img.addEventListener( 'click', function () { openBox( items, n ); } );
			img.addEventListener( 'keydown', function ( e ) {
				if ( 'Enter' === e.key || ' ' === e.key ) {
					e.preventDefault();
					openBox( items, n );
				}
			} );
		} );

		var controls = document.createElement( 'div' );
		controls.className = 'sat-reel-controls';
		var prev = button( 'sat-reel-prev', 'Previous photos', LEFT );
		var next = button( 'sat-reel-next', 'Next photos', RIGHT );
		var bar = document.createElement( 'div' );
		bar.className = 'sat-reel-progress';
		bar.innerHTML = '<span></span>';
		controls.append( prev, bar, next );
		reel.parentNode.insertBefore( controls, reel.nextSibling );

		function step() {
			var first = items[ 0 ].getBoundingClientRect().width;
			var gap = parseFloat( getComputedStyle( reel ).columnGap ) || 0;
			return first + gap;
		}
		function atEnd() {
			return reel.scrollLeft + reel.clientWidth >= reel.scrollWidth - 4;
		}
		function go( dir ) {
			if ( dir > 0 && atEnd() ) {
				reel.scrollTo( { left: 0, behavior: 'smooth' } );
			} else if ( dir < 0 && reel.scrollLeft <= 4 ) {
				reel.scrollTo( { left: reel.scrollWidth, behavior: 'smooth' } );
			} else {
				reel.scrollBy( { left: dir * step(), behavior: 'smooth' } );
			}
		}
		prev.addEventListener( 'click', function () { go( -1 ); restart(); } );
		next.addEventListener( 'click', function () { go( 1 ); restart(); } );
		reel.addEventListener( 'keydown', function ( e ) {
			if ( e.target !== reel ) {
				return;
			}
			if ( 'ArrowRight' === e.key ) {
				e.preventDefault();
				go( 1 );
			} else if ( 'ArrowLeft' === e.key ) {
				e.preventDefault();
				go( -1 );
			}
		} );

		var fill = bar.firstChild;
		function progress() {
			var max = reel.scrollWidth - reel.clientWidth;
			var visible = reel.clientWidth / reel.scrollWidth;
			fill.style.width = ( visible * 100 ) + '%';
			fill.style.transform = 'translateX(' + ( max > 0 ? ( reel.scrollLeft / max ) * ( ( 1 - visible ) / visible ) * 100 : 0 ) + '%)';
		}
		reel.addEventListener( 'scroll', progress, { passive: true } );
		window.addEventListener( 'resize', progress );
		progress();

		// autoplay, paused while someone is looking closely
		var timer = null;
		var hold = false;
		function start() {
			if ( reduceMotion || timer ) {
				return;
			}
			timer = setInterval( function () {
				if ( ! hold && box_closed() && ! document.hidden ) {
					go( 1 );
				}
			}, INTERVAL );
		}
		function restart() {
			clearInterval( timer );
			timer = null;
			start();
		}
		function box_closed() {
			return ! box || box.hidden;
		}
		[ 'mouseenter', 'focusin', 'touchstart' ].forEach( function ( ev ) {
			reel.addEventListener( ev, function () { hold = true; }, { passive: true } );
		} );
		[ 'mouseleave', 'focusout' ].forEach( function ( ev ) {
			reel.addEventListener( ev, function () { hold = false; } );
		} );
		reel.addEventListener( 'touchend', function () {
			setTimeout( function () { hold = false; }, 6000 );
		}, { passive: true } );

		// only run while the gallery is on screen
		if ( 'IntersectionObserver' in window ) {
			new IntersectionObserver( function ( entries ) {
				if ( entries[ 0 ].isIntersecting ) {
					start();
				} else {
					clearInterval( timer );
					timer = null;
				}
			} ).observe( reel );
		} else {
			start();
		}
		reel.classList.add( 'is-ready' );
	}

	function boot() {
		Array.prototype.forEach.call( document.querySelectorAll( '.sat-reel' ), init );
	}
	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}
}() );
