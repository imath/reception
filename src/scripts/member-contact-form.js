/**
 * WordPress dependencies.
 */
const { Component, render, createElement, Fragment } = wp.element;
const { __ } = wp.i18n;
const { RichText } = wp.blockEditor;
const { Button, TextControl, Modal } = wp.components;
const { apiFetch } = wp;
const { isEmail } = wp.url;

class MemberContactForm extends Component {
	constructor() {
		super( ...arguments );

		this.state = {
			email: '',
			message: '',
			displayUserId: 0,
			loggedInUserId: 0,
			checked: false,
			verifiedEmail: {},
			isEditorOpen: false,
			feedback: [],
		};

		this.closeEmailEditor = this.closeEmailEditor.bind( this );
		this.isSelfProfile = false;
		this.isUserLoggedIn = false;
	}

	componentDidMount() {
		let { displayUserId, loggedInUserId } = this.state;

		if ( window.receptionMemberContactForm ) {
			if ( window.receptionMemberContactForm.displayUserId ) {
				displayUserId = parseInt( window.receptionMemberContactForm.displayUserId, 10 );
				this.setState( { displayUserId: displayUserId } );
			}

			if ( window.receptionMemberContactForm.loggedInUserId ) {
				loggedInUserId = parseInt( window.receptionMemberContactForm.loggedInUserId, 10 );
				this.setState( { loggedInUserId: loggedInUserId } );
			}
		}

		this.isUserLoggedIn = loggedInUserId && 0 !== loggedInUserId;
		this.isSelfProfile  = this.isUserLoggedIn && displayUserId === loggedInUserId;
	}

	openEmailEditor( e ) {
		e.preventDefault();

		const { email, checked } = this.state;
		this.setState( { isEditorOpen: true } );

		if ( ! this.isUserLoggedIn || this.isSelfProfile ) {
			if ( ! isEmail( email ) ) {
				this.setState( { feedback: [
					( <p key="missing-email" className="reception-error">{ __( 'Merci de renseigner un email valide.', 'reception' ) }</p> ),
				] } );

				return;
			}

			if ( ! checked ) {
				this.setState( { feedback: [
					( <p key="missing-email" className="reception-info">{ __( 'Vérification de votre email. merci de patienter.', 'reception' ) }</p> ),
				] } );

				apiFetch( {
					path: '/reception/v1/email/check/' + email,
					method: 'GET'
				} ).then( ( verifiedEmail ) => {
					let updatedFeedback = [];

					if ( updatedFeedback && ! updatedFeedback.id ) {
						updatedFeedback = [ ( <p key="reception-unverified" className="reception-info">{ __( 'Votre e-mail n’a pas été vérifié, merci de procéder à cette vérification.', 'reception' ) }</p> ) ];
					}

					this.setState( {
						feedback: updatedFeedback,
						verifiedEmail: verifiedEmail,
					} );
				} );

				this.setState( { checked: true } );
			}
		}
	}

	closeEmailEditor() {
		this.setState( {
			isEditorOpen: false,
			feedback: [],
		} );
	}

	sendEmail( e ) {
		e.preventDefault();

		console.log( 'send' );
	}

	render() {
		const { displayUserId, email, isEditorOpen, feedback, verifiedEmail } = this.state;
		const labelEmailInput = displayUserId && this.isSelfProfile ? __( 'E-mail du destinataire', 'reception' ) : __( 'Votre e-mail', 'reception' );
		const labelCancelButton = 0 !== feedback.length ? __( 'Fermer', 'reception' ) : __( 'Annuler', 'reception' );
		let emailInput;

		console.log( verifiedEmail );

		if ( ! this.isUserLoggedIn || this.isSelfProfile ) {
			emailInput = (
				<TextControl
					label={ labelEmailInput }
					type="email"
					value={ email }
					onChange={ ( email ) => this.setState( { email: email } ) }
				/>
			);
		}

		return(
			<Fragment>
				{ emailInput }
				<Button
					isPrimary={ true }
					onClick={ ( e ) => this.openEmailEditor( e ) }
				>
					{ __( 'Rédiger votre message', 'reception' ) }
				</Button>
				{ isEditorOpen && (
					<Modal title={ __( 'Envoyer un message', 'reception' ) } onRequestClose={ this.closeEmailEditor }>
						{ feedback }
						{ 0 === feedback.length && (
							<Button
								isPrimary={ true }
								onClick={ ( e ) => this.sendEmail( e ) }
							>
								{ __( 'Envoyer', 'reception' ) }
							</Button>
						) }
						<Button
							onClick={ () => this.closeEmailEditor() }
						>
							{ labelCancelButton }
						</Button>
					</Modal>
				) }
			</Fragment>
		);
	}
}

render( <MemberContactForm />, document.querySelector( '.reception-member-contact-form-content' ) );
