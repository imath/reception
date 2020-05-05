/**
 * WordPress dependencies.
 */
const { registerBlockType } = wp.blocks;
const { createElement, Fragment } = wp.element;
const { Disabled, PanelBody, TextControl } = wp.components;
const { InspectorControls } = wp.blockEditor;
const { ServerSideRender } = wp.editor;
const { __ } = wp.i18n;

registerBlockType( 'reception/member-bio', {
	title: __( 'Présentation du membre', 'reception' ),

	description: __( 'Ce bloc permet aux membres de votre communauté de partager leurs informations de présentation ou biographiques.', 'reception' ),

	supports: {
		className: false,
		anchor: false,
		multiple: false,
		reusable: false,
	},

	icon: 'id-alt',

	category: 'widgets',

	attributes: {
		blockTitle: {
			type: 'string',
			default: __( 'À propos', 'reception' ),
		},
	},

	edit: function( { attributes, setAttributes } ) {
		const { blockTitle } = attributes;

		return (
			<Fragment>
				<InspectorControls>
					<PanelBody title={ __( 'Réglages', 'reception' ) } initialOpen={ true }>
						<TextControl
							label={ __( 'Titre du bloc', 'reception' ) }
							value={ blockTitle }
							onChange={ ( text ) => {
								setAttributes( { blockTitle: text } );
							} }
							help={ __( 'Pour masquer le titre du bloc, laisser ce champ vide.', 'reception' ) }
						/>
					</PanelBody>
				</InspectorControls>
				<Disabled>
					<ServerSideRender block="reception/member-bio" attributes={ attributes } />
				</Disabled>
			</Fragment>
		);
	},
} );
