<?php
define( "FILE_DIR", "_tmp/" );

$REQUIRED_ITEMS = array (
	"name",
	"email"
);

$to   = "to@example.com";
$from = "no-reply@example.com";
$subjectAdmin = "お問い合わせ | {$_SERVER[ "HTTP_HOST" ]}"; // Contact Form
$subjectUser  = "自動返信 | {$_SERVER[ "HTTP_HOST" ]}"; // Auto-reply
$userInfo = @gethostbyaddr( $_SERVER[ "REMOTE_ADDR" ] ) . "\n" . $_SERVER[ "HTTP_USER_AGENT" ];
$templateAdmin = <<<EOF
以下の内容でお問い合わせを受け付けました。
------------------------------
## お名前
{$_POST[ "name" ]}

## メールアドレス
{$_POST[ "email" ]}

## 選択肢1
{$_POST[ "radio1" ]}

## お問い合わせ内容
{$_POST[ "comment" ]}

------------------------------

{$userInfo}
EOF;

// Thank you for contacting us. We will be in touch with you very soon.
// We have received the below message.
$templateUser = <<<EOF
お問い合わせありがとうございます。
以下のお問い合わせ内容で、お問い合わせを受け付けました。
内容を確認のうえ、ご返信いたします。
------------------------------
## お名前
{$_POST[ "name" ]}

## メールアドレス
{$_POST[ "email" ]}

## 選択肢1
{$_POST[ "radio1" ]}

## お問い合わせ内容
{$_POST[ "comment" ]}

------------------------------
EOF;

// -----------------------------------------------------------------------------

header( "content-type: application/json; charset=utf-8" );

mb_language( "Japanese" );
mb_internal_encoding( "UTF-8" );
$headers = "Content-Type: multipart/mixed;boundary=\"__BOUNDARY__\"\nFrom: {$from}\n";
$files = array();
$result = array(
	"errors" => array(),
	"state" => 0
);

// bot対策: formsecretの値のチェック
if ( ! $_SERVER[ "HTTP_HOST" ] === $_POST[ "formsecret" ] ) {

	$result[ "state" ] = "Not Acceptable";
	echo json_encode( $result );
	exit;

}

// 必須項目が空になっていないかのチェック
foreach ( $REQUIRED_ITEMS as $requiredItemKey ) {

	if ( empty( $_POST[ $requiredItemKey ] ) ) {

		array_push( $result[ "errors" ], $requiredItemKey );

	}

}

// emailパターンチェック
if (
	! preg_match( "/^[\w\-\.]+\@[\w\-\.]+\.([a-z]+)$/", $_POST[ "email" ] ) &&
	in_array( "email", $result[ "errors" ] )
) {

	array_push( $result[ "errors" ], "email" );

}

// ファイルのアップロード
foreach ( $_FILES as $file ) {

	$uploadRes = move_uploaded_file( $file[ "tmp_name" ], FILE_DIR.$file[ "name" ] );

	if( $uploadRes !== true ) {

		// failed to upload a file
		array_push( $result[ "errors" ], "file" );

	} else {

		array_push( $files, $file[ "name" ] );

	}

}

$hasError = count( $result[ "errors" ] ) > 0;

// エラーがあればJSONを出力して終わり
if ( $hasError ) {

	$result[ "state" ] = "Error";
	echo json_encode( $result );
	// clean the file directory
	foreach ( $files as $fileName ) unlink( FILE_DIR.$fileName );
	exit;

}

if (
	mb_send_mail( $to,               $subjectAdmin, makeMessageBody( $templateAdmin, $files ), $headers ) &&
	mb_send_mail( $_POST[ "email" ], $subjectUser,  makeMessageBody( $templateUser,  $files ), $headers )
) {

	$result[ "state" ] = "OK";
	echo json_encode( $result );
	// clean the file directory
	foreach ( $files as $fileName ) unlink( FILE_DIR.$fileName );
	exit;

} else {

	$result[ "state" ] = "Failed";
	echo json_encode( $result );
	exit;

}

function makeMessageBody( $message, $files ) {

	$body = "--__BOUNDARY__\n";
	$body .= "Content-Type: text/plain; charset=\"ISO-2022-JP\"\n\n";
	$body .= "{$message}\n";

	// ファイルを添付
	foreach ( $files as $fileName ) {
		$body .= "--__BOUNDARY__\n";
		$body .= "Content-Type: application/octet-stream; name=\"{$fileName}\"\n";
		$body .= "Content-Disposition: attachment; filename=\"{$fileName}\"\n";
		$body .= "Content-Transfer-Encoding: base64\n";
		$body .= "\n";
		$body .= chunk_split( base64_encode( file_get_contents( FILE_DIR.$fileName ) ) );
	}

	$body .= "--__BOUNDARY__\n";

	return $body;

}
