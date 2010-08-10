<?php
/** Loads a .yml test fixture.
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test
 */
class Test_FixtureLoader_Loader_Yml extends Test_FixtureLoader_Loader
{
  /** Loads and evaluates a fixture file.
   *
   * @param string $file Absolute path to the file.
   *
   * @return void
   */
  protected function _loadFile( $file )
  {
    return Doctrine_Core::loadData($file, true);
  }
}