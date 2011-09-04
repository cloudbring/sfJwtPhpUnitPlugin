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

/** An assertion constraint that checks a the Test_Browser's status code, with
 *    reporting of error codes on failure.
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test.constraint
 */
class Test_Constraint_StatusCodeEquals extends PHPUnit_Framework_Constraint
{
  protected
    $_expected;

  /** Init the class instance.
   *
   * @param int $expected
   *
   * @return void
   */
  public function __construct( $expected )
  {
    if( ! ctype_digit((string) $expected) )
    {
      throw PHPUnit_Util_InvalidArgumentHelper::factory(0, 'int');
    }

    $this->_expected = $expected;
  }

  /** Checks the status code.
   *
   * @param Test_Browser $browser
   *
   * @return bool
   */
  public function evaluate( $browser )
  {
    if( ! ($browser instanceof Test_Browser) )
    {
      throw PHPUnit_Util_InvalidArgumentHelper::factory(0, 'Test_Browser');
    }

    return ($browser->getResponse()->getStatusCode() == $this->_expected);
  }

  /** Returns a generic string representation of the object.
   *
   * @return string
   */
  public function toString()
  {
    return sprintf('is equal to <int:%d>', $this->_expected);
  }

  /** Appends relevant error message information to a failed status check.
   *
   * @param Test_Browser  $browser
   * @param string        $message
   * @param bool          $not
   */
  protected function failureDescription( $browser, $message, $not )
  {
    $code = $browser->getResponse()->getStatusCode();

    $message = parent::failureDescription($code, $message, $not);

    /* See if there's an error we can report. */
    if( ! $error = (string) $browser->getError() )
    {
      $error = Zend_Http_Response::responseCodeAsText($code);
    }

    if( $error != '' )
    {
      return (
        (substr($message, -1) == '.')
          ? sprintf('%s (error: %s).', substr($message, 0, -1), $error)
          : sprintf('%s (error: %s)', $message, $error)
      );
    }

    return $message;
  }
}