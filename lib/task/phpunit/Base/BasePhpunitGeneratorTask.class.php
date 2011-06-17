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
  const DEFAULT_PACKAGE = 'symfony';

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
      $properties = parse_ini_file(
        $this->_getBaseDir('config') . 'properties.ini',
        true
      );

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
}