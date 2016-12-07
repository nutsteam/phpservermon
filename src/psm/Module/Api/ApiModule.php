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
		);

	}
}
