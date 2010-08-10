<?php
/** Adds features for working with test browser forms.
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test
 */
class Test_Browser_Plugin_Form extends Test_Browser_Plugin
{
  /** Returns the identifying name for the plugin.
   *
   * @return string
   */
  public function getName(  )
  {
    return 'form';
  }

  /** (Re-)Initialize the plugin.
   *
   * @return void
   */
  public function setup(  )
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
}