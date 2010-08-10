<?php
/** Loads a .php test fixture.
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test
 */
class Test_FixtureLoader_Loader_Php extends Test_FixtureLoader_Loader
{
  /** Used to share variables between fixture files. */
  static protected $_vars = array();

  /** Loads and evaluates a fixture file.
   *
   * Note that the fixture file is loaded via include(), which means that $this
   *  is accessible to PHP fixture files.
   *
   * @param string $file Absolute path to the file.
   *
   * @return void
   */
  protected function _loadFile( $file )
  {
    $Harness = new Test_FixtureLoader_Loader_Php_Harness_Safe(
      $this->getParent(),
      $file
    );
    return $Harness->execute();
  }
}