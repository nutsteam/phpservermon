<?php
namespace psm\Module\Api;

use psm\Module\ModuleInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ApiModule implements ModuleInterface {

	public function load(ContainerBuilder $container) {

	}

	public function getControllers() {
		return array(
			'api' => __NAMESPACE__ . '\Controller\ApiController',
		    'uptimerobot' => __NAMESPACE__ . '\Controller\UptimerobotController',
		);

	}
	
	public function token($str) {
	    $privateKey = defined('PSM_REGION_KEY') ? PSM_REGION_KEY : '';
	    return $str.substr(md5($str.$privateKey), 0, strlen($str));
	}
}
