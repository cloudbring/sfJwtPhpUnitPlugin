<?php
/** Extends browser request functionality.
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test.browser.plugin
 */
class Test_Browser_Plugin_Request extends Test_Browser_Plugin
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
    return 'getRequest';
  }

  /** Returns a reference to the request object from the browser context.
   *
   * @return Test_Browser_Plugin_Request($this)
   */
  public function invoke(  )
  {
    if( ! $this->hasEncapsulatedObject() )
    {
      $this->setEncapsulatedObject($this->getBrowser()->getRequest());
    }

    return $this;
  }

  /** Returns whether the request was forwarded.
   *
   * @return bool
   */
  public function isForwarded(  )
  {
    return $this->getBrowser()->getContext()->getActionStack()->getSize() > 1;
  }

  /** Returns the stack entry that the request was forwarded to.
   *
   * @param $pos Position in the stack to reference.
   *
   * @return sfActionStackEntry|null Only returns a value if the request was
   *  forwarded.
   */
  public function getForwardedActionStackEntry( $pos = 'last' )
  {
    if( $this->isForwarded() )
    {
      /* @var $Stack sfActionStack */
      $Stack = $this->getBrowser()->getContext()->getActionStack();

      switch( $pos )
      {
        case 'last':  $Entry = $Stack->getLastEntry();  break;
        case 'first': $Entry = $Stack->getFirstEntry(); break;
        default:      $Entry = $Stack->getEntry($pos);  break;
      }

      return $Entry;
    }
  }

  /** Returns the action and module name that the request was forwarded to.
   *
   * @param $pos Position in the stack to reference.
   *
   * @return array|void Only returns a value if the request was forwarded.
   *
   *  array(
   *    'module'  => (string; module name),
   *    'action'  => (string; action name)
   *  )
   */
  public function getForwardedArray( $pos = 'last' )
  {
    if( $Entry = $this->getForwardedActionStackEntry($pos) )
    {
      return array(
        'module'  => $Entry->getModuleName(),
        'action'  => $Entry->getActionName()
      );
    }
  }

  /** Returns the action and module name that the request was forwarded to, in
   *   string format.
   *
   * @param $pos Position in the stack to reference.
   *
   * @return string|void e.g., "module/action" if the request was forwarded.
   */
  public function getForwardedString( $pos = 'last' )
  {
    if( $Entry = $this->getForwardedActionStackEntry($pos) )
    {
      return sprintf('%s/%s', $Entry->getModuleName(), $Entry->getActionName());
    }
  }
}