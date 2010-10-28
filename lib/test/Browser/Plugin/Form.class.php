<?php
/** Exposes the sfForm instance bound to the request.
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test.browser.plugin
 */
class Test_Browser_Plugin_Form extends Test_Browser_Plugin
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
    return 'getForm';
  }

  /** Returns a reference to the sfForm instance from the action stack.
   *
   * Note:  If no form was submitted, this method returns null.
   *
   * @return Test_Browser_Plugin_Form($this)|null
   */
  public function invoke(  )
  {
    if( ! $this->hasEncapsulatedObject() )
    {
      $Action =
        $this->getBrowser()
          ->getContext()
            ->getActionStack()
            ->getLastEntry()
              ->getActionInstance();

      foreach( $Action->getVarHolder()->getAll() as $name => $value )
      {
        if( $value instanceof sfForm and $value->isBound() )
        {
          $this->setEncapsulatedObject($value);
        }
      }
    }

    return $this->hasEncapsulatedObject() ? $this : null;
  }
}