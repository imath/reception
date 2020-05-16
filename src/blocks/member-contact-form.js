/**
 * WordPress dependencies.
 */
const { registerBlockType } = wp.blocks;
const { createElement, Fragment } = wp.element;
const { Disabled, PanelBody, TextControl } = wp.components;
const { InspectorControls } = wp.blockEditor;
const { ServerSideRender } = wp.editor;
const { __ } = wp.i18n;

registerBlockType( 'reception/member-contact-form', {
	title: __( 'Formulaire de contact du membre', 'reception' ),

	description: __( 'Ce bloc permet aux visiteurs de votre site de contacter un membre de votre communauté.', 'reception' ),

	supports: {
		className: false,
		anchor: false,
		multiple: false,
		reusable: false,
	},

	icon: 'email-alt',

	category: 'widgets',

	attributes: {
		blockTitle: {
			type: 'string',
			default: __( 'Contacter ce membre', 'reception' ),
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
					<ServerSideRender block="reception/member-contact-form" attributes={ attributes } />
				</Disabled>
			</Fragment>
		);
	},
} );
