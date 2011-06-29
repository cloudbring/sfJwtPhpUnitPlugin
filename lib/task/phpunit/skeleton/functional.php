<?php
/** Functionl tests for ##URL##.
 *
 * @package ##PROJECTNAME##
 * @subpackage ##SUBPACKAGE##
 */
class ##ROUTENAME##Test extends Test_Case_Functional
{
  protected
    $_url;

  protected function _setUp(  )
  {
    $this->_url = '##URL##';
  }

  public function testSmokeCheck(  )
  {
    $this->_browser->get($this->_url);
    $this->assertStatusCode(200);
  }
}