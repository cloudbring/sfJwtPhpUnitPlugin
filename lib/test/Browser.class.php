<?php
/** Adds domain-specific functionality to sfTestBrowser.
 *
 * Note:  Designed to work with Symfony 1.4.  Might not work properly with later
 *  versions of Symfony.
 *
 * @package jwt
 * @subpackage lib.test
 */
class Test_Browser extends Test_ObjectWrapper
{
  private
    $_isCalled,
    $_plugins;

  /** Init the class instance.
   *
   * @return void
   */
  public function __construct(  )
  {
    $this->_isCalled  = false;
    $this->_plugins   = array();

    $this->setEncapsulatedObject('sfBrowser');
  }

  /** Activate a plugin.
   *
   * @param $name
   *
   * @return Test_Browser($this)
   */
  public function usePlugin( $name )
  {
    if( empty($this->_plugins[$name]) )
    {
      $class = Test_Browser_Plugin::normalizeClassname($name);

      /* @var $Plugin Test_Browser_Plugin */
      $Plugin = new $class($this);

      $this->_plugins[$name] = $Plugin;

      $this->injectDynamicMethod(
        $Plugin->getMethodName(),
        array($Plugin, 'invoke')
      );

      if( $this->isCalled() )
      {
        $Plugin->initialize();
      }
    }

    return $this;
  }

  /** Execute a browser request.
   *
   * @param string $uri          The URI to fetch
   * @param string $method       The request method
   * @param array  $parameters   The Request parameters
   * @param bool   $changeStack  Change the browser history stack?
   *
   * @return Test_Browser($this)
   */
  public function call( $uri, $method = 'get', $parameters = array(), $changeStack = true )
  {
    $this->getEncapsulatedObject()->call(
      $uri,
      $method,
      $parameters,
      $changeStack
    );
    $this->_isCalled = true;

    /* @var $Plugin Test_Browser_Plugin */
    foreach( $this->_plugins as $Plugin )
    {
      $Plugin->initialize();
    }

    return $this;
  }

  /** Returns whether the browser has made a request yet.
   *
   * @return bool
   */
  public function isCalled(  )
  {
    return $this->_isCalled;
  }

  /** Returns the content of elements that match a CSS selector.
   *
   * @param string $selector
   *
   * @return array(sfDomCssSelector)
   */
  public function select( $selector )
  {
    return $this->getResponseDomCssSelector()->matchAll($selector);
  }

  /** Returns serialized content from a request as an object.
   *
   * @return mixed
   */
  public function getSerializedContent(  )
  {
    $res = @unserialize($this->getContent());

    if( $res === false and $this->getContent() !== serialize(false) )
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

  /** Returns the message of an uncaught exception, if one exists.
   *
   * @return string
   */
  public function getError(  )
  {
    return
      $this->checkCurrentExceptionIsEmpty()
        ? ''
        : $this->getCurrentException()->getMessage();
  }

  /** Returns the sfForm instance from the action stack.
   *
   * @return sfForm|null
   */
  public function getForm(  )
  {
    $Action =
      $this->getContext()
        ->getActionStack()
        ->getLastEntry()
          ->getActionInstance();

    foreach( $Action->getVarHolder()->getAll() as $name => $value )
    {
      if( $value instanceof sfForm and $value->isBound() )
      {
        return $value;
      }
    }

    return null;
  }

  /** Returns the email logger from the browser context.
   *
   * @return sfMailerMessageLoggerPlugin
   */
  public function getMailer(  )
  {
    return $this->getContext()->getMailer()->getLogger();
  }
}