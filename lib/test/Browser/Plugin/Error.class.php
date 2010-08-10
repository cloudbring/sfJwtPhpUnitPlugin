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
   *  - true:   Return the Exception object (or null if there was none).
   *  - false:  Return the error message (or null if there was no Exception).
   *
   * @return string|Exception|null
   */
  protected function _doGetInstance( $object = false )
  {
    if( $Exception = $this->getEncapsulatedObject() )
    {
      $args = func_get_args();

      return
        empty($args[0])
          ? (string) $this->getEncapsulatedObject()->getMessage()
          : $this->getEncapsulatedObject();
    }
    else
    {
      return null;
    }
  }
}