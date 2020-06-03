/**
 * WordPress dependencies.
 */
const { Component, render, createElement } = wp.element;
const { __ } = wp.i18n;

class ManageVerifiedEmail extends Component {
	constructor() {
		super( ...arguments );
	}

	render() {
		return ( <p>{ __( 'Table des e-mails vérifiés', 'reception' ) }</p> );
	}
}
render( <ManageVerifiedEmail />, document.querySelector( '#reception-verified-emails-list-table' ) );
