<?php
/** Accesses browser response content.
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test.browser.plugin
 */
class Test_Browser_Plugin_Content extends Test_Browser_Plugin
{
  private
    $_content;

  /** Returns the name of the accessor that will invoke this plugin.
   *
   * For example, if this method returns 'getMagic', then the plugin can be
   *  invoked in a test case by calling $this->_browser->getMagic().
   *
   * @return string
   */
  public function getMethodName(  )
  {
    return 'getContent';
  }

  /** Initialize the plugin.
   *
   * @return void
   */
  public function initialize(  )
  {
    parent::initialize();
    $this->_content = null;
  }

  /** Returns a reference to the response content.
   *
   * @return Test_Browser_Plugin_Content($this)
   */
  public function invoke(  )
  {
    if( ! isset($this->_content) )
    {
      $this->_content = $this->getBrowser()->getResponse()->getContent();
    }

    return $this;
  }

  /** Return the response content as a string.
   *
   * @return string
   */
  public function __toString(  )
  {
    return (string) $this->_content;
  }

  /** Parse content as JSON.
   *
   * @param bool $assoc If true, JS objects will be converted to associative
   *  arrays instead of stdClass instances.
   *
   * @return mixed
   */
  public function decodeJson( $assoc = false )
  {
    $res = json_decode($this->_content, $assoc);

    if( $res === null and $this->_content != json_encode(null) )
    {
      /* Output the content in the Exception for easy debugging. */
      throw new RuntimeException(sprintf(
        "Invalid JSON Content:\n\n%s",
          $this->_content
      ));
    }

    return $res;
  }

  /** Parse content as serialize()'d.
   *
   * @return mixed
   */
  public function unserialize(  )
  {
    $res = @unserialize($this->_content);

    if( $res === false and $this->_content !== serialize(false) )
    {
      throw new Exception(sprintf(
        "Invalid serialized content:\n\n%s",
          $this->_content
      ));
    }

    return $res;
  }
}