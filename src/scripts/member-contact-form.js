/**
 * WordPress dependencies.
 */
const { Component, render, createElement, Fragment } = wp.element;
const { __ } = wp.i18n;
const { RichText } = wp.blockEditor;
const { Button, TextControl, Modal, Snackbar, SelectControl, Notice } = wp.components;
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
			sending: false,
			verifiedEmail: {},
			isEditorOpen: false,
			feedback: [],
			situations: [],
			situation: 'reception-contact-member',
			needsContent: true,
			modalTitle: __( 'Envoyer un message', 'reception' ),
			modalAction: __( 'Envoyer', 'reception' ),
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

			if ( window.receptionMemberContactForm.name ) {
				this.setState( { name: window.receptionMemberContactForm.name } );
			}

			if ( window.receptionMemberContactForm.email ) {
				this.setState( { email: window.receptionMemberContactForm.email } );
			}

			if ( window.receptionMemberContactForm.situations ) {
				this.setState( { situations: JSON.parse( window.receptionMemberContactForm.situations ) } );
			}
		}

		this.isUserLoggedIn = loggedInUserId && 0 !== loggedInUserId;
		this.isSelfProfile  = this.isUserLoggedIn && displayUserId === loggedInUserId;
	}

	setSituation( situation ) {
		const { situations, modalTitle, modalAction } = this.state;
		let needsContent = true, customModalTitle = modalTitle, customModalAction = modalAction;

		situations.forEach( ( availableSituation ) => {
			if ( situation === availableSituation.value ) {
				if( ! availableSituation.needs_content || false === availableSituation.needs_content ) {
					needsContent = false;
				}

				if ( availableSituation.label ) {
					customModalTitle = availableSituation.label;
				}

				if ( !! availableSituation.action ) {
					customModalAction = availableSituation.action;
				}
			}
		} );

		this.setState( {
			situation: situation,
			needsContent: needsContent,
			modalTitle: customModalTitle,
			modalAction: customModalAction,
		} );
	}

	openEmailEditor( e ) {
		e.preventDefault();

		const { name, email, checked } = this.state;
		this.setState( { isEditorOpen: true } );

		if ( ! this.isUserLoggedIn || this.isSelfProfile ) {
			if ( ! name ) {
				this.setState( { feedback: [
					( <Notice key="missing-name" status="error" isDismissible={ false }>{ __( 'Merci de renseigner un prénom et un nom.', 'reception' ) }</Notice> ),
				] } );

				return;
			}

			if ( ! isEmail( email ) ) {
				this.setState( { feedback: [
					( <Notice key="missing-email" status="error" isDismissible={ false }>{ __( 'Merci de renseigner un e-mail valide.', 'reception' ) }</Notice> ),
				] } );

				return;
			}

			if ( ! checked ) {
				this.setState( { feedback: [
					( <Notice key="checking-email" status="info" isDismissible={ false }>{ __( 'Vérification de votre e-mail. merci de patienter.', 'reception' ) }</Notice> ),
				] } );

				apiFetch( {
					path: '/reception/v1/email/check/' + email,
					method: 'GET'
				} ).then( ( verifiedEmail ) => {
					let updatedFeedback = [];

					if ( this.isSelfProfile ) {
						if ( true === verifiedEmail.spam ) {
							updatedFeedback = [ (
								<Notice key="reception-spam" status="error" isDismissible={ false }>{ __( 'Désolé l’e-mail du visiteur a été marqué comme indésirable : vous ne pouvez pas utiliser le site pour le contacter.', 'reception' ) }</Notice>
							) ];
						} else if ( ! verifiedEmail.id || ! verifiedEmail.confirmed ) {
							updatedFeedback = [ (
								<Notice key="reception-not-verified" status="error" isDismissible={ false }>{ __( 'L‘email du visisteur que vous souhaitez contacter n’a pas été vérifié, merci de lui demander de le faire ou de le contacter directement.', 'reception' ) }</Notice>
							) ];
						}
					} else {
						if ( true === verifiedEmail.spam ) {
							updatedFeedback = [ (
								<Notice key="reception-spam" status="error" isDismissible={ false }>{ __( 'Désolé votre e-mail a été marqué comme indésirable : vous ne pouvez pas contacter ce membre.', 'reception' ) }</Notice>
							) ];
						} else if ( ! verifiedEmail.id ) {
							updatedFeedback = [ (
								<Fragment key="reception-unverified">
									<Notice status="warning" isDismissible={ false }>{ __( 'Votre e-mail a besoin d’être validé, cette étape de validation est nécessaire afin de garantir à nos membres qu’ils ne recevront pas de messages indésirables.', 'reception' ) }</Notice>
									<p className="reception-help description">{ __( 'Merci de cliquer sur le bouton « Obtenir le code de validation » afin de recevoir un e-mail le contenant dans les prochaines minutes.', 'reception' ) }</p>
									<p className="reception-help description">{ __( 'Dés que vous l’aurez reçu, vous pourrez revenir sur cette page afin de l’utiliser pour déverrouiller cette sécurité et contacter ce membre. Merci de votre compréhension.', 'reception' ) }</p>
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
								<Notice key="reception-do-verify" status="info" isDismissible={ false }>{ __( 'Le code de validation associé à votre e-mail a besoin d’être vérifié, Merci de copier le code de validation que vous avez reçu dans le champ ci-dessous avant de lancer la vérification.', 'reception' ) }</Notice>
							) ];

							this.setState( { needsValidation: true } );
						}
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
			checked: false,
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

		this.closeEmailEditor();
	}

	sendEmail( e ) {
		e.preventDefault();

		const { name, email, message, displayUserId, loggedInUserId, situation, needsContent, sending } = this.state;
		let emailData = {
			name: name,
			email: email,
			message: message,
		};

		if ( loggedInUserId && ! this.isSelfProfile ) {
			emailData.current_user = loggedInUserId;
		}

		if ( 'reception-contact-member' !== situation ) {
			emailData.situation = situation;

			if ( ! needsContent ) {
				emailData.message = situation;
			}
		}

		if ( ! emailData.message ) {
			this.setState( {
				resultMessage: __( 'Merci d’ajouter du texte à votre e-mail.', 'reception' ),
				message: '',
				sending: false,
			} );

			this.closeEmailEditor();
			return;
		}

		if ( ! sending ) {
			this.setState( { sending: true } );

			apiFetch( {
				path: '/reception/v1/email/send/' + displayUserId,
				method: 'POST',
				data: emailData,
			} ).then( ( response ) => {
				this.setState( {
					resultMessage: __( 'Votre e-mail a bien été transmis.', 'reception' ),
					verifiedEmail: response.verifiedEmail,
					message: '',
					sending: false,
				} );
			}, () => {
				this.setState( {
					resultMessage: __( 'Désolé, l’envoi de votre e-mail a échoué.', 'reception' ),
					message: '',
					sending: false,
				} );
			} );
		}

		this.closeEmailEditor();
	}

	render() {
		const {
			displayUserId,
			loggedInUserId,
			name,
			email,
			isEditorOpen,
			feedback,
			resultMessage,
			confirmationCode,
			needsValidation,
			message,
			situations,
			situation,
			needsContent,
			modalTitle,
			modalAction,
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
					{ 0 !== situations.length && 0 === loggedInUserId && (
						<SelectControl
							label={ __( 'Motif de votre contact', 'reception' ) }
							value={ situation }
							options={ situations }
							onChange={ ( situation ) => this.setSituation( situation ) }
						/>
					) }
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
				{ '' !== resultMessage &&
					<Snackbar onRemove={ () => this.setState( { resultMessage: '' } ) }>
						{ resultMessage }
					</Snackbar>
				}
				{ isEditorOpen && (
					<Modal title={ modalTitle } onRequestClose={ this.closeEmailEditor } className="reception-contact-form-modal">
						<div className="reception-modal-content">
							{ feedback }
							{ 0 === feedback.length && (
								<Fragment>
									{ true === needsContent && (
										<Fragment>
											<h2>{ __( 'Votre message', 'reception' ) }</h2>
											<RichText
												value={ message }
												tagName="p"
												onChange={ ( text ) => this.setState( { message: text } ) }
												placeholder={ __( 'Utilisez cette zone pour rédiger votre message', 'reception' ) }
												multiline={ true }
											/>
										</Fragment>
									) }
									<Button
										isPrimary={ true }
										onClick={ ( e ) => this.sendEmail( e ) }
									>
										{ modalAction }
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
						</div>
					</Modal>
				) }
			</Fragment>
		);
	}
}

render( <MemberContactForm />, document.querySelector( '.reception-member-contact-form-content' ) );
