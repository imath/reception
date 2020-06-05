/**
 * WordPress dependencies.
 */
const { registerBlockType } = wp.blocks;
const { createElement } = wp.element;
const { Notice } = wp.components;
const { __ } = wp.i18n;

const receptionInfo = __( 'Utilisez cet espace pour personnaliser l’apparence des pages d’accueil de vos membres.', 'reception' )

registerBlockType( 'reception/info', {
	title: __( 'Informations de Réception', 'reception' ),

	description: receptionInfo,

	supports: {
		className: false,
		anchor: false,
		multiple: false,
		reusable: false,
	},

	icon: 'editor-help',

	category: 'widgets',

	edit: function() {
		return(
			<Notice status="info" isDismissible={ false }>
				{ receptionInfo }
			</ Notice>
		)
	},
} );
