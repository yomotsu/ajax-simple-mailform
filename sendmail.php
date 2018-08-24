<?php

$REQUIRED_ITEMS = array (
	"name",
	"email"
);

$to   = "to@example.com";
$from = "no-reply@example.com";
$subjectAdmin = "お問い合わせ | ".$_SERVER[ "HTTP_HOST" ]; // Contact Form
$subjectUser  = "自動返信 | ".$_SERVER[ "HTTP_HOST" ]; // Auto-reply
$template = 
// "Thank you for contacting us. We will be in touch with you very soon.\n".
// "We have received the below message.\n".
"お問い合わせありがとうございます。\n".
"以下のお問い合わせ内容で、お問い合わせを受け付けました。\n".
"内容を確認のうえ、ご返信いたします。\n".

"------------------------------\n".
"## お名前\n".
$_POST[ "name" ]."\n".
"\n".
"## メールアドレス\n".
$_POST[ "email" ]."\n".
"\n".
"## 選択肢1\n".
$_POST[ "radio1" ]."\n".
"\n".
"## お問い合わせ内容\n".
$_POST[ "comment" ]."\n".
"\n".
"------------------------------\n".
"\n".
USERINFO()."\n";

// -----------------------------------------------------------------------------

header( "content-type: application/json; charset=utf-8" );

mb_language( "Japanese" );
mb_internal_encoding( "UTF-8" );
$headers = "From: " . $from . "\r\n";
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

$hasError = count( $result[ "errors" ] ) > 0;

// エラーがあればJSONを出力して終わり
if ( $hasError ) {

	$result[ "state" ] = "Error";
	echo json_encode( $result );
	exit;

}

if (
	mb_send_mail( $to,               $subjectAdmin, $template, $headers ) &&
	mb_send_mail( $_POST[ "email" ], $subjectUser,  $template, $headers )
) {

	$result[ "state" ] = "OK";
	echo json_encode( $result );
	exit;

} else {

	$result[ "state" ] = "Failed";
	echo json_encode( $result );
	exit;

}


function USERINFO(){

	return @gethostbyaddr( $_SERVER[ "REMOTE_ADDR" ] ) . "\n" . $_SERVER[ "HTTP_USER_AGENT" ];

}
