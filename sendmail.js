( () => {

	const $form = document.getElementById( 'mailform' );

	$form.addEventListener( 'submit', async( event ) => {

		event.preventDefault();

		// await recaptcha( $form );

		const formData = new FormData();

		$form.setAttribute( 'aria-busy', true );

		Array.prototype.forEach.call( $form.elements, ( $input ) => {

			$input.disabled = true;

			if ( ! $input.name || formData.has( $input.name ) ) return;

			if ( $input.type === 'file' ) {

				formData.append( $input.name, $input.files[ 0 ] );
				return;

			}

			formData.append( $input.name, $form.elements[ $input.name ].value );

		} );

		formData.append( 'formsecret', location.host );

		const xhr = new XMLHttpRequest();
		xhr.open( 'POST', $form.action, true );
		xhr.onload = () => {

			const json = JSON.parse( xhr.responseText );

			$form.setAttribute( 'aria-busy', false );

			Array.prototype.forEach.call( $form.elements, ( $input ) => {

				$input.disabled = false;

			} );

			if ( json.state === 'OK' ) {

				// alert( 'Your email has been sent successfully.' );
				alert( 'お問い合わせを受け付けました。' );
				$form.reset();
				return;

			}

			if ( json.state === 'Error' ) {

				alert( '入力内容にエラーがあります。入力内容をご確認ください。' );
				// alert( 'Faild. Some field(s) does not appear to be valid. Please check once again.' );
				return;

			}

		};

		xhr.send( formData );

	} );

} )();


// function recaptcha( $form ) {

// 	const recaptchaScript = Array.prototype.filter.call( document.scripts, ( s ) => /www\.google\.com\/recaptcha\/api.js/.test( s.src ) )[ 0 ];

// 	return new Promise( ( resolve, reject ) => {

// 		if ( ! recaptchaScript ) {

// 			alert( 'recaptcha script is not loaded' );
// 			reject();
// 			return;

// 		}

// 		const sitekey = getParameterByName( 'render', recaptchaScript.src );
// 		grecaptcha.ready( () => {

// 			grecaptcha.execute( sitekey, { action: '_recaptcha' } ).then( ( token ) => {

// 				const $recaptchaResponseInput = document.createElement( 'input' );
// 				$recaptchaResponseInput.hidden = true;
// 				$recaptchaResponseInput.name = 'recaptcha-response';
// 				$recaptchaResponseInput.value = token;
// 				$form.appendChild( $recaptchaResponseInput );
// 				resolve( token );

// 			} );

// 		} );

// 	} );

// }

// function getParameterByName( name, url = window.location.href ) {

// 	const _name = name.replace( /[\[\]]/g, '\\$&' );
// 	const regex = new RegExp( '[?&]' + _name + '(=([^&#]*)|&|#|$)' );
// 	const results = regex.exec( url );
// 	if ( ! results ) return null;
// 	if ( ! results[ 2 ] ) return '';
// 	return decodeURIComponent( results[ 2 ].replace( /\+/g, ' ' ) );

// }
