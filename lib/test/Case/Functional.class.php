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

    $this->assertEquals(
      $code,
      $this->_browser->getResponse()->getStatusCode(),
      $msg
    );
  }
}