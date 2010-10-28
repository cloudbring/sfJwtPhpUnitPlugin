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

  /** Returns the message of an uncaught exception, if one exists.
   *
   * @param bool $asObject If true, returns the Exception instance itself.
   *
   * @return string|Exception|null
   */
  public function invoke( $asObject = false )
  {
    if( ! $this->getBrowser()->checkCurrentExceptionIsEmpty() )
    {
      /* @var $Exception Exception */
      if( $Exception = $this->getBrowser()->getCurrentException() )
      {
        return $asObject ? $Exception : $Exception->getMessage();
      }
    }

    return $asObject ? null : '';
  }
}