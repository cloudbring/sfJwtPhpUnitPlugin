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

/** Used to inject an sfVarLogger into the application context so that tests
 *    can inspect application log messages.
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test.browser.listener
 */
class Test_Browser_Listener_VarLogger implements Test_Browser_Listener
{
  protected
    /** @var sfVarLogger */
    $_logger;

  /** Returns the events that this listener should be registered for.
   *
   * @return array
   */
  public function getEventNames(  )
  {
    return array('context.load_factories');
  }

  /** Accessor for the logger instance.
   *
   * @return sfVarLogger
   */
  public function getLogger(  )
  {
    return $this->_logger;
  }

  /** Invokes the listener.
   *
   * @param sfEvent $event
   *
   * @return void
   */
  public function invoke( sfEvent $event )
  {
    /** @var $context sfContext */
    $context = $event->getSubject();

    /** @var $logger sfAggregateLogger */
    $logger = $context->getLogger();

    /* Check to see if there is an existing sfVarLogger we can use; this is
     *  preferable to injecting a new one because the pre-existing one will
     *  likely contain additional log messages from factory initialization.
     */
    foreach( $logger->getLoggers() as $log )
    {
      if( $log instanceof sfVarLogger )
      {
        $this->_logger = $log;
        break;
      }
    }

    if( ! $this->_logger )
    {
      $this->_logger = new sfVarLogger($context->getEventDispatcher());
      $logger->addLogger($this->_logger);
    }
  }
}