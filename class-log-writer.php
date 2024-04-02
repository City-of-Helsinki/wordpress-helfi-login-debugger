<?php

namespace CityOfHelsinki\WordPress\LoginDebugger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Log_Writer
{
	protected LLAR_Adapter $adapter;
	protected array $errors;

	public function __construct( LLAR_Adapter $adapter )
	{
		$this->adapter = $adapter;
		$this->errors = array();
	}

	public function hook(): string
	{
		return 'login_errors';
	}

	public function collect( string $errors ): string
	{
		$this->errors[] = $errors;

		return $errors;
	}

	public function hasErrors(): bool
	{
		return ! empty( $this->errors );
	}

	public function message(): string
	{
		return $this->hasErrors() ? $this->buildMessage() : '';
	}

	protected function buildMessage(): string
	{
		$message = implode( '<br />', array_unique( $this->errors ) );

		$username = $this->getUsername();
		if ( $username ) {
			$message .= sprintf(
				'<p><strong>Username</strong>: %s</p>',
				$username
			);
		}

		$ip = $this->adapter->detectIp();
		if ( $ip ) {
			$message .= sprintf(
				'<p><strong>IP</strong>: %s</p>',
				$ip
			);
		}

		return $message;
	}

	protected function getUsername(): string
	{
		return ! empty( $_POST['log'] ) ? $_POST['log'] : '';
	}
}
