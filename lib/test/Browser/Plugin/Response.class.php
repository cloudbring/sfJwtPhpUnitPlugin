<?php
/** Extends browser response functionality.
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test.browser.plugin
 */
class Test_Browser_Plugin_Response extends Test_Browser_Plugin
{
  /** Returns the name of the accessor that will invoke this plugin.
   *
   * For example, if this method returns 'getMagic', then the plugin can be
   *  invoked in a test case by calling $this->_browser->getMagic().
   *
   * @return string
   */
  public function getMethodName(  )
  {
    return 'getResponse';
  }

  /** Returns a reference to the response object from the browser context.
   *
   * @return Test_Browser_Plugin_Response($this)
   */
  public function invoke(  )
  {
    if( ! $this->hasEncapsulatedObject() )
    {
      $this->setEncapsulatedObject($this->getBrowser()->getResponse());
    }

    return $this;
  }

  /** Returns whether the response was redirected.
   *
   * @return bool
   */
  public function isRedirected(  )
  {
    return $this->hasHttpHeader('location');
  }

  /** Returns the URL that the response was redirected to.
   *
   * @return string|null
   */
  public function getRedirectUrl(  )
  {
    return $this->getHttpHeader('location');
  }
}