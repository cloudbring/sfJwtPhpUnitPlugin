<?php
/** Extends PHPUnit for use with Symfony 1.4.
 *
 * Note:  This class is designed to work with Symfony 1.4 and might not work
 *  properly with other versions of Symfony.
 *
 * @package jwt
 * @subpackage lib.test
 */
abstract class Test_Case extends PHPUnit_Framework_TestCase
{
  const
    ERR_HEADER = '*** Halting execution to prevent corrupting production data! ***';

  static private
    $_appConfig,
    $_dbRebuilt,
    $_dbNameCheck,
    $_dbTableNames,
    $_uploadsDirCheck;

  private
    $_fixtureLoader;

  /** (Global) Init test environment.
   *
   * Note that test case subclasses should use _setUp().
   *
   * @return void
   */
  final public function setUp(  )
  {
    $this->_fixtureLoader = new Test_FixtureLoader();

    $this->flushDatabase();
    $this->validateUploadsDir();

    $this->_init();
    $this->_setUp();
  }

  /** (Global) Clean up test environment.
   *
   * Note that test case subclasses should use _tearDown().
   *
   * @return void
   */
  final public function tearDown(  )
  {
    $this->_tearDown();
  }

  /** Accessor for a variable set in a fixture.
   *
   * @param string $key
   *
   * @return mixed
   */
  protected function getFixtureVar( $key )
  {
    return $this->_fixtureLoader->$key;
  }

  /** Loads a text fixture into the database.
   *
   * @param string $fixture The name of the fixture file (e.g., test_data.yml).
   * @param bool   $force   If true, the fixture will be loaded even if it has
   *  already been loaded.
   *
   * @return void
   */
  protected function loadFixture( $fixture, $force = false )
  {
    return $this->_fixtureLoader->loadFixture($fixture, $force);
  }

  /** Flush the database and reload base fixtures.
   *
   * @return Test_Case $this
   */
  protected function flushDatabase(  )
  {
    self::verifyTestDbConnection();

    $db = self::getDbConnection();

    /* The first time we run a test case, drop and rebuild the database.
     *
     * After that, we can simply truncate all tables for speed.
     */
    if( empty(self::$_dbRebuilt) )
    {
      $db->dropDatabase();
      $db->createDatabase();

      Doctrine_Core::loadModels(
        sfConfig::get('sf_lib_dir').'/model/doctrine',
        Doctrine_Core::MODEL_LOADING_CONSERVATIVE
      );
      Doctrine_Core::createTablesFromArray(Doctrine_Core::getLoadedModels());

      self::$_dbRebuilt = true;
    }
    else
    {
      if( ! isset(self::$_tableNames) )
      {
        self::$_dbTableNames = array();
        foreach( $db->execute('SHOW TABLES') as $row )
        {
          self::$_dbTableNames[] = $db->quoteIdentifier($row[0]);
        }
      }

      $db->execute('SET foreign_key_checks = 0');

      foreach( self::$_dbTableNames as $table )
      {
        $db->execute('TRUNCATE ' . $table);
      }

      $db->execute('SET foreign_key_checks = 1');
    }

    Doctrine_Core::loadData(sfConfig::get('sf_data_dir').'/fixtures');

    $this->_fixtureLoader
      ->flushFixtures()
      ->loadFixture(
          sfFinder::type('file')
            ->name('_global.*')
            ->relative()
            ->in(sfConfig::get('sf_root_dir') . '/test/fixtures')
        );

    return $this;
  }

  /** Accessor for $_appConfig.
   *
   * @return sfApplicationConfiguration
   */
  static protected function getAppConfig(  )
  {
    if( ! isset(self::$_appConfig) )
    {
      if( sfContext::hasInstance() )
      {
        self::$_appConfig = sfContext::getInstance()->getConfiguration();
      }
      else
      {
        self::$_appConfig = ProjectConfiguration::getApplicationConfiguration(
          'frontend',
          'test',
          true,
          realpath(dirname(__FILE__) . '/../..')
        );
        sfContext::createInstance(self::$_appConfig);
      }
    }

    return self::$_appConfig;
  }

  /** Gets the Doctrine connection, initializing it if necessary.
   *
   * @return Doctrine_Connection
   */
  static protected function getDbConnection(  )
  {
    try
    {
      return Doctrine_Manager::connection();
    }
    catch( Doctrine_Connection_Exception $e )
    {
      new sfDatabaseManager(self::getAppConfig());
      return Doctrine_Manager::connection();
    }
  }

