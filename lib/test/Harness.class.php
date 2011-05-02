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

/** Creates a safe environment for executing PHP scripts.
 *
 * @author Phoenix Zerin <phoenix.zerin@jwt.com>
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test
 */
class Test_Harness
{
  /* @access private so that they are not accessible to included files. */
  private
    $_file,
    $_isExecuting;

  /** Init the class instance.
   *
   * @param string              $file
   *
   * @return void
   */
  public function __construct( $file )
  {
    /* No, you can't run __construct() from within a fixture file! */
    if( ! $this->isExecuting() )
    {
      $this->_file = $file;
    }
  }

  /** Accessor for $_isExecuting.
   *
   * @return bool
   */
  public function isExecuting(  )
  {
    return $this->_isExecuting;
  }

  /** Exeucute the fixture file.
   *
   * @return mixed
   */
  public function execute(  )
  {
    if( ! $this->isExecuting() )
    {
      $this->_isExecuting = true;
      $res = require $this->_file;
      $this->_isExecuting = false;
      return $res;
    }
  }
}