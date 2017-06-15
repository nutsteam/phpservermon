<?php
/**
 * PHP Server Monitor
 * Monitor your servers and websites.
 *
 * This file is part of PHP Server Monitor.
 * PHP Server Monitor is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PHP Server Monitor is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PHP Server Monitor.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package     phpservermon
 * @author      Pepijn Over <pep@peplab.net>
 * @copyright   Copyright (c) 2008-2015 Pepijn Over <pep@peplab.net>
 * @license     http://www.gnu.org/licenses/gpl.txt GNU GPL v3
 * @version     Release: @package_version@
 * @link        http://www.phpservermonitor.org/
 **/

namespace psm\Module\Api\Controller;
use psm\Module\AbstractController;
use psm\Service\Database;
use psm\Module\Api\ApiModule;

class ApiController extends AbstractController {

	function __construct(Database $db, \Twig_Environment $twig) {
		parent::__construct($db, $twig);

		$this->setMinUserLevelRequired(PSM_USER_ANONYMOUS);

        $this->setActions(array(
            'index', 'server', 'update'
        ), 'index');

        if (! $this->checkToken()) {
            return $this->ajax(["error"=>"token error"]);
        }
	}

	protected function executeIndex() {
        $servers = $this->db->select(PSM_DB_PREFIX . 'servers', array(
            'active' => 'yes',
        ), array(
            'server_id', 'label', 'status', 'type'
        ));
        $this->ajax($servers);
    }

    protected function executeServer() {
        $server_id = intval($_GET["server_id"]);
        if (empty($server_id)) {
            return $this->ajax(["error"=>"server_id is null"]);
        }

        $servers = $this->db->selectRow(PSM_DB_PREFIX . 'servers', array(
            'server_id' => $server_id,
        ), array(
            'server_id', 'ip', 'port', 'label', 'type', 'pattern', 'header_name', 'header_value', 'headers', 'status', 'active', 'warning_threshold',
            'warning_threshold_counter', 'timeout', 'website_username', 'website_password'
        ));
        $this->ajax($servers);
    }

    protected function executeUpdate() {
        $server_id = intval($_GET["server_id"]);
        $status    = intval($_GET["status"]);
        $latency   = floatval($_GET["latency"]);
        $region    = trim($_GET["region"]);

        if (empty($server_id)) {
            return $this->ajax(["error"=>"server_id is null"]);
        }

        psm_log_uptime($server_id, $status, $latency, $region);
        $this->ajax(["error"=>"ok"]);
    }

    protected function checkToken() {
        $token = $_GET["token"];
        $time  = substr($token, 0, strlen($token)/2);

        if (empty($token) || empty($time)) {
            return false;
        }

        if ($time < time() - 86400) {
            return false;
        }

        return (new ApiModule)->token($time) == $token;
    }

    protected function ajax($json) {
        header("ContentType: application/json");
        echo json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

}
