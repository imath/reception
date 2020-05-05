/**
 * WordPress dependencies.
 */
const { Component, render, createElement, Fragment } = wp.element;
const { __ } = wp.i18n;
const { RichText } = wp.blockEditor;
const { Button } = wp.components;
const { apiFetch } = wp;

class MemberBio extends Component {
	constructor() {
		super( ...arguments );

		this.state = {
			description: '',
			loaded: false,
			previousDescription: '',
		};
	}

	componentDidMount() {
		const { loaded } = this.state;

		if ( ! loaded ) {
			apiFetch( {
				path: '/wp/v2/users/me?context=edit',
				method: 'GET'
			} ).then( ( user ) => {
				if ( user && user.description ) {
					const description = '<p>' + user.description + '</p>';
					this.setState( {
						description: description,
						previousDescription: description,
					} );
				}
			} );

			this.setState( { loaded: true } );
		}
	}

	saveDescription( e ) {
		e.preventDefault();

		const { description } = this.state;

		apiFetch( {
			path: '/wp/v2/users/me?context=edit',
			method: 'PUT',
			data: { description: description }
		} ).then( ( user ) => {
			if ( user && user.description ) {
				const description = '<p>' + user.description + '</p>';
				this.setState( {
					previousDescription: description,
				} );
			}
		} );
	}

	render() {
		const { description, previousDescription } = this.state;

		return(
			<Fragment>
				<RichText
					value={ description }
					onChange={ ( text ) => this.setState( { description: text } ) }
					placeholder={ __( 'Utiliser cette zone pour personnaliser votre présentation', 'reception' ) }
					multiline={ true }
				/>

				{ previousDescription !== description && (
					<Button
						isPrimary={ true }
						onClick={ ( e ) => this.saveDescription( e ) }
					>
						{ __( 'Enregistrer', 'reception' ) }
					</Button>
				) }
			</Fragment>
		);
	}
}
render( <MemberBio />, document.querySelector( '.reception-block-member-bio.dynamic' ) );
