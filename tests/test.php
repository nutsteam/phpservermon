<?php
define("PSM_DEBUG",   true);
define("PSM_INSTALL", true);

require dirname(__FILE__) . '/../src/bootstrap.php';

psm_load_lang("de_DE");

$message        = $subject = "你好-Löschen-Düzenle-Действие-ใบแจ้งหนี้-저장";
$status_new     = 0;
$server         = ["LABEL"=>$subject];

$mail           = psm_build_mail();
$mail->Priority	= 1;
$mail->AddAddress("admin@4wei.cn", "shuhai");


//------------------------------------------------------------------

$mail->Subject  = $subject;
$mail->Body     = $message;
$mail->AltBody  = str_replace('<br/>', "\n", $message);


var_dump($mail->send());

//------------------------------------------------------------------
$message        = psm_parse_msg($status_new, 'email_body', $server);

$mail->Subject	= $subject . trim(psm_parse_msg($status_new, 'email_subject', $server));
$mail->Body		= $subject . trim($message);
$mail->AltBody	= str_replace('<br/>', "\n", $message);

var_dump($mail->send());

//------------------------------------------------------------------
$mail->Subject	= $subject . utf8_decode(psm_parse_msg($status_new, 'email_subject', $server));
$mail->Body		= $subject . utf8_decode($message);
$mail->AltBody	= str_replace('<br/>', "\n", $message);

var_dump($mail->send());