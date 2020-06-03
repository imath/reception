/**
 * WordPress dependencies.
 */
const { Component, render, createElement, Fragment } = wp.element;
const { TextControl, Button, Notice } = wp.components;
const { apiFetch } = wp;
const { isEmail } = wp.url;
const { __ } = wp.i18n;
const { dateI18n } = wp.date;

class ManageVerifiedEmail extends Component {
	constructor() {
		super( ...arguments );

		this.state = {
			email: '',
			entry: [],
			feedback: {},
			searching: false,
		};
	}

	lookupEmail( e ) {
		e.preventDefault();

		const { email, searching } = this.state;

		if ( ! isEmail( email ) ) {
			this.setState( { feedback: {
				status: 'error',
				text: __( 'Merci de renseigner un e-mail valide.', 'reception' ),
			} } );

			return;
		}

		if ( false === searching ) {
			this.setState( { searching: true, entry: [], feedback: {} } );

			apiFetch( {
				path: '/reception/v1/email/?email=' + email,
				method: 'GET'
			} ).then( ( verifiedEmail ) => {
				this.setState( { entry: verifiedEmail } );

				if ( ! verifiedEmail.length ) {
					this.setState( { feedback: {
						status: 'info',
						text: __( 'Aucun élément ne correspond à votre recherche', 'reception' ),
					} } );
				}
			}, ( error ) => {
				this.setState( { feedback: {
					status: 'error',
					text: error.message,
				} } );
			} ).then( () => {
				this.setState( { searching: false } );
			} );
		}
	}

	spamUnspam( e ) {
		e.preventDefault();

		const { entry } = this.state;

		// @todo.
	}

	deleteEntry( e ) {
		e.preventDefault();

		const { entry } = this.state;

		// @todo.
	}

	getHeadFoot( id ) {
		return (
			<tr>
				<td id="cb" className="manage-column column-cb check-column"></td>
				<th scope="col" id={ 'comment-' + id } className="manage-column column-comment">
					{ __( 'Code de confirmation', 'reception' ) }
				</th>
				<th scope="col" id={ 'status-' + id } className="manage-column column-response">
					{ __( 'Statut', 'reception' ) }
				</th>
				<th scope="col" id={ 'date-confirmed-' + id } className="manage-column column-date">
					{ __( 'Code confirmé le', 'reception' ) }
				</th>
				<th scope="col" id={ 'date-last-use-' + id } className="manage-column column-date">
					{ __( 'Dernier envoi le', 'reception') }
				</th>
			</tr>
		);
	}

	render() {
		const { email, feedback, entry } = this.state;
		let entryItems;

		if ( entry.length >= 1 ) {
			entryItems = entry.map( ( item ) => {
				return (
					<tr key={ 'item-' + item.id } id={ 'item-' + item.id } className={ 'comment byuser depth-1 ' + true === item.spam ? 'unapproved' : 'approved' }>
						<th scope="row" className="check-column" />
						<td className="comment column-comment has-row-actions column-primary">
							<strong>{ item.code }</strong>
							<div className="row-actions">
								{ true !== item.spam && (
									<span className="spam">
										<a href="#markspam" onClick={ ( e ) => this.spamUnspam( e ) } aria-label={ __( 'Marquer comme indésirable', 'reception' ) } role="button">
											{ __( 'Indésirable', 'reception' ) }
										</a>
									</span>
								) }
								{ true === item.spam && (
									<span className="unspam approve">
										<a href="#markham" onClick={ ( e ) => this.spamUnspam( e ) } aria-label={ __( 'Marquer comme non indésirable', 'reception' ) } role="button">
											{ __( 'Non Indésirable', 'reception' ) }
										</a>
									</span>
								) }

								&nbsp;|&nbsp;<span className="trash">
									<a href="#delete" onClick={ ( e ) => this.deleteEntry( e ) } aria-label={ __( 'Supprimer cette entrée', 'reception' ) } role="button" className="delete">
										{ __( 'Supprimer', 'reception' ) }
									</a>
								</span>
							</div>
						</td>
						<td className="response column-response">
							{ ! item.confirmed && ! item.spam && (
								<span>{ __( 'En attente de validation du code', 'reception' ) }</span>
							) }
							{ item.confirmed && ! item.spam && (
								<span>{ __( 'Confirmé', 'reception' ) }</span>
							) }
							{ item.spam && (
								<span>{ __( 'Indésirable', 'reception' ) }</span>
							) }
						</td>
						<td className="date column-date">
							<div className="submitted-on"> { sprintf( __( '%s à %s', 'risk-ops' ), dateI18n( 'd/m/Y', item.confirmation_date ), dateI18n( 'H:i', item.confirmation_date ) ) }</div>
						</td>
						<td className="date column-date">
							<div className="submitted-on"> { sprintf( __( '%s à %s', 'risk-ops' ), dateI18n( 'd/m/Y', item.last_use_date ), dateI18n( 'H:i', item.last_use_date ) ) }</div>
						</td>
					</tr>
				);
			} );
		}


		return (
			<Fragment>
				<div className="wp-privacy-request-form-field">
					<TextControl
						className="regular-text"
						label={ __( 'Adresse de messagerie', 'reception' ) }
						type="search"
						value={ email }
						onChange={ ( email ) => this.setState( { email: email } ) }
					/>
					<Button isSecondary onClick={ ( e ) => this.lookupEmail( e ) } className="button">
						{ __( 'Charger les informations', 'reception' ) }
					</Button>
				</div>

				<hr/>

				{ feedback && feedback.status && (
					<Notice status={ feedback.status } onRemove={ () => this.setState( { feedback: {} } ) }>
						<p>{ feedback.text }</p>
					</Notice>
				) }

				{ entry.length >= 1 && (
					<table className="wp-list-table widefat striped comments">
						<thead>{ this.getHeadFoot( 1 ) }</thead>
						<tbody id="the-comment-list">
							{ entryItems }
						</tbody>
						<tfoot>{ this.getHeadFoot( 2 ) }</tfoot>
					</table>
				) }
			</Fragment>
		);
	}
}
render( <ManageVerifiedEmail />, document.querySelector( '#reception-verified-emails' ) );
