<?php

namespace psm\Txtmsg;

class Smsgw extends Core
{
    public function sendSMS($message)
    {
        $p["action"]   = "send";
        $p["userid"]   = 4425;
        $p["account"]  = $this->username;
        $p["password"] = $this->password;
        $p["mobile"]   = join(",", $phone);
        $p["content"]  = sprintf("【海南坚果】告警业务:%s，告警内容:%s", "CloudMonitor", $message);
        $parameter = http_build_query($p);
        $url = "http://sms.kingtto.com:9999/sms.aspx?{$parameter}";
        $return =  `curl -s '$url'`;
        $return = simplexml_load_string($return);
        return $return->returnstatus == "Success";
    }
}
