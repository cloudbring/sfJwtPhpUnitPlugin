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

/** Creates a safe environment for executing PHP fixture files.
 *
 * @author Phoenix Zerin <phoenix.zerin@jwt.com>
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test
 */
abstract class Test_FixtureLoader_Loader_Php_Harness extends Test_Harness
{
  /* @access private so that they are not accessible to fixture files. */
  private
    $_fixtureLoader;

  /** Init the class instance.
   *
   * @param Test_FixtureLoader  $FixtureLoader
   * @param string              $file
   *
   * @return void
   */
  public function __construct( Test_FixtureLoader $FixtureLoader, $file )
  {
    /* No, you can't run __construct() from within a fixture file! */
    if( ! $this->isExecuting() )
    {
      $this->_fixtureLoader = $FixtureLoader;
      parent::__construct($file);
    }
  }

  /** Loads an additional fixture file.
   *
   * @param string $file
   * @param bool   $force
   *
   * @return mixed
   */
  public function loadFixture( $file, $force = false )
  {
    return $this->_fixtureLoader->loadFixture($file, $force);
  }

  /** Defines a constant if it is not already defined.
   *
   * @param string $name
   * @param mixed  $value
   *
   * @return mixed $value
   */
  public function define( $name, $value )
  {
    if( ! defined($name) )
    {
      define($name, $value);
    }

    return constant($name);
  }

  /** Generic accessor.
   *
   * @param string $var
   *
   * @return mixed
   */
  public function __get( $var )
  {
    return $this->_fixtureLoader->$var;
  }

  /** Generic modifier.
   *
   * @param string $var
   * @param mixed  $val
   *
   * @return mixed $val
   */
  public function __set( $var, $val )
  {
    return $this->_fixtureLoader->$var = $val;
  }

  /** Generic isset() handler.
   *
   * @param string $var
   *
   * @return bool
   */
  public function __isset( $var )
  {
    return isset($this->_fixtureLoader->$var);
  }

  /** Generic unset() handler.
   *
   * @param string $var
   *
   * @return void
   */
  public function __unset( $var )
  {
    unset($this->_fixtureLoader->$var);
  }
}