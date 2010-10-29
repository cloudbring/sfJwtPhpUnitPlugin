<?php
/** Extends browser view cache functionality.
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test.browser.plugin
 */
class Test_Browser_Plugin_ViewCache extends Test_Browser_Plugin
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
    return 'getViewCache';
  }

  /** Returns a reference to the view cache object from the browser context.
   *
   * Note:  If caching is disabled, this method will return null.
   *
   * @return Test_Browser_Plugin_ViewCache($this)|null
   */
  public function invoke(  )
  {
    if( ! $this->hasEncapsulatedObject() )
    {
      $this->setEncapsulatedObject(
        $this->getBrowser()->getContext()->getViewCacheManager()
      );
    }

    return $this->hasEncapsulatedObject() ? $this : null;
  }

  /** Returns whether the specified URL has been cached properly.
   *
   * @param string $uri         If not specified, the browser object's current
   *  URI (cache key) will be used.
   * @param bool   $with_layout If set, with_layout setting must also match.
   *
   * @return bool
   */
  public function isUriCached( $uri = null, $with_layout = null )
  {
    if( ! $uri )
    {
      $uri = $this->getCurrentCacheKey();
    }

    if( $this->has($uri) )
    {
      if( ! ($with_layout === null or $this->withLayout($uri) == $with_layout) )
      {
        return false;
      }

      $cached = $this->getContent($uri);
      return $cached == $this->getBrowser()->getContent();
    }

    return false;
  }
  
  /** Returns the HTML content of a cached URI.
   * 
   * @param string $uri If not specified, the browser object's current URI
   *  (cache key) will be used.
   * 
   * @return string
   */
  public function getContent( $uri )
  {
    if( ! $uri )
    {
      $uri = $this->getCurrentCacheKey();
    }
    
    return $this->has($uri) ? unserialize($this->get($uri))->getContent() : '';
  }
}