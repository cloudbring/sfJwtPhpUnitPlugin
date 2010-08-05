<?php
/** Base functionality for PHPUnit-related tasks.
 *
 * @package jwt
 * @subpackage lib.task
 */
abstract class BasePhpunitTask extends sfBaseTask
{
  protected
    $_type   = '';

  public function configure(  )
  {
    $this->namespace = 'phpunit';

    $this->addOptions(array(
      new sfCommandOption(
        'filter',
        'f',
        sfCommandOption::PARAMETER_REQUIRED,
        'Regex used to filter tests; only tests matching the filter will be run.',
        null
      ),

      new sfCommandOption(
        'groups',
        'g',
        sfCommandOption::PARAMETER_REQUIRED,
        'Only run tests from the specified group(s).',
        null
      ),

      new sfCommandOption(
        'verbose',
        'v',
        sfCommandOption::PARAMETER_NONE,
        'If set, PHPUnit will output additional information (e.g. test names).',
        null
      )
    ));
  }

  public function execute( $args = array(), $opts = array() )
  {
    $this->_runTests(
      $this->_type,
      $this->_validatePhpUnitInput($args, $opts)
    );
  }

  /** Runs all tests of a given type.
   *
   * @param string $type    ('unit', 'functional', '') If empty, runs all tests.
   * @param array  $options
   *
   * @return void
   */
  protected function _runTests( $type = '', array $options = array() )
  {
    $basedir = sfConfig::get('sf_plugins_dir') . '/sfJwtPhpUnitPlugin';
    require_once $basedir . '/test/bootstrap/phpunit.php';

    /* Run the project bootstrap file, if one exists. */
    $init = sfConfig::get('sf_root_dir') . '/test/bootstrap/phpunit.php';
    if( is_file($init) )
    {
      $Harness = new Test_Harness($init);
      $Harness->execute();
    }

    /* Do not list infrastructure directories in test failure backtraces. */
    $blacklist = array(
      $basedir . '/lib/test',
      realpath(dirname(__FILE__) . '/..'),
      sfCoreAutoload::getInstance()->getBaseDir()
    );
    foreach( $blacklist as $dir )
    {
      PHPUnit_Util_Filter::addDirectoryToFilter($dir);
    }

    PHPUnit_Util_Filter::addFileToFilter(
      sfConfig::get('sf_root_dir') . '/symfony'
    );

    require_once 'PHPUnit/TextUI/TestRunner.php';
    $Runner = new PHPUnit_TextUI_TestRunner();

    $Suite = new PHPUnit_Framework_TestSuite(ucfirst($this->_type) . ' Tests');
    $Suite->addTestFiles($this->_findTestFiles($type));

    try
    {
      $Runner->doRun($Suite, $options);
    }
    catch( PHPUnit_Framework_Exception $e )
    {
      $this->logSection('phpunit', $e->getMessage());
    }
  }

  /** Generates a list of test files.
   *
   * @param string $type ('unit', 'functional') If empty, all tests returned.
   *
   * @return array(string)
   */
  protected function _findTestFiles( $type = '' )
  {
    if( $type == '' )
    {
      return array_merge(
        $this->_findTestFiles('unit'),
        $this->_findTestFiles('functional')
      );
    }
    else
    {
      $base = sfConfig::get('sf_root_dir') . '/test/';

      return sfFinder::type('file')
        ->name('*.php')
        ->in($base . $type);
    }
  }

  /** Compiles arguments and options into a single array.
   *
   * @param array $args
   * @param array $opts     Note:  in a conflict, options override arguments.
   * @param array $defaults
   *
   * @return array
   */
  protected function _validateInput( array $args, array $opts, array $defaults = array() )
  {
    return array_merge(
      $defaults,
      array_filter($args, array($this, '_isset')),
      array_filter($opts, array($this, '_isset'))
    );
  }

  /** Extracts PHPUnit-specific arguments/options.
   *
   * @param array $args
   * @param array $opts
   *
   * @return array
   */
  protected function _validatePhpUnitInput( array $args, array $opts )
  {
    $allowed = array(
      'colors'      => true,
      'filter'      => null,
      'groups'      => null,
      'verbose'     => false
    );

    $params = array_intersect_key(
      $this->_validateInput($args, $opts, $allowed),
      $allowed
    );

    foreach( $params as $key => &$val )
    {
      if( isset($allowed[$key]) )
      {
        settype($val, gettype($allowed[$key]));
      }
    }

    /* Special case:  groups has to be an array. */
    if( isset($params['groups']) )
    {
      $params['groups'] = preg_split('/\s*,\s*/', (string) $params['groups']);
    }

    return $params;
  }

  /** Used as a callback to array_filter() _validateInput() for PHP < 5.3.
   *
   * @param mixed $val
   *
   * @return bool
   */
  protected function _isset( $val )
  {
    return isset($val);
  }
}