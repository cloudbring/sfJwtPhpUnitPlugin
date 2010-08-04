<?php
/** Adds Symfony functional test case functionality to PHPUnit's TestCase
 *   framework.
 *
 * Note:  Designed to work with Symfony 1.4.  Might not work properly with later
 *  versions of Symfony.
 *
 * @package jwt
 * @subpackage lib.test
 */
abstract class Test_Case_Functional extends Test_Case
{
  const
    MSG_STATUSCODE      = 'Browser returned HTTP status %d.',
    MSG_FORWARD_MODULE  = 'Browser is forwarded to %s module.',
    MSG_FORWARD_ACTION  = 'Browser is forwarded to %s action.',
    MSG_REDIRECT_MODULE = 'Browser is redirected to %s module.',
    MSG_REDIRECT_ACTION = 'Browser is redirected to %s action.',
    MSG_REDIRECT_RAW    = 'Browser is redirected to URL: %s.';

  protected
    $_browser;

  /** Pre-test initialization.
   *
   * @return void
   */
  final protected function _init(  )
  {
    $this->_browser = new Test_Browser();
  }

  /** Shortcut for assertEqual($this->_browser->getStatusCode(), $code).
   *
   * @param int    $code
   * @param string $msg Custom failure message (optional).
   *
   * @return void
   */
  protected function assertStatusCode( $code, $msg = null )
  {
    if( $msg === null )
    {
      $msg = sprintf(self::MSG_STATUSCODE, $code);
    }

    $this->assertEquals($code, $this->_browser->getStatusCode(), $msg);
  }

  /** Asserts that the browser has been forwarded (not redirected) to the
   *   specified action.
   *
   * @param string                $module
   * @param string                $action
   * @param string|array(string)  $msg    Custom failure message(s) (optional).
   *  If an array is provided, the first element will be the message for the
   *   module check, and the second element will be the message for the action
   *   check.
   *
   * @return void
   */
  public function assertForwardedTo( $module, $action, $msg = null )
  {
    if( $msg === null )
    {
      $msg = array(
        sprintf(self::MSG_FORWARD_MODULE, $module),
        sprintf(self::MSG_FORWARD_ACTION, $action)
      );
    }
    elseif( ! is_array($msg) )
    {
      $msg = array($msg, $msg);
    }

    $Entry = $this->_browser->getContext()->getActionStack()->getLastEntry();

    $this->assertEquals($module, $Entry->getModuleName(), reset($msg));
    $this->assertEquals($action, $Entry->getActionName(), next($msg));
  }

  /** Asserts that the browser has been redirected (not forwarded) to the
   *   specified action.
   *
   * @param string                $module
   * @param string                $action If null, $module is considered to be a
   *  URL (relative or absolute).
   * @param string|array(string)  $msg    Custom failure message(s) (optional).
   *  If an array is provided, the first element will be the message for the
   *   module check, and the second element will be the message for the action
   *   check.
   *
   * @return void
   */
  public function assertRedirectedTo( $module, $action = null, $msg = null )
  {
    $destination = $this->_browser->getResponse()->getHttpHeader('location');

    if( $action )
    {
      if( $msg === null )
      {
        $msg = array(
          sprintf(self::MSG_REDIRECT_MODULE, $module),
          sprintf(self::MSG_REDIRECT_ACTION, $action)
        );
      }
      elseif( ! is_array($msg) )
      {
        $msg = array($msg, $msg);
      }

      list($route, $params) =
        $this->_browser->getContext()->getController()
          ->convertUrlStringToParameters($destination);

      $this->assertEquals($module, $params['module'], reset($msg));
      $this->assertEquals($action, $params['action'], next($msg));
    }
    else
    {
      if( $msg === null )
      {
        $msg = sprintf(self::MSG_REDIRECT_RAW, $module);
      }

      $this->assertEquals($module, $destination, $msg);
    }
  }
}