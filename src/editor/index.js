const { unregisterPlugin, registerPlugin } = wp.plugins;
const { PluginMoreMenuItem } = wp.editPost;
const { select, dispatch } = wp.data;
const { __ } = wp.i18n;

unregisterPlugin( 'edit-post' );

const ReceptionMoreMenuItem = () => {
	const isFullScreenActive = select( 'core/edit-post' ).isFeatureActive( 'fullscreenMode' );

	// Force the Full Screen display.
	if ( ! isFullScreenActive ) {
		dispatch( 'core/edit-post' ).toggleFeature( 'fullscreenMode' );
	}

	return (
		<PluginMoreMenuItem
			icon="buddicons-buddypress-logo"
			href={ window.receptionEditor.buddyPressOptionsUrl }
		>
			{ __( 'Options de BuddyPress', 'reception' ) }
		</PluginMoreMenuItem>
	);
}

registerPlugin( 'reception-more-menu-item', { render: ReceptionMoreMenuItem } );
