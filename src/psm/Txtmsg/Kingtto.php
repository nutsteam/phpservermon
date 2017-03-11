<?php

namespace psm\Txtmsg;

class Kingtto extends Core
{
    public function sendSMS($message)
    {
        $p["action"]   = "send";
        $p["userid"]   = $this->originator;
        $p["account"]  = $this->username;
        $p["password"] = $this->password;
        $p["mobile"]   = join(",", $this->recipients);
        $p["content"]  = sprintf("【海南坚果】告警业务:%s，告警内容:%s", "CloudMonitor", $message);
        $parameter = http_build_query($p);
        $url = "http://sms.kingtto.com:9999/sms.aspx?{$parameter}";
        $return = `curl -s '$url'`;

        $return = simplexml_load_string($return);
        return $return->returnstatus == "Success";
    }
}
