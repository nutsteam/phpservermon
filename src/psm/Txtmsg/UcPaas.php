<?php

namespace psm\Txtmsg;

function send_msg_ucpaas($accountSid, $accountToken, $phone, $message)
{
	$SoftVersion = '2014-06-30';

	$time = date("YmdHis");
	$SigParameter = md5($accountSid . $accountToken . $time);
	$SigParameter = strtoupper($SigParameter);

	$Authorization= base64_encode($accountSid.":".$time); 

	$function    = 'Calls';
	$operation   = 'voiceNotify';

	$data = [
		"voiceNotify"=> [
			"appId"     => "8885b25524bc4f5d9f260dd8a9312dd7", 
			"to"        => $phone, 
			"type"      => 0,
			"content"   => $message,
			"playTimes" => 2
		],
	];

	$data = json_encode($data);

	$api = "https://api.ucpaas.com/{$SoftVersion}/Accounts/{$accountSid}/{$function}/{$operation}?sig={$SigParameter}";
	$cmd = "curl -XPOST -H 'Content-Type: application/json;charset=utf-8' -H 'Accept: application/json' -H 'Authorization: $Authorization' '$api' -d '$data'";
	$json = `$cmd`;
	$json = json_decode($json, true);

	return $json["resp"]["respCode"] == "000000";
}

class UcPaas extends Core {

	public $gateway = 1;
	public $resultcode = null;
	public $resultmessage = null;
	public $success = false;
	public $successcount = 0;

	public function sendSMS($message) {

		foreach( $this->recipients as $phone ){
			$result = send_msg_ucpaas($this->username, $this->password, $phone, $message);
		}

		return $result;
	}
}
