( function () {

	const $form = document.getElementById( 'mailform' );

	$form.addEventListener( 'submit', function ( event ) {

		event.preventDefault();
		const formData = new FormData();

		$form.setAttribute( 'aria-busy', true );

		Array.prototype.forEach.call( $form.elements, function ( $input ) {

			$input.disabled = true;

			if ( ! $input.name || formData.has( $input.name ) ) return;

			formData.append( $input.name, $form.elements[ $input.name ].value );

		} );

		formData.append( 'formsecret', location.host );

		const xhr = new XMLHttpRequest();
		xhr.open( 'POST', $form.action, true );
		xhr.onload = function () {

			const json = JSON.parse( xhr.responseText );

			$form.setAttribute( 'aria-busy', false );

			Array.prototype.forEach.call( $form.elements, function ( $input ) {

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
