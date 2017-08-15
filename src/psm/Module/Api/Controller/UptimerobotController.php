<?php

namespace psm\Module\Api\Controller;
use psm\Module\AbstractController;
use psm\Service\Database;

class UptimerobotController extends AbstractController {

	function __construct(Database $db, \Twig_Environment $twig) {
		parent::__construct($db, $twig);

		$this->setMinUserLevelRequired(PSM_USER_ANONYMOUS);

        $this->setActions(array(
            'index'
        ), 'index');

        if (empty($_GET["apikey"])) {
            $this->ajax(["error"=>"api key miss"]);
        }
	}

	protected function executeIndex() {
        $list = $this->db->select(PSM_DB_PREFIX . 'servers', array(
            'type'=>'website'
        ), array(
            'server_id', 'label', 'ip', 'port', 'status', 'type', 'headers', 'pattern', 'active', 'sms'
        ));
        
        $servers = [];
        foreach ($list as $server) {
            //无法处理header
            if (!empty($server["headers"])) continue;
            $servers[$server["server_id"]] = $server;
        }
       
        $UptimeRobot = new UptimeRobot();
        $UptimeRobot->key($_GET["apikey"]);
        
        $alertID = 0;
        $alert = $UptimeRobot->getAlertContacts();
        foreach ($alert as $row) {
            if ($row["type"]==5) {
                $alertID = $row["id"];
                break;
            }
        }

        $monitors = $UptimeRobot->getMonitors();
        
        // cloudmonitor.cc 中已经删除的记录，做同步删除
        foreach ($monitors as $id => $monitor) {
            if (!isset($servers[$id])) {
                $UptimeRobot->deleteMonitor($monitor["id"]);
            }
        }
        
        foreach ($servers as $server) {
            
            $type = 2; //Keyword match mode
            if (empty($server["pattern"]) || $server["pattern"] == '.+') {
                $type = 1;
            }
            
            $monitor = [
                "url"            =>$server["ip"],
                "friendly_name"  =>$server["server_id"]."|".$server["label"],
                "type"           =>$type,
                "keyword_type"   =>2,
                "keyword_value"  =>$server["pattern"],
                "interval"       =>120,
                "alert_contacts"  =>"{$alertID}_0_0",
            ];

            if ($server["active"] == "yes") {
                if (!isset($monitors[$server["server_id"]])) {
                    $data = $UptimeRobot->newMonitor($monitor);
                }else{
                    $monitor["id"] = $monitors[$server["server_id"]]["id"];
                    $data = $UptimeRobot->editMonitor($monitor);
                }
            }else{
                // 暂停的任务直接删除，避免占用过多任务
                $data = $UptimeRobot->deleteMonitor($monitor["id"]);
            }
            
            print_r($data);
        }

        exit;
    }
    
    protected function ajax($json) {
        header("ContentType: application/json");
        echo json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

class UptimeRobot
{
    private $base_uri = 'https://api.uptimerobot.com/v2';
    private $apikey = '';
    
    function curl($method, $args=[]) {
        
        $data = ["api_key"=>$this->apikey, "format"=>"json"];
        $data = array_merge($data, $args);
        $data = http_build_query($data);
        
        $curl = `curl -X POST -H "Content-Type: application/x-www-form-urlencoded" -H "Cache-Control: no-cache" -d '{$data}' "{$this->base_uri}/{$method}"`;
        $json = json_decode($curl, true);
        
        if (empty($json) || !isset($json["stat"])) {
            print_r($json);
        }
        
        if ($json["stat"] != "ok") {
            print_r($json);
        }
        
        return $json;
    }
    
    function key($key) {
        $this->apikey = $key;
    }
    
    /**
     * type         1 - HTTP(s), 2 - Keyword, 3 - Ping, 4 - Port
     * keyword_type 1 - exists,  2 - not exists
     * @param array $monitor [id, friendly_name, url, type, keyword_type, keyword_value]
     */
    function newMonitor(array $monitor) {
        $data = [];
        
        try {
            $data = $this->curl('newMonitor', $monitor);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        return $data;
    }
    
    function editMonitor(array $monitor) {
        $data = [];
        
        try {
            $data = $this->curl('editMonitor', $monitor);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        
        return $data;
    }
    
    function deleteMonitor($id) {
        $data = [];

        try {
            $data = $this->curl('deleteMonitor', ["id"=>$id]);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        
        return $data;
    }
    
    function getMonitors() {
        $monitors = [];
        $pagination = 0;
        $limit = 50;
        
        while (true) {
            $data = $this->curl('getMonitors', ["limit"=>$limit, "offset"=> $limit * $pagination, "alert_contacts"=>1]);
            $monitors = array_merge($monitors, $data["monitors"]);
            
            if (ceil($data["pagination"]["total"] / $limit) == $pagination) {
                break;
            }
            
            $pagination ++;
        }
        
        $array = [];
        foreach ($monitors as $monitor) {
            $title = explode("|", $monitor["friendly_name"]);
            if (count($title) == 1) continue;
            $array[$title[0]] = $monitor;
        }
        
        return $array;
    }
    
    function getAlertContacts() {
        $result = [];
        $pagination = 0;
        $limit = 50;
        
        while (true) {
            $data = $this->curl('getAlertContacts', ["limit"=>$limit, "offset"=> $limit * $pagination]);
            $result = array_merge($result, $data["alert_contacts"]);
        
            if (ceil($data["pagination"]["total"] / $limit) == $pagination) {
                break;
            }
        
            $pagination ++;
        }

        return $result;
    }
}