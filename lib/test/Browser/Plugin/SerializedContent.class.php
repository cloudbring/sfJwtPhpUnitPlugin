<?php
/** Parses a response formatted using PHP's serialize() method.
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test.browser.plugin
 */
class Test_Browser_Plugin_SerializedContent extends Test_Browser_Plugin
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
    return 'getSerializedContent';
  }

  /** Returns serialized content from a request as an object.
   *
   * @return mixed
   */
  public function invoke(  )
  {
    $content  = $this->getBrowser()->getContent();
    $res      = @unserialize($content);

    if( $res === false and $content !== serialize(false) )
    {
      throw new Exception(sprintf(
        "Invalid serialized content:\n\n%s",
          $content
      ));
    }

    return $res;
  }
}