  /** Verifies that we are not connected to the production database.
   *
   * @param bool $force
   *
   * @return void Triggers an error if our database connection is unsafe for
   *  testing.
   */
  static protected function verifyTestDbConnection( $force = false )
  {
    if( ! self::$_dbNameCheck or $force )
    {
      self::_assertTestEnvironment();

      $config = sfConfigHandler::replaceConstants(sfYaml::load(
        sfConfig::get('sf_root_dir') . '/config/databases.yml'
      ));

      /* Check to see if a test database has been specified. */
      if( empty($config['test']['doctrine']['param']['dsn']) )
      {
        self::_halt('Please specify a "test" database in databases.yml.');
      }

      $test = $config['test']['doctrine']['param']['dsn'];

      $prod =
        isset($config['prod']['doctrine']['param']['dsn'])
          ? $config['prod']['doctrine']['param']['dsn']
          : $config['all']['doctrine']['param']['dsn'];

      /* Check to see if a *separate* test database has been specified. */
      if( $prod == $test )
      {
        self::_halt('Please specify a *separate* "test" database in databases.yml.');
      }

      /* Check to see that the active connection is using the correct DSN. */
      if( self::getDbConnection()->getOption('dsn') != $test )
      {
        self::_halt('Doctrine connection is not using test DSN!');
      }

      self::$_dbNameCheck = true;
    }
  }

  /** Validates the uploads directory to ensure we're not going to inadvertently
   *   put test uploads in the wrong place and/or delete production files.
   *
   * @param bool $force
   *
   * @return string path to uploads directory.
   */
  static protected function validateUploadsDir( $force = false )
  {
    if( ! self::$_uploadsDirCheck or $force )
    {
      self::_assertTestEnvironment();

      $config = sfConfigHandler::replaceConstants(sfYaml::load(
        sfConfig::get('sf_app_dir') . '/config/settings.yml'
      ));

      /* Determine whether a test uploads directory has been specified. */
      if( ! isset($config['test']['.settings']['upload_dir']) )
      {
        self::_halt('Please specify a "test" value for sf_upload_dir in settings.yml.');
      }

      $test = $config['test']['.settings']['upload_dir'];

      /* Make double-sure that we're actually using the test uploads dir. */
      if( sfConfig::get('sf_upload_dir') != $test )
      {
        self::_halt('Symfony is not using the test upload dir setting.  Try symfony cc.');
      }

      /* Determine whether a the test uploads directory is different than the
       *  production one.
       */
      if( isset($config['prod']['.settings']['upload_dir']) )
      {
        $prod = $config['prod']['.settings']['upload_dir'];
      }
      elseif( isset($config['all']['.settings']['upload_dir']) )
      {
        $prod = $config['all']['.settings']['upload_dir'];
      }
      else
      {
        /* Get the default value:  no good way to do this in Symfony 1.4. */
        $prod = sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'uploads';
      }

      if( $prod == $test )
      {
        self::_halt('Please specify a *separate* "test" value for sf_upload_dir in settings.yml.');
      }

      /* Check the directory itself to make sure it's valid. */
      if( ! is_dir($test) )
      {
        self::_halt('Test upload directory (%s) does not exist or is not a directory.', $test);
      }

      if( ! is_writable($test) )
      {
        self::_halt('Test upload directory (%s) is not writable.', $test);
      }

      self::$_uploadsDirCheck = true;
    }
  }

  /** Check to make sure we are using the "test" environment.
   *
   * Throws an error if the check fails to avoid executing test code in a
   *  production environment.
   *
   * @return void
   */
  static protected function _assertTestEnvironment(  )
  {
    self::getAppConfig();
    if( sfConfig::get('sf_environment') != 'test' )
    {
      self::_halt('Please verify that getAppConfig() is specifying the "test" environment.');
    }

    if( sfConfig::get('sf_error_reporting') !== (E_ALL | E_STRICT) )
    {
      self::_halt('error_reporting should be set to %d (%s) in settings.yml.',
        (E_ALL | E_STRICT),
        'E_ALL | E_STRICT' // Split out for easy editing if necessary.
      );
    }
  }

  /** Halts test script execution.
   *
   * @param string    $message Can use sprintf() syntax.
   * @param mixed,...
   *
   * @return void
   */
  static protected function _halt( $message /*, $value,... */ )
  {
    echo
      self::ERR_HEADER, PHP_EOL,
      call_user_func_array('sprintf', func_get_args()), PHP_EOL,
      PHP_EOL;

    /* Explicitly halt execution. */
    exit;
  }

  /** Init test environment.
   *
   * @return void
   */
  protected function _setUp(  )
  {
  }

  /** Clean up test environment.
   *
   * @return void
   */
  protected function _tearDown(  )
  {
  }

  /** Used by subclasses to do any pre-test initialization.
   *
   * @return void
   */
  abstract protected function _init(  );
}