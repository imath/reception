/**
 * WordPress dependencies.
 */
const { Component, render, createElement, Fragment } = wp.element;
const { __ } = wp.i18n;
const { RichText } = wp.blockEditor;
const { Button, TextControl, Modal, Snackbar } = wp.components;
const { apiFetch } = wp;
const { isEmail } = wp.url;

class MemberContactForm extends Component {
	constructor() {
		super( ...arguments );

		this.state = {
			name: '',
			email: '',
			message: '',
			confirmationCode: '',
			displayUserId: 0,
			loggedInUserId: 0,
			resultMessage: '',
			needsValidation: false,
			checked: false,
			verifiedEmail: {},
			isEditorOpen: false,
			feedback: [],
		};

		this.closeEmailEditor = this.closeEmailEditor.bind( this );
		this.sendValidationCode = this.sendValidationCode.bind( this );
		this.checkValidationCode = this.checkValidationCode.bind( this );
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

		const { name, email, checked } = this.state;
		this.setState( { isEditorOpen: true } );

		if ( ! this.isUserLoggedIn || this.isSelfProfile ) {
			if ( ! name ) {
				this.setState( { feedback: [
					( <p key="missing-name" className="reception-error">{ __( 'Merci de renseigner un prénom et un nom.', 'reception' ) }</p> ),
				] } );

				return;
			}

			if ( ! isEmail( email ) ) {
				this.setState( { feedback: [
					( <p key="missing-email" className="reception-error">{ __( 'Merci de renseigner un e-mail valide.', 'reception' ) }</p> ),
				] } );

				return;
			}

			if ( ! checked ) {
				this.setState( { feedback: [
					( <p key="missing-email" className="reception-info">{ __( 'Vérification de votre e-mail. merci de patienter.', 'reception' ) }</p> ),
				] } );

				apiFetch( {
					path: '/reception/v1/email/check/' + email,
					method: 'GET'
				} ).then( ( verifiedEmail ) => {
					let updatedFeedback = [];

					if ( ! verifiedEmail.id ) {
						updatedFeedback = [ (
							<Fragment key="reception-unverified">
								<p className="reception-info">{ __( 'Votre e-mail a besoin d’être validé, cette étape de validation est nécessaire afin de garantir à nos membres qu’ils ne recevront pas de messages indésirables.', 'reception' ) }</p>
								<p className="reception-help">{ __( 'Merci de cliquer sur le bouton « Obtenir le code de validation » afin de recevoir un e-mail le contenant dans les prochaines minutes.', 'reception' ) }</p>
								<p className="reception-help">{ __( 'Dés que vous l’aurez reçu, vous pourrez revenir sur cette page afin de l’utiliser pour déverrouiller cette sécurité et contacter ce membre. Merci de votre compréhension.', 'reception' ) }</p>
								<Button
									isPrimary={ true }
									onClick={ ( e ) => this.sendValidationCode( e ) }
								>
									{ __( 'Obtenir le code de validation', 'reception' ) }
								</Button>
							</Fragment>
						) ];
					} else if ( ! verifiedEmail.confirmed ) {
						updatedFeedback = [ (
							<Fragment key="reception-do-verify">
								<p className="reception-info">{ __( 'Le code de validation associé à votre e-mail a besoin d’être vérifié, Merci de copier le code de validation que vous avez reçu dans le champ ci-dessous avant de lancer la vérification.', 'reception' ) }</p>
							</Fragment>
						) ];

						this.setState( { needsValidation: true } );
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

	sendValidationCode( e ) {
		e.preventDefault();

		const { name, email, displayUserId } = this.state;

		apiFetch( {
			path: '/reception/v1/email',
			method: 'POST',
			data: {
				name: name,
				email: email,
				member_id: displayUserId,
			}
		} ).then( ( verifiedEmail ) => {
			this.setState( {
				resultMessage: __( 'L’e-mail contenant le code de validation vous a bien été transmis', 'reception' ),
				verifiedEmail: verifiedEmail,
			} );
		}, () => {
			this.setState( {
				resultMessage: __( 'Désolé, une erreur a empêché l’envoi de s’effectuer.', 'reception' ),
			} );
		} );

		// Make sure the user can recheck without reloading the page.
		this.setState( { checked: false } );
		this.closeEmailEditor();
	}

	checkValidationCode( e ) {
		e.preventDefault();

		const { email, confirmationCode } = this.state;

		apiFetch( {
			path: '/reception/v1/email/validate/' + email,
			method: 'PUT',
			data: {
				code: confirmationCode,
			}
		} ).then( ( verifiedEmail ) => {
			this.setState( {
				resultMessage: __( 'Merci d’avoir validé votre e-mail. Vous pouvez poursuivre la rédaction de votre message', 'reception' ),
				verifiedEmail: verifiedEmail,
				needsValidation: false,
			} );
		}, () => {
			this.setState( {
				resultMessage: __( 'Désolé, la validation de votre e-mail a échoué.', 'reception' ),
				confirmationCode: '',
			} );
		} );

		// Make sure the a check is performed if the code was wrong.
		this.setState( { checked: false } );
		this.closeEmailEditor();
	}

	sendEmail( e ) {
		e.preventDefault();

		const { name, email, message, displayUserId } = this.state;

		console.log( message );
	}

	render() {
		const {
			displayUserId,
			name,
			email,
			isEditorOpen,
			feedback,
			resultMessage,
			confirmationCode,
			needsValidation,
			message,
		} = this.state;
		const labelEmailInput = displayUserId && this.isSelfProfile ? __( 'E-mail du destinataire (obligatoire)', 'reception' ) : __( 'Votre e-mail (obligatoire)', 'reception' );
		const labelNameInput = displayUserId && this.isSelfProfile ? __( 'Prénom et nom du destinataire (obligatoire)', 'reception' ) : __( 'Vos prénom et nom (obligatoire)', 'reception' );
		const labelCancelButton = 0 !== feedback.length ? __( 'Fermer', 'reception' ) : __( 'Annuler', 'reception' );
		let emailInputs;

		if ( ! this.isUserLoggedIn || this.isSelfProfile ) {
			emailInputs = (
				<Fragment>
					<TextControl
						label={ labelNameInput }
						type="text"
						value={ name }
						onChange={ ( name ) => this.setState( { name: name } ) }
						required={ true }
					/>
					<TextControl
						label={ labelEmailInput }
						type="email"
						value={ email }
						onChange={ ( email ) => this.setState( { email: email } ) }
						required={ true }
					/>

					{ '' !== resultMessage &&
						<Snackbar onRemove={ () => this.setState( { resultMessage: '' } ) }>
							{ resultMessage }
						</Snackbar>
					}
				</Fragment>
			);
		}

		return(
			<Fragment>
				{ emailInputs }
				<Button
					isPrimary={ true }
					onClick={ ( e ) => this.openEmailEditor( e ) }
				>
					{ __( 'Rédiger votre message', 'reception' ) }
				</Button>
				{ isEditorOpen && (
					<Modal title={ __( 'Envoyer un message', 'reception' ) } onRequestClose={ this.closeEmailEditor } className="reception-contact-form-modal">
						{ feedback }
						{ 0 === feedback.length && (
							<Fragment>
								<h2>{ __( 'Votre message', 'reception' ) }</h2>
								<RichText
									value={ message }
									tagName="p"
									onChange={ ( text ) => this.setState( { message: text } ) }
									placeholder={ __( 'Utiliser cette zone pour rédiger votre message', 'reception' ) }
									multiline={ true }
								/>
								<Button
									isPrimary={ true }
									onClick={ ( e ) => this.sendEmail( e ) }
								>
									{ __( 'Envoyer', 'reception' ) }
								</Button>
							</Fragment>
						) }
						{ true === needsValidation && (
							<Fragment>
								<TextControl
									label={ __( 'Code de validation', 'reception') }
									type="password"
									value={ confirmationCode }
									onChange={ ( code ) => this.setState( { confirmationCode: code } ) }
									required={ true }
								/>
								<Button
									isPrimary={ true }
									onClick={ ( e ) => this.checkValidationCode( e ) }
								>
									{ __( 'Lancer la vérification', 'reception' ) }
								</Button>
							</Fragment>
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
