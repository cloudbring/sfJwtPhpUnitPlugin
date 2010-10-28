<?php
/** Used to extend the functionality of Test_Browser.
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test.browser
 */
abstract class Test_Browser_Plugin extends Test_ObjectWrapper
{
  private
    /** @var Test_Browser */
    $_browser;

  /** Returns the name of the accessor that will invoke this plugin.
   *
   * For example, if this method returns 'getMagic', then the plugin can be
   *  invoked in a test case by calling $this->_browser->getMagic().
   * 
   * @return string
   */
  abstract public function getMethodName(  );

  /** Initialize the plugin.  Invoked right after a get() or post() call
   *   finishes.
   *
   * @return void
   */
  public function initialize(  )
  {
  }

  /** Invokes the plugin.
   *
   * @param mixed,...
   *
   * @return mixed
   */
  abstract public function invoke( /* $param, ... */ );

  /** Init the class instance.
   *
   * @param Test_Browser $Browser
   *
   * @return void
   */
  final public function __construct( Test_Browser $Browser )
  {
    $this->_browser = $Browser;
  }

  /** Accessor for the corresponding browser object.
   *
   * @return Test_Browser
   */
  public function getBrowser(  )
  {
    return $this->_browser;
  }

  /** Given a plugin name, attempts to determine the correct corresponding
   *   classname.
   *
   * @param string $name
   *
   * @return string
   * @throws InvalidArgumentException if $name can't be sanitized.
   */
  static public function sanitizeClassname( $name )
  {
    if( ! is_string($name) )
    {
      throw new InvalidArgumentException(sprintf(
        'Invalid %s encountered; string expected.',
          is_object($name) ? get_class($name) : gettype($name)
      ));
    }

    if( ! class_exists($name) )
    {
      $altname = 'Test_Browser_Plugin_' . ucfirst($name);

      if( class_exists($altname) )
      {
        $name = $altname;
      }
      else
      {
        throw new InvalidArgumentException(sprintf(
          'Unable to locate a plugin named "%s".',
            $name
        ));
      }
    }

    if( ! is_subclass_of($name, __CLASS__) )
    {
      throw new InvalidArgumentException(sprintf(
        '%s is not a valid %s class.',
          $name,
          __CLASS__
      ));
    }

    return $name;
  }
}