/**
 * navigation.js
 *
 * Handles toggling the navigation menu for small screens.
 */
( function() {
	var container, button, menu;

	container = document.getElementById( 'site-navigation' );
	if ( !container ) {
		return;
	}

	button = container.getElementsByClassName( 'menu-toggle' )[ 0 ];
	if ( 'undefined' === typeof button ) {
		return;
	}

	menu = container.getElementsByTagName( 'ul' )[ 0 ];

	// Hide menu toggle button if menu is empty and return early.
	if ( 'undefined' === typeof menu ) {
		button.style.display = 'none';
		return;
	}

	if ( -1 === menu.className.indexOf( 'nav-menu' ) ) {
		menu.className += ' nav-menu';
	}

	button.onclick = function() {
		if ( -1 !== container.className.indexOf( 'main-small-navigation' ) ) {
			container.className = container.className.replace( 'main-small-navigation', 'main-navigation' );
		} else {
			container.className = container.className.replace( 'main-navigation', 'main-small-navigation' );
		}
	};
} )();

// Show Submenu on click on touch enabled deviced
( function() {
	var container;
	container = document.getElementById( 'site-navigation' );

	/**
	 * Toggles `focus` class to allow submenu access on tablets.
	 */
	( function( container ) {
		var touchStartFn, i,
		    parentLink = container.querySelectorAll( '.menu-item-has-children > a, .page_item_has_children > a' );

		if ( ( 'ontouchstart' in window ) && ( window.matchMedia( "( min-width: 768px ) " ).matches ) ) {
			touchStartFn = function( e ) {
				var menuItem = this.parentNode, i;

				if ( !menuItem.classList.contains( 'focus' ) ) {
					e.preventDefault();
					for ( i = 0; i < menuItem.parentNode.children.length; ++i ) {
						if ( menuItem === menuItem.parentNode.children[ i ] ) {
							continue;
						}
						menuItem.parentNode.children[ i ].classList.remove( 'focus' );
					}
					menuItem.classList.add( 'focus' );
				} else {
					menuItem.classList.remove( 'focus' );
				}
			};

			for ( i = 0; i < parentLink.length; ++i ) {
				parentLink[ i ].addEventListener( 'touchstart', touchStartFn, false );
			}
		}
	}( container ) );
} )();

/**
 * Fix: Menu out of view port
 */
( function() {

	var subMenu;

	jQuery( '.main-navigation ul li.menu-item-has-children a, .main-navigation ul li.page_item_has_children a' ).on( {

		'mouseover touchstart': function() {

			function isElementInViewport( subMenu ) {

				if ( 'function' === typeof jQuery && subMenu instanceof jQuery ) {
					subMenu = subMenu[ 0 ];
				}

				// In case browser doesn't support getBoundingClientRect function.
				if ( 'function' === typeof subMenu.getBoundingClientRect ) {

					var rect = subMenu.getBoundingClientRect();

					if ( rect.right + 2 > ( window.innerWidth || document.documentElement.clientWidth ) ) {
						return 'spacious-menu--left'; // menu goes out of viewport from right.
					} else if ( rect.left < 0 ) {
						return 'spacious-menu--right'; // menu goes out of viewport from left.
					} else {
						return false;
					}
				}
			}

			subMenu = jQuery( this ).next( '.sub-menu, .children' );

			// If menu item has submenu
			if ( subMenu.length > 0 ) {

				var viewportClass = isElementInViewport( subMenu );

				if ( false !== viewportClass ) {
					subMenu.addClass( viewportClass );
				}
			}

		}

	} );

} )();
