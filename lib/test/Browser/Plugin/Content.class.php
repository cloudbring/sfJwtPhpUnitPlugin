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

/** Accesses browser response content.
 *
 * @author Phoenix Zerin <phoenix.zerin@jwt.com>
 * 
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test.browser.plugin
 */
class Test_Browser_Plugin_Content extends Test_Browser_Plugin
{
  private
    $_content;

  /** Returns the name of the accessor that will invoke this plugin.
   *
   * For example, if this method returns 'getMagic', then the plugin can be
   *  invoked in a test case by calling $this->_browser->getMagic().
   *
   * @return string
   */
  public function getMethodName(  )
  {
    return 'getContent';
  }

  /** Initialize the plugin.
   *
   * @return void
   */
  public function initialize(  )
  {
    parent::initialize();
    $this->_content = null;
  }

  /** Returns a reference to the response content.
   *
   * @return Test_Browser_Plugin_Content($this)
   */
  public function invoke(  )
  {
    if( ! isset($this->_content) )
    {
      $this->_content = $this->getBrowser()->getResponse()->getContent();
    }

    return $this;
  }

  /** Return the response content as a string.
   *
   * @return string
   */
  public function __toString(  )
  {
    return (string) $this->_content;
  }

  /** Parse content as JSON.
   *
   * @param bool $assoc If true, JS objects will be converted to associative
   *  arrays instead of stdClass instances.
   *
   * @return mixed
   * @throws RuntimeException if the response content is not well-formed.
   */
  public function decodeJson( $assoc = false )
  {
    $res = json_decode($this->_content, $assoc);

    if( $res === null and $this->_content != json_encode(null) )
    {
      /* Output the content in the Exception for easy debugging. */
      throw new RuntimeException(sprintf(
        "Invalid JSON Content:\n\n%s",
          $this->_content
      ));
    }

    return $res;
  }

  /** Parse content as serialize()'d.
   *
   * @return mixed
   * @throws RuntimeException if the response content is not well-formed.
   */
  public function unserialize(  )
  {
    $res = @unserialize($this->_content);

    if( $res === false and $this->_content !== serialize(false) )
    {
      throw new RuntimeException(sprintf(
        "Invalid serialized content:\n\n%s",
          $this->_content
      ));
    }

    return $res;
  }

  /** Returns elements in the response DOM that match a jQuery-style selector.
   *
   * @param string $selector
   *
   * @return array(sfDomCssSelector)
   */
  public function select( $selector )
  {
    return
      $this->getBrowser()->getResponseDomCssSelector()->matchAll($selector);
  }
}