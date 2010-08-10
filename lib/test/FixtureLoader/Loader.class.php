<?php
/** Base functionality for fixture file loaders.
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test
 */
abstract class Test_FixtureLoader_Loader
{
  private
    $_parent;

  /** Init the class instance.
   *
   * @param Test_FixtureLoader $Parent
   *
   * @return void
   */
  public function __construct( Test_FixtureLoader $Parent )
  {
    $this->_parent = $Parent;
  }

  /** Accessor for $_parent.
   *
   * @return Test_FixtureLoader
   */
  public function getParent(  )
  {
    return $this->_parent;
  }

  /** Loads and evaluates a fixture file.
   *
   * @param string $fixture
   *
   * @return mixed
   */
  public function loadFixture( $fixture )
  {
    $dir = sfConfig::get('sf_test_dir').'/fixtures';
    $target = realpath($dir . '/' . $fixture);

    if( ! $target )
    {
      throw new Exception(sprintf(
        'Fixture file "%s" does not exist in %s.',
          $fixture,
          $dir
      ));
    }

    if( ! is_readable($target) )
    {
      throw new Exception(sprintf(
        'Fixture file "%s" is not readable.',
          $target
      ));
    }

    if( strpos($target, $dir) !== 0 )
    {
      throw new Exception(sprintf(
        'Fixture file "%s" is not in allowed directory %s.',
          $fixture,
          $dir
      ));
    }

    return $this->_loadFile($target);
  }

  /** Does the actual loading and evaluating of a fixture file.
   *
   * @param string $file Absolute path to the file.
   *
   * @return mixed
   */
  abstract protected function _loadFile( $file );
}