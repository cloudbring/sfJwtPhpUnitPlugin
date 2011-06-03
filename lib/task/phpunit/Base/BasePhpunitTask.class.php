<?php
/**
 * Copyright (c) 2011 J. Walter Thompson dba JWT
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/** Base functionality for PHPUnit-related tasks.
 *
 * @author Phoenix Zerin <phoenix.zerin@jwt.com>
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.task.phpunit.base
 */
abstract class BasePhpunitTask extends sfBaseTask
{
  protected
    $_type,
    $_paths;

  private
    $_basedir;

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
    $this->_runTests($this->_validatePhpUnitInput($args, $opts));
  }

  /** Return the base directory for the plugin.
   *
   * @return string(dirpath)
   */
  protected function _getBasedir(  )
  {
    if( ! isset($this->_basedir) )
    {
      /* I.e., sf_root_dir/plugins/sfJwtPhpUnitPlugin(/lib/task/phpunit/Base) */
      $this->_basedir = realpath(
          dirname(__FILE__)
        . str_repeat(DIRECTORY_SEPARATOR . '..', 4)
      );
    }

    return $this->_basedir;
  }

  /** Runs all tests of a given type.
   *
   * @param array $options PHPUnit options.
   *
   * @return void
   */
  protected function _runTests( array $options = array() )
  {
    $this->_executeGlobalBootstrap();
    $this->_executeProjectBootstrap();

    $this->_populateTraceBlacklist();

    $this->_doRunTests($options);
  }

  /** Initialize the PHPUnit test runner and run tests.
   *
   * @param array $options
   *
   * @return void
   */
  private function _doRunTests( array $options )
  {
    if( $files = $this->_findTestFiles($this->_type, (array) $this->_paths) )
    {
      require_once
          'PHPUnit' . DIRECTORY_SEPARATOR
        . 'TextUI'  . DIRECTORY_SEPARATOR
        . 'TestRunner.php';

      $Runner = new PHPUnit_TextUI_TestRunner();

      $Suite = new PHPUnit_Framework_TestSuite(ucfirst($this->name) . ' Tests');
      $Suite->addTestFiles($files);

      try
      {
        $Runner->doRun($Suite, $options);
      }
      catch( PHPUnit_Framework_Exception $e )
      {
        $this->logSection('phpunit', $e->getMessage());
      }
    }
    else
    {
      $this->logSection('phpunit', 'No tests found.');
    }
  }

  /** Generates a list of test files.
   *
   * @param string  $type ('unit', 'functional') If empty, all tests returned.
   * @param array   $paths Sub-paths within $type to search.  If empty, all
   *  tests under $type returned.
   *
   * @return array(string)
   */
  protected function _findTestFiles( $type = '', array $paths = array() )
  {
    if( ! $paths )
    {
      $paths = array('');
    }

    if( $type == '' )
    {
      return array_merge(
        $this->_findTestFiles('unit', $paths),
        $this->_findTestFiles('functional', $paths)
      );
    }
    else
    {
      $base =
          sfConfig::get('sf_root_dir')  . DIRECTORY_SEPARATOR
        . 'test'                        . DIRECTORY_SEPARATOR
        . $type                         . DIRECTORY_SEPARATOR;

      $files = array();
      foreach( $paths as $path )
      {
        $fullpath = $base . $path;

        /* Don't allow path injection, just in case. */
        if( array_search('..', explode(DIRECTORY_SEPARATOR, $path)) !== false )
        {
          $this->logSection(
            'phpunit',
            sprintf('Skipping unsafe path %s.', $fullpath),
            null,
            'ERROR'
          );

          continue;
        }

        /* If $fullpath points to a file, load it. */
        if( is_file($fullpath) )
        {
          $files[] = $fullpath;
        }

        /* If $fullpath points to a directory, load all files in it. */
        elseif( is_dir($fullpath) )
        {
          $files = array_merge(
            $files,
            sfFinder::type('file')
              ->name('*.php')
              ->in($fullpath)
          );
        }

        /* If $fullpath is the path to a file minus a '.php' or '.class.php'
         *  extension, load the auto-corrected filepath.
         */
        else
        {
          $basename =
              dirname($fullpath) . DIRECTORY_SEPARATOR
            . basename($fullpath, '.php');

          if( is_file($basename . '.php') )
          {
            $files[] = $basename . '.php';
          }
          elseif( is_file($basename . '.class.php') )
          {
            $files[] = $basename . '.class.php';
          }
          else
          {
            $this->logSection(
              'phpunit',
              sprintf('No test files located at %s.', $fullpath)
            );
          }
        }
      }
      return $files;
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

  /** Executes the global PHPUnit bootstrap file.
   *
   * @return void
   */
  protected function _executeGlobalBootstrap(  )
  {
    /* sf_root_dir/plugins/sfJwtPhpUnitPlugin/test/bootstrap/phpunit.php */
    $Harness = new Test_Harness_Safe(
        $this->_getBasedir()  . DIRECTORY_SEPARATOR
      . 'test'                . DIRECTORY_SEPARATOR
      . 'bootstrap'           . DIRECTORY_SEPARATOR
      . 'phpunit.php'
    );
    $Harness->execute();
  }

  /** Execute the project-specific bootstrap file, if it exists.
   *
   * @return void
   */
  protected function _executeProjectBootstrap(  )
  {
    /* sf_root_dir/test/bootstrap/phpunit.php */
    $init =
        sfConfig::get('sf_root_dir')  . DIRECTORY_SEPARATOR
      . 'test'                        . DIRECTORY_SEPARATOR
      . 'bootstrap'                   . DIRECTORY_SEPARATOR
      . 'phpunit.php';

    if( is_file($init) )
    {
      $Harness = new Test_Harness_Safe($init);
      $Harness->execute();
    }
  }

  /** Adds various plugin support files to the blacklist so that they are not
   *   output in test failure traces.
   *
   * @return void
   */
  protected function _populateTraceBlacklist(  )
  {
    $blacklist = array(
      $this->_getBasedir(),                       /* Plugin files.        */
      sfCoreAutoload::getInstance()->getBaseDir() /* Base Symfony files.  */
    );

    foreach( $blacklist as $dir )
    {
      PHP_CodeCoverage_Filter::getInstance()->addDirectoryToBlacklist($dir);
    }

    /* Also ignore the Symfony executable. */
    PHP_CodeCoverage_Filter::getInstance()->addFileToBlacklist(
      sfConfig::get('sf_root_dir') . DIRECTORY_SEPARATOR . 'symfony'
    );
  }
}
