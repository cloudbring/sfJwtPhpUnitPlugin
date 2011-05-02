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

/** Base functionality for fixture file loaders.
 *
 * @author Phoenix Zerin <phoenix.zerin@jwt.com>
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