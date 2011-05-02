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

/** Adds domain-specific functionality to sfTestBrowser.
 *
 * Note:  Designed to work with Symfony 1.4.  Might not work properly with later
 *  versions of Symfony.
 *
 * @author Phoenix Zerin <phoenix.zerin@jwt.com>
 *
 * @package jwt
 * @subpackage lib.test
 */
class Test_Browser extends Test_ObjectWrapper
{
  private
    $_isCalled,
    $_plugins;

  /** Init the class instance.
   *
   * @return void
   */
  public function __construct(  )
  {
    $this->_isCalled  = false;
    $this->_plugins   = array();

    $this->setEncapsulatedObject('sfBrowser');

    /* Activate commonly-used plugins. */
    $this->usePlugin(
      'content',
      'error',
      'form',
      'request',
      'response'
    );
  }

  /** Activate a plugin.
   *
   * @param string,... $plugin_name
   *
   * @return Test_Browser($this)
   */
  public function usePlugin( $plugin_name/*, ... */ )
  {
    foreach( func_get_args() as $name )
    {
      if( empty($this->_plugins[$name]) )
      {
        $class = Test_Browser_Plugin::sanitizeClassname($name);

        /* @var $Plugin Test_Browser_Plugin */
        $Plugin = new $class($this->getEncapsulatedObject());

        $this->_plugins[$name] = $Plugin;

        $this->injectDynamicMethod(
          $Plugin->getMethodName(),
          array($Plugin, 'invoke')
        );
      }
    }

    return $this;
  }

  /** Gets a URI.
   *
   * @param string $uri         The URI to fetch
   * @param array  $parameters  The Request parameters
   * @param bool   $changeStack  Change the browser history stack?
   *
   * @return Test_Browser($this)
   */
  public function get( $uri, $parameters = array(), $changeStack = true )
  {
    return $this->call($uri, 'get', $parameters, $changeStack);
  }

  /** Posts a URI.
   *
   * @param string $uri         The URI to fetch
   * @param array  $parameters  The Request parameters
   * @param bool   $changeStack  Change the browser history stack?
   *
   * @return Test_Browser($this)
   */
  public function post( $uri, $parameters = array(), $changeStack = true )
  {
    return $this->call($uri, 'post', $parameters, $changeStack);
  }

  /** Execute a browser request.
   *
   * @param string $uri          The URI to fetch
   * @param string $method       The request method
   * @param array  $parameters   The Request parameters
   * @param bool   $changeStack  Change the browser history stack?
   *
   * @return Test_Browser($this)
   */
  public function call( $uri, $method = 'get', $parameters = array(), $changeStack = true )
  {
    /* @var $Plugin Test_Browser_Plugin */
    foreach( $this->_plugins as $Plugin )
    {
      $Plugin->initialize();
    }

    $this->getEncapsulatedObject()->call(
      $uri,
      $method,
      $parameters,
      $changeStack
    );
    $this->_isCalled = true;

    /* Flush output buffer. */
    ob_end_clean();

    return $this;
  }

  /** Returns whether the browser has made a request yet.
   *
   * @return bool
   */
  public function isCalled(  )
  {
    return $this->_isCalled;
  }

  /** Handles an attempt to call a non-existent method.
   *
   * @param string $meth
   *
   * @return void
   * @throws BadMethodCallException
   */
  protected function handleBadMethodCall( $meth )
  {
    throw new BadMethodCallException(sprintf(
      'Call to undefined method %s->%s() - did you forget to call usePlugin()?',
        __CLASS__,
        $meth
    ));
  }
}