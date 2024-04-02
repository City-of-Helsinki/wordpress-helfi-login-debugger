<?php

namespace CityOfHelsinki\WordPress\LoginDebugger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LLAR_Adapter
{
	protected bool $canDetect;

	public function __construct()
	{
		$this->canDetect = class_exists( 'LLAR\Core\Helpers' );
	}

	public function detectIp(): string
	{
		return $this->canDetect
			? \LLAR\Core\Helpers::detect_ip_address( \LLAR\Core\Config::get( 'trusted_ip_origins' ) )
			: '';
	}
}
