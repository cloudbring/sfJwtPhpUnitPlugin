<?php
/** Adds features for working with test browser responses.
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test
 */
class Test_Browser_Plugin_Response extends Test_Browser_Plugin
{
  /** Returns the identifying name for the plugin.
   *
   * @return string
   */
  public function getName(  )
  {
    return 'response';
  }

  /** (Re-)Initialize the plugin.
   *
   * @return void
   */
  public function setup(  )
  {
    $this->setEncapsulatedObject($this->getBrowser()->getResponse());
  }

  /** Returns the content of elements that match a jQuery selector.
   *
   * @param string $selector
   *
   * @return array(sfDomCssSelector)
   */
  public function select( $selector )
  {
    return
      $this->getBrowser()
        ->getResponseDomCssSelector()
        ->matchAll($selector);
  }

  /** Returns whether the response has been forwarded.
   *
   * @return bool
   */
  public function isForwarded(  )
  {
    return $this->getBrowser()->getContext()->getActionStack()->getSize() > 0;
  }

  /** Returns the most recent action that the response was forwarded to.
   *
   * @return sfActionStackEntry|null
   */
  public function getForward(  )
  {
    return $this->getBrowser()->getContext()->getActionStack()->getLastEntry();
  }

  /** Returns whether the response has been redirected.
   *
   * @return bool
   */
  public function isRedirected(  )
  {
    return (bool) $this->getHttpHeader('location');
  }

  /** Returns the route and URL/param array that the request was redirected to.
   *
   * @return array(string $route, array|string $url)
   */
  public function getRedirect(  )
  {
    return $this->getBrowser()->getContext()->getController()
      ->convertUrlStringToParameters($this->getHttpHeader('location'));
  }
}