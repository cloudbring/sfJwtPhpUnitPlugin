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

/** Exposes the application logger for inspecting log messages while testing.
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test.browser.plugin
 */
class Test_Browser_Plugin_Logger extends Test_Browser_Plugin
{
  protected
    /** @var Test_Browser_Listener_VarLogger */
    $_listener;

  /** Initialize the plugin.
   */
  public function initialize(  )
  {
    $this->_listener = new Test_Browser_Listener_VarLogger();
    $this->addListener($this->_listener);
  }

  /** Returns the name of the accessor that will invoke this plugin.
   *
   * For example, if this method returns 'getMagic', then the plugin can be
   *  invoked in a test case by calling $this->_browser->getMagic().
   *
   * @return string
   */
  public function getMethodName()
  {
    return 'getLogger';
  }

  /** Invokes the plugin.
   *
   * @return Test_Browser_Plugin_Logger($this)
   */
  public function invoke(  )
  {
    if( ! $this->hasEncapsulatedObject() )
    {
      $this->setEncapsulatedObject($this->_listener->getLogger());
    }

    return $this;
  }

  /** Returns a generic string representation of the object.
   *
   * @return string
   */
  public function __toString(  )
  {
    $messages = array();

    $logs   = $this->getEncapsulatedObject()->getLogs();
    $digits = strlen(count($logs));

    $i = 0;
    foreach( $logs as $log )
    {
      $messages[] = sprintf(
        '%s) [%s] %s: (%s) %s',
          str_pad(++$i, $digits, ' ', STR_PAD_LEFT),
          date('Y-m-d H:i:s', $log['time']),
          $log['priority_name'],
          $log['type'],
          $log['message']
      );
    }

    return implode(PHP_EOL, $messages);
  }
}