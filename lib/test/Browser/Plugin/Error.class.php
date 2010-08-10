<?php
/** Adds features for working with test browser execution errors.
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test
 */
class Test_Browser_Plugin_Error extends Test_Browser_Plugin
{
  /** Returns the identifying name for the plugin.
   *
   * @return string
   */
  public function getName(  )
  {
    return 'error';
  }

  /** (Re-)Initialize the plugin.
   *
   * @return void
   */
  public function setup(  )
  {
    if( ! $this->getBrowser()->checkCurrentExceptionIsEmpty() )
    {
      $this->setEncapsulatedObject($this->getBrowser()->getCurrentException());
    }
  }

  /** Handles a call from getPluginInstance() in the browser instance.
   *
   * @param bool $object
   *  - true:   Return the Plugin object (Exception is encapsulated).
   *  - false:  Return the error message (or null if there was no Exception).
   *
   * @return string|Test_Browser_Plugin_Error|null
   */
  protected function _doGetInstance( $object = false )
  {
    if( $this->hasEncapsulatedObject() )
    {
      $args = func_get_args();

      return
        empty($args[0])
          ? (string) $this->getMessage()
          : $this;
    }
    else
    {
      return null;
    }
  }
}