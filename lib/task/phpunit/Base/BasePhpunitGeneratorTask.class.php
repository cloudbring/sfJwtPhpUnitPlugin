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

/** Defines base functionality for tasks that generate PHPUnit test cases.
 *
 * @author Phoenix Zerin <phoenix.zerin@jwt.com>
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.task.phpunit.base
 */
abstract class BasePhpunitGeneratorTask extends BasePhpunitTask
{
  /** Default package name if no value can be guessed.
   *
   * @var string
   */
  const
    DEFAULT_PACKAGE = 'symfony';

  private
    /** Caches the result of parsing sf_config_dir/properties.ini. */
    $_projectProperties;

  public function configure(  )
  {
    parent::configure();

    $this->addOptions(array(
      new sfCommandOption(
        'token',
        null,
        sfCommandOption::PARAMETER_REQUIRED | sfCommandOption::IS_ARRAY,
        'Specify custom token names/values in key:value format (e.g., "PACKAGE:MyAwesomeProject").'
      ),

      new sfCommandOption(
        'verbose',
        'v',
        sfCommandOption::PARAMETER_NONE,
        'If set, additional (mostly debugging) information will be output.'
      )
    ));
  }

  /** Copies askeleton file to the specified destination, creating intermediate
   *    directories as needed.
   *
   * @param string(absfilepath) $skeleton
   * @param string(absfilepath) $target
   *
   * @return void
   * @throws RuntimeException if anything goes wrong.
   */
  protected function _copySkeletonFile( $skeleton, $target )
  {
    /* Set up the directory structure if necessary. */
    $fs = $this->getFilesystem();
    if( ! $fs->mkdirs(dirname($target), 0755) )
    {
      throw new RuntimeException(sprintf(
        'Failed to create directory %s.',
          $target
      ));
    }

    /* Copy the skeleton file into place. */
    $fs->copy($skeleton, $target);
    if( ! is_file($target) )
    {
      throw new RuntimeException(sprintf(
        'Failed to copy skeleton class file %s to %s.',
          $skeleton,
          $target
      ));
    }
  }

  /** Returns the location to a skeleton class file, checking for a user-defined
   *    version before defaulting to the stock file included with the plugin.
   *
   * @param string(filename) $filename
   *
   * @return string(absfilepath)
   * @throws RuntimeException if no skeleton file was located.
   */
  protected function _findSkeletonFile( $filename )
  {
    /* Check for custom skeleton file at
     *  sf_data_dir/skeleton/phpunit/unit.php.
     */
    $skeleton = $this->_getBaseDir('data', array('skeleton', 'phpunit')) . $filename;
    if( ! is_file($skeleton) )
    {
      $skeleton = $this->_genPath(array(
        realpath($this->_genPath(array(dirname(__FILE__), '..'))),
        'skeleton',
        $filename
      ), false);

      if( ! is_file($skeleton) )
      {
        throw new RuntimeException(sprintf(
          'Cannot find skeleton class file at %s.',
            $skeleton
        ));
      }
    }

    return $skeleton;
  }

  /** Returns a base directory, normalized (ensures that it ends with a
   *    directory separator).
   *
   * @param string  $name     The name of the sfConfig value to check.
   * @param array   $subdirs  Subdirectories that should be appended (the array
   *  is implode()'d, using directory separators as separators.
   *
   * @return string(abspath)
   */
  protected function _getBaseDir( $name = 'root', array $subdirs = array() )
  {
    array_unshift($subdirs, sfConfig::get('sf_' . $name . '_dir'));
    return $this->_genPath($subdirs);
  }

  /** Injects directory separators into a path so that you don't have to.
   *
   * @param array $dirs
   * @param bool  $isDir  If true, a directory separator will be appended to the
   *  end of the path.
   *
   * @return string(path)
   */
  protected function _genPath( array $dirs, $isDir = true )
  {
    $path = rtrim(implode(DIRECTORY_SEPARATOR, $dirs), DIRECTORY_SEPARATOR);

    if( $isDir )
    {
      $path .= DIRECTORY_SEPARATOR;
    }

    return $path;
  }

  /** Tries to guess the name of the 'main' application.
   *
   * If the "frontend" application exists, it will use that.  Otherwise, the
   *  result is the same as {@see sfBaseTask::getFirstApplication()}.
   *
   * @return string
   */
  protected function _guessDefaultAppName(  )
  {
    $default = 'frontend';
    return (
      $this->_doesAppExist($default)
        ? $default
        : $this->getFirstApplication()
    );
  }

  /** Similar to {@see sfBaseTask::checkAppExists()}, except it always returns a
   *    boolean instead of potentially throwing an exception.
   *
   * @param string $app
   *
   * @return bool
   */
  protected function _doesAppExist( $app )
  {
    return is_dir($this->_getBaseDir('apps') . $app);
  }

  /** Parses the contents of sf_config_dir/properties.ini.
   *
   * @return array
   */
  protected function _getProjectProperties(  )
  {
    if( ! isset($this->_projectProperties) )
    {
      $this->_projectProperties = parse_ini_file(
        $this->_getBaseDir('config') . 'properties.ini',
        true
      );
    }

    return $this->_projectProperties;
  }

  /** Determines the package name of a class.
   *
   * @param ReflectionClass $ref
   *
   * @return string Class package, project name, or 'symfony'.
   */
  protected function _guessPackageName( ReflectionClass $ref )
  {
    if( preg_match('/^\s*\*\s*@package\s+(.+)\s*$/m', $ref->getDocComment(), $matches) )
    {
      return $matches[1];
    }
    else
    {
      $properties = $this->_getProjectProperties();

      return (
        empty($properties['symfony']['name'])
          ? self::DEFAULT_PACKAGE
          : $properties['symfony']['name']
      );
    }
  }

  /** Determines the subpackage name of a class.
   *
   * @param ReflectionClass $ref
   * @param string          $prefix
   *
   * @return string Class subpackage, dotted file path, or ''.
   */
  protected function _guessSubpackageName( ReflectionClass $ref, $prefix = '' )
  {
    if( preg_match('/^\s*\*\s*@subpackage\s+(.+)\s*$/m', $ref->getDocComment(), $matches) )
    {
      $subpackage = $matches[1];
    }
    else
    {
      $subpackage = str_replace(DIRECTORY_SEPARATOR, '.',
        dirname(
          /* Strip the path to the class file, removing sf_root_dir. */
          substr($ref->getFileName(), strlen($this->_getBaseDir()))
        )
      );
    }

    /* Prepend prefix, removing trailing '.' if $subpackage is empty. */
    if( $prefix != '' )
    {
      $subpackage = rtrim($prefix . '.' . $subpackage, '.');
    }

    return $subpackage;
  }

  /** Determines the author(s) of a class.
   *
   * If a class has no author tags in its docblock, the project author will be
   *  returned instead.
   *
   * @param ReflectionClass $ref
   * @param bool            $multiple
   *
   * @return array(string)|string if $multiple is false, only the first author
   *  will be returned.
   */
  protected function _guessAuthorNames( ReflectionClass $ref, $multiple = true )
  {
    if( ! $authors = $this->_getTagValues('author', $ref->getDocComment()) )
    {
      $properties = $this->_getProjectProperties();

      $authors = (
        empty($properties['symfony']['author'])
          ? array()
          : array($properties['symfony']['author'])
      );
    }

    return $multiple ? $authors : reset($authors);
  }

  /** Parses all values for a particular tag in a docblock.
   *
   * @param string $tag
   * @param string $docblock
   *
   * @return array(string)
   */
  protected function _getTagValues( $tag, $docblock )
  {
    $regex = sprintf(
      '/^\s*\*\s*@%s\s+(.+)\s*$/m',
        preg_quote($tag, '/')
    );

    preg_match_all($regex, $docblock, $matches);
    return $matches[1];
  }

  /** Parses and validates custom tokens for the skeleton test case.
   *
   * @param array $tokens
   *
   * @return array(string(token) => string)
   */
  protected function _parseCustomTokens( array $tokens )
  {
    $customTokens = array();

    foreach( $tokens as $token )
    {
      $split = explode(':', $token, 2);
      if( isset($split[1]) )
      {
        $customTokens[strtoupper($split[0])] = $split[1];
      }
      else
      {
        throw new InvalidArgumentException(sprintf(
          'Invalid token format for "%s"; "key:value" expected.',
            $token
        ));
      }
    }

    return $customTokens;
  }
}