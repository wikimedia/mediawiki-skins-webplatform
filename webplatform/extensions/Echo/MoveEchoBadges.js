// This little snippet of JS moves the Echo icons out of the drop-down menu and next to
// the user name on the user name drop-down menu
$( () => {
	$( '#p-personal .links' )
		.after( $( '#pt-notifications-alert' ) )
		.after( $( '#pt-notifications-notice' ) );
} );