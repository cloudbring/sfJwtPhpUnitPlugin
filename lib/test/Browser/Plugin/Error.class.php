<?php
/** Exposes the Exception generated during a failed request.
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test.browser.plugin
 */
class Test_Browser_Plugin_Error extends Test_Browser_Plugin
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
    return 'getError';
  }

  /** Returns a reference to the uncaught exception from the application.
   *
   * @return Test_Browser_Plugin_Error($this)
   */
  public function invoke(  )
  {
    if( ! $this->hasEncapsulatedObject() )
    {
      if( ! $this->getBrowser()->checkCurrentExceptionIsEmpty() )
      {
        $this->setEncapsulatedObject(
          $this->getBrowser()->getCurrentException()
        );
      }
    }

    return $this;
  }

  /** Return a string representation of the error (i.e., the error message).
   *
   * @return string
   */
  public function __toString(  )
  {
    return (string) $this->getMessage();
  }
}