<?php
/** Init environment for working with PHPUnit tests.
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage test.bootstrap
 */

$basedir = realpath(dirname(__FILE__) . '/../..');

/** Disable conflicting extensions. */
if( extension_loaded('xdebug') )
{
  xdebug_disable();
}

/** Add lib/vendor to include path. */
set_include_path(
    get_include_path() . PATH_SEPARATOR
  . $basedir . '/lib/vendor'
);

/** Do not include this file in stack traces if/when tests fail. */
PHP_CodeCoverage_Filter::getInstance()->addFileToBlacklist(__FILE__, 'PHPUNIT');