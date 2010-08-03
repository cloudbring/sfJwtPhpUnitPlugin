<?php
/** Adds domain-specific functionality to sfTestBrowser.
 *
 * Note:  Designed to work with Symfony 1.4.  Might not work properly with later
 *  versions of Symfony.
 *
 * @package jwt
 * @subpackage lib.test
 */
class Test_Browser extends sfBrowser
{
  /** Returns the content of elements that match a CSS selector.
   *
   * @param string $selector
   *
   * @return array(string)
   */
  public function select( $selector )
  {
    return $this->getResponseDomCssSelector()->matchAll($selector);
  }

  /** Returns JSON-encoded content from a request as an object.
   *
   * @return mixed
   */
  public function getJsonResponse(  )
  {
    $res = json_decode($this->getContent());

    if( is_null($res) )
    {
      throw new Exception(sprintf(
        "Invalid JSON Content:\n\n%s",
          $this->getContent()
      ));
    }

    return $res;
  }

  /** Returns serialized content from a request as an object.
   *
   * @return mixed
   */
  public function getSerializedResponse(  )
  {
    $res = @unserialize($this->getContent());

    if( $res === false and $this->getContent() !== 'b:0;' )
    {
      throw new Exception(sprintf(
        "Invalid serialized content:\n\n%s",
          $this->getContent()
      ));
    }

    return $res;
  }

  /** Shortcut for getting the status code from the response.
   *
   * @return int
   */
  public function getStatusCode(  )
  {
    return $this->getResponse()->getStatusCode();
  }

  /** Shortcut for getting the content from the response.
   *
   * @return string
   */
  public function getContent(  )
  {
    return $this->getResponse()->getContent();
  }
}