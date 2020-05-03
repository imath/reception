/**
 * WordPress dependencies.
 */
const { registerBlockType } = wp.blocks;
const { createElement } = wp.element;
const { __ } = wp.i18n;

registerBlockType( 'reception/member-bio', {
	title: __( 'Présentation du membre', 'reception' ),

	description: __( 'Ce bloc permet aux membres de votre communauté de partager leurs informations de présentation ou biographiques.', 'reception' ),

	icon: 'id-alt',

	category: 'widgets',

	attributes: {
		userID: {
			type: 'integer',
			default: 0,
		},
	},

	edit: function( { attributes, setAttributes } ) {
		return(
			<p>{ __( 'Présentation du membre.', 'reception' ) }</p>
		)
	},
} );
