<?php
/**
 * PHPUnit Bootstrap for GCB Tests.
 *
 * @package GCB_Content_Intelligence\Tests
 */

declare(strict_types=1);

// Composer autoloader (for PHPUnit and dependencies).
$autoloader = dirname( __DIR__, 2 ) . '/vendor/autoload.php';
if ( file_exists( $autoloader ) ) {
	require_once $autoloader;
}

// Note: Migration parser tests are standalone (no WordPress required).
// The test files include the required classes directly.
// WordPress integration tests can be added later with wp-phpunit.
