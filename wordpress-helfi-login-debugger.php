<?php

namespace CityOfHelsinki\WordPress\LoginDebugger;

/**
  * Plugin Name: WordPress Helsinki Login Debugger
  * Description: Log login errors to comments.
  * Version: 1.0.0
  * License: GPLv3
  * Requires at least: 5.7
  * Requires PHP:      7.1
  * Author: ArtCloud
  * Author URI: https://www.artcloud.fi
  */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'class-llar-adapter.php';
require_once plugin_dir_path( __FILE__ ) . 'class-log-writer.php';

function log_writer( LLAR_Adapter $adapter ): Log_Writer {
	return new Log_Writer( $adapter );
}

function adapter(): LLAR_Adapter {
	return new LLAR_Adapter();
}

add_action( 'init', __NAMESPACE__ . '\\setup' );
function setup(): void {
	$logger = log_writer( adapter() );
	$callbacks = hook_callbacks( $logger->hook() );

	foreach ( $callbacks as $priority => $callback ) {
		add_filter( $logger->hook(), array( $logger, 'collect' ), $priority );
	}

	add_action( 'shutdown', function() use ( $logger ) {
		if ( $logger->hasErrors() ) {
			wp_insert_comment( array(
				'comment_author' => $logger->hook(),
				'comment_content' => $logger->message(),
			) );
		}
	} );
}

function hook_callbacks( string $name ): array {
	global $wp_filter;

	if ( ! isset( $wp_filter[$name] ) ) {
		return array();
	}

	if ( empty( $wp_filter[$name]->callbacks ) ) {
		return array();
	}

	return $wp_filter[$name]->callbacks;
}

register_deactivation_hook( __FILE__, __NAMESPACE__ . '\\deactivate' );
function deactivate(): void {
	$logger = log_writer( adapter() );

	$comment_ids = get_comments( array(
		'author' => $logger->hook(),
		'fields' => 'ids',
	) );

	array_walk(
		$comment_ids,
		function( $comment_id ) {
			wp_delete_comment( $comment_id, true );
		}
	);
}
