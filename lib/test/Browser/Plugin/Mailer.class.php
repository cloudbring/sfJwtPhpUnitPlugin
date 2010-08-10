<?php
/** Adds features for working with test browser emails.
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test
 */
class Test_Browser_Plugin_Mailer extends Test_Browser_Plugin
{
  /** Returns the identifying name for the plugin.
   *
   * @return string
   */
  public function getName(  )
  {
    return 'mailer';
  }

  /** (Re-)Initialize the plugin.
   *
   * @return void
   */
  public function setup(  )
  {
    $this->setEncapsulatedObject(
      $this->getBrowser()->getContext()->getMailer()->getLogger()
    );
  }
}