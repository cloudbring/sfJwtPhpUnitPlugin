<?php
/** Parses a response formatted as JSON.
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test.browser.plugin
 */
class Test_Browser_Plugin_JsonContent extends Test_Browser_Plugin
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
    return 'getJsonContent';
  }

  /** Returns JSON-encoded content from a request as an object.
   *
   * @param bool $assoc If true, JS objects will be converted to associative
   *  arrays instead of stdClass instances.
   *
   * @return mixed
   */
  public function invoke( $assoc = false )
  {
    $content  = $this->getBrowser()->getContent();
    $res      = json_decode($content, $assoc);

    if( $res === null and $content != json_encode(null) )
    {
      /* Output the content in the Exception for easy debugging. */
      throw new RuntimeException(sprintf(
        "Invalid JSON Content:\n\n%s",
          $content
      ));
    }

    return $res;
  }
}