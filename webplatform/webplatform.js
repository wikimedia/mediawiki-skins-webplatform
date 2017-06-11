function setupSearchField() {
	var searchInput = $('#searchInput'),
	defaultValue = 'Search...';

	searchInput.focus( function () {
		if ( searchInput.val() == defaultValue ) {
			searchInput.val('');
		}
	} );

	searchInput.blur( function () {
		if ( searchInput.val() === '' ) {
			searchInput.val( defaultValue );
		}
	} );
}


function setupDropdown() {
	$( '#p-personal' ).bind( 'mouseover', function () {
		$( '#p-personal .user-dropdown' ).show();
	} );

	$( '#p-personal' ).bind( 'mouseout', function () {
		$( '#p-personal .user-dropdown' ).hide();
	} );
}

function init() {
	var i;

	setupSearchField();
	setupDropdown();

	if ( document.querySelectorAll && document.body.addEventListener ) {
		var dropdowns = document.querySelectorAll( '.dropdown' );

		for ( i=0, dropdown; dropdown=dropdowns[i++]; ) {
			dropdown.addEventListener( 'focus', function () {
				this.className += ' focus';
			}, true );

			dropdown.addEventListener( 'blur', function () {
				this.className = this.className.replace( /\s+focus\b/, ' ' );
			}, true );
		}
	}
}

$( document ).ready( init );
