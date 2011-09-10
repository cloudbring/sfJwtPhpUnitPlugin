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

/** Used to extend the functionality of Test_Browser.
 *
 * @author Phoenix Zerin <phoenix.zerin@jwt.com>
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test.browser
 */
abstract class Test_Browser_Plugin extends Test_ObjectWrapper
{
  private
    /** @var sfBrowserBase */
    $_browser;

  /** Returns the name of the accessor that will invoke this plugin.
   *
   * For example, if this method returns 'getMagic', then the plugin can be
   *  invoked in a test case by calling $this->_browser->getMagic().
   *
   * @return string
   */
  abstract public function getMethodName(  );

  /** Invokes the plugin.
   *
   * @param mixed,...
   *
   * @return mixed
   */
  abstract public function invoke( /* $param, ... */ );

  /** Initialize the plugin.
   *
   * This gets called when the plugin is instantiated and before every browser
   *  request.  It should clear out any values from the previous request.
   *
   * @return void
   */
  public function initialize(  )
  {
    $this->setEncapsulatedObject(null);
  }

  /** Init the class instance.
   *
   * @param sfBrowserBase $Browser
   *
   * @return void
   */
  final public function __construct( sfBrowserBase $Browser )
  {
    $this->_browser = $Browser;
    $this->initialize();
  }

  /** Accessor for the corresponding browser object.
   *
   * @return sfBrowserBase
   */
  public function getBrowser(  )
  {
    return $this->_browser;
  }

  /** Adds an event listener to the browser object.
   *
   * @param Test_Browser_Listener $listener
   *
   * @return Test_Browser_Plugin($this)
   */
  public function addListener( Test_Browser_Listener $listener )
  {
    foreach( $listener->getEventNames() as $event )
    {
      $this->getBrowser()->addListener($event, array($listener, 'invoke'));
    }

    return $this;
  }

  /** Given a plugin name, attempts to determine the correct corresponding
   *   classname.
   *
   * @param string  $name
   * @param bool    $addPrefix If true, the default classname prefix for
   *                            plugin classes ("Test_Browser_Plugin_") will be
   *                            added if $name doesn't match a valid plugin
   *                            class.
   *
   * @return string
   * @throws InvalidArgumentException if $name can't be sanitized.
   */
  static public function sanitizeClassname( $name, $addPrefix = true )
  {
    if( ! is_string($name) )
    {
      throw new InvalidArgumentException(sprintf(
        'Invalid %s encountered; string expected.',
          is_object($name) ? get_class($name) : gettype($name)
      ));
    }

    if( class_exists($name) )
    {
      if( is_subclass_of($name, __CLASS__) )
      {
        return $name;
      }
      elseif( ! $addPrefix )
      {
        throw new InvalidArgumentException(sprintf(
          '%s is not a valid %s class.',
            $name,
            __CLASS__
        ));
      }
    }
    elseif( ! $addPrefix )
    {
      throw new InvalidArgumentException(sprintf(
        'No such class "%s".',
          $name
      ));
    }

    /* If we get to this point, $name is not a valid plugin class, but
     *  $addPrefix is true, so add the classname prefix and try again.
     */
    return self::sanitizeClassname(__CLASS__ . '_' . ucfirst($name), false);
  }
}
