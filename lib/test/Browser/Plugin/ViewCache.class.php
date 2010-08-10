<?php
/** Adds features for working with the test browser view cache manager.
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test
 */
class Test_Browser_Plugin_ViewCache extends Test_Browser_Plugin
{
  /** Returns the identifying name for the plugin.
   *
   * @return string
   */
  public function getName(  )
  {
    return 'viewCache';
  }

  /** (Re-)Initialize the plugin.
   *
   * @return void
   */
  public function setup(  )
  {
    $this->setEncapsulatedObject($this->getBrowser()->getViewCacheManager());
  }
}