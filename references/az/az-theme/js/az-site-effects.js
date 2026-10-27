( function () {
  'use strict';

  function init() {
    var preloader = document.querySelector( '[data-az-preloader]' );
    var backToTop = document.querySelector( '[data-az-back-to-top]' );

    if ( preloader ) {
      var closePreloader = function () {
        preloader.classList.add( 'is-loaded' );
        window.setTimeout( function () { preloader.remove(); }, 260 );
      };

      if ( document.readyState === 'complete' ) {
        closePreloader();
      } else {
        window.addEventListener( 'load', closePreloader, { once: true } );
      }
    }

    if ( ! backToTop ) {
      return;
    }

    function updateVisibility() {
      var root = document.scrollingElement || document.documentElement;
      var scrollTop = window.scrollY || window.pageYOffset || ( root && root.scrollTop ) || 0;
      // A short page should also expose the control when the visitor reaches its footer.
      var maxScroll = root ? Math.max( 0, root.scrollHeight - root.clientHeight ) : 0;
      var threshold = maxScroll > 0 ? Math.min( 360, maxScroll / 2 ) : 360;
      backToTop.classList.toggle( 'is-visible', scrollTop > threshold );
    }

    backToTop.addEventListener( 'click', function ( event ) {
      event.preventDefault();
      var reduceMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
      window.scrollTo( { top: 0, behavior: reduceMotion ? 'auto' : 'smooth' } );
    } );
    updateVisibility();
    // Until initialization, the native #page link stays visible and usable.
    backToTop.classList.add( 'is-enhanced' );
    window.addEventListener( 'scroll', updateVisibility, { passive: true } );
    window.addEventListener( 'resize', updateVisibility );
    window.addEventListener( 'pageshow', updateVisibility );
  }

  if ( document.readyState === 'loading' ) {
    document.addEventListener( 'DOMContentLoaded', init, { once: true } );
  } else {
    init();
  }
}() );
