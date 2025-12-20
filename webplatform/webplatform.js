( function ( mw, $ ) {
	function setupDropdown() {
		// .links instead of just plain #p-personal to avoid extending the "hitbox" to the right
		// (on LTR interface) since we do NOT want to trigger this when hovering the mouse
		// over the Echo alert icon (or the notice icon, but that wasn't an issue to begin with)
		$( '#p-personal .links' ).on( {
			'mouseover': function () {
				$( '#p-personal .user-dropdown' ).show();
			},
			'mouseout': function () {
				$( '#p-personal .user-dropdown' ).hide();
			}
		} );
	}

	function init() {
		var i, dropdown;

		setupDropdown();

		if ( document.querySelectorAll && document.body.addEventListener ) {
			var dropdowns = document.querySelectorAll( '.dropdown' );

			for ( i = 0; dropdown = dropdowns[i++]; ) {
				dropdown.addEventListener( 'focus', function () {
					this.className += ' focus';
				}, true );

				dropdown.addEventListener( 'blur', function () {
					this.className = this.className.replace( /\s+focus\b/, ' ' );
				}, true );
			}
		}
	}

	init();

}( mediaWiki, jQuery ) );
