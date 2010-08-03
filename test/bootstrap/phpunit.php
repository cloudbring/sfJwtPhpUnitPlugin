<?php
/** Init environment for working with PHPUnit tests.
 *
 * @package cms
 * @subpackage test
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

require_once 'PHPUnit/Framework.php';

/** Do not include this file in stack traces if/when tests fail. */
PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');