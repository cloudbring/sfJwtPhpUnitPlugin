<?php
/** Creates a safe environment for executing PHP scripts.
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test
 */
class Test_Harness
{
  /* @access private so that they are not accessible to included files. */
  private
    $_file,
    $_isExecuting;

  /** Init the class instance.
   *
   * @param string              $file
   *
   * @return void
   */
  public function __construct( $file )
  {
    /* No, you can't run __construct() from within a fixture file! */
    if( ! $this->isExecuting() )
    {
      $this->_file = $file;
    }
  }

  /** Accessor for $_isExecuting.
   *
   * @return bool
   */
  public function isExecuting(  )
  {
    return $this->_isExecuting;
  }

  /** Exeucute the fixture file.
   *
   * @return mixed
   */
  public function execute(  )
  {
    if( ! $this->isExecuting() )
    {
      $this->_isExecuting = true;
      $res = require $this->_file;
      $this->_isExecuting = false;
      return $res;
    }
  }
}