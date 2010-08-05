<?php
/** Runs all tests in a specified file(s).
 *
 * @package jwt
 * @subpackage lib.task
 */
class FileTestsTask extends BasePhpUnitTask
{
  protected
    $_files;

  public function configure(  )
  {
    parent::configure();

    $this->addArguments(array(
      new sfCommandArgument(
        'file',
        sfCommandArgument::REQUIRED | sfCommandArgument::IS_ARRAY,
        'Path to the file(s) to load, relative to the base tests directory.',
        null
      )
    ));

    $this->name = 'file';

    $this->briefDescription = 'Runs all PHPUnit tests in the specified file path(s).';

    $this->detailedDescription = <<<END
Runs all PHPUnit tests in the specified file path(s).

File paths should be specified relative to the base tests directory in your
  project.  You can omit the '.php' extension, but you must provide filenames,
  not directory names.

For example, suppose your tests directory looked like this:

tests/
  functional/
    backend/
      main/
        index.php
    frontend/
      account/
        profile.php
      main/
        about.php
        index.php
      contact
        index.php
  unit/
    lib/
      validator/
        CustomValidator.php
    model/
      Guestbook.php
      Profile.php

If you wanted to run the test for /account/profile and its dependencies, you
  might invoke phpunit:file like this:

symfony phpunit:file unit/model/Profile functional/frontend/account/profile
END;

    $this->_type = 'custom';
  }

  public function execute( $args = array(), $opts = array() )
  {
    $this->_files = $args['file'];

    $this->_runTests(
      $this->_type,
      $this->_validatePhpUnitInput(array(), $opts)
    );
  }

  /** Generates a list of test files.
   *
   * @param string $type ('unit', 'functional') If empty, all tests returned.
   *
   * @return array(string)
   */
  protected function _findTestFiles( $type = '' )
  {
    $base = sfConfig::get('sf_root_dir') . '/test/';

    $files = array();
    foreach( $this->_files as $path )
    {
      /* '.php' suffix is optional in $args. */
      if( substr($path, -4) !== '.php' )
      {
        $path .= '.php';
      }

      if( $real = realpath($base . '/' . $path) )
      {
        $isValid = (
              strpos($real, $base . 'unit/')        === 0
          or  strpos($real, $base . 'functional/')  === 0
        );

        if( $isValid )
        {
          $files[] = $real;
        }
        else
        {
          throw new InvalidArgumentException(sprintf(
            '"%s" is located outside a valid test directory.',
              $real
          ));
        }
      }
      else
      {
        throw new InvalidArgumentException(sprintf(
          'File does not exist:  %s.',
            $base . ltrim($path, '/')
        ));
      }
    }

    return $files;
  }
}