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

/** Base functionality for all PHPUnit-related tasks.
 *
 * @author Phoenix Zerin <phoenix.zerin@jwt.com>
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.task.phpunit.base
 */
abstract class BasePhpunitTask extends sfBaseTask
{
  public function configure(  )
  {
    $this->namespace = 'phpunit';
  }

  /** Validates that the PHPUnit framework is accessible via PHP's include_path.
   *
   * @return void
   * @throws RuntimeException if it can't find PHPUnit.
   */
  protected function _verifyPhpUnit(  )
  {
    @include_once 'PHPUnit/Autoload.php';
    if( ! class_exists('PHPUnit_Framework_TestCase') )
    {
      throw new RuntimeException(
        'Unable to locate PHPUnit framework.  Please ensure that your include_path can find it:'
          . PHP_EOL . PHP_EOL . get_include_path()
      );
    }
  }

  /** Compiles arguments and options into a single array.
   *
   * @param array $args
   * @param array $opts     Note:  in a conflict, options override arguments.
   * @param array $defaults
   * @param bool  $strict   If true, only keys present in $defaults will be
   *  returned in the final array.
   *
   * @return array
   */
  protected function _consolidateInput(
    array $args,
    array $opts,
    array $defaults = array(),
          $strict   = false
  )
  {
    $res = array_merge(
      $defaults,
      array_filter($args, array($this, '_isset')),
      array_filter($opts, array($this, '_isset'))
    );

    return ($strict ? array_intersect_key($res, $defaults) : $res);
  }

  /** Used as a callback to array_filter() _consolidateInput() for PHP < 5.3.
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