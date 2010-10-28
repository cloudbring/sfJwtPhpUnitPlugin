<?php
/** Exposes the Symfony email logger.
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test.browser.plugin
 */
class Test_Browser_Plugin_Mailer extends Test_Browser_Plugin
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
    return 'getMailer';
  }

  /** Returns a reference to the email logger from the browser context.
   *
   * @return Test_Browser_Plugin_Mailer($this)
   */
  public function invoke(  )
  {
    if( ! $this->hasEncapsulatedObject() )
    {
      $this->setEncapsulatedObject(
        $this->getBrowser()->getContext()->getMailer()->getLogger()
      );
    }

    return $this;
  }
}