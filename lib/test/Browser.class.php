<?php
/** Adds domain-specific functionality to sfTestBrowser.
 *
 * Note:  Designed to work with Symfony 1.4.  Might not work properly with later
 *  versions of Symfony.
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test
 */
class Test_Browser extends sfBrowser
{
  const
    CLASS_PLUGIN  = 'Test_Browser_Plugin';

  private
    $_plugins;

  /** Init the class instance.
   *
   * @return void
   */
  public function __construct(  )
  {
    parent::__construct();

    $this->_plugins = array();

    $this
      ->usePlugin('request')
      ->usePlugin('response')
      ->usePlugin('error');
  }

  /** Registers a plugin with the browser.
   *
   * @param string|Test_Browser_Plugin $plugin
   *
   * @return Test_Browser $this
   * @throws InvalidArgumentException if $plugin is invalid.
   * @throws LogicException if the plugin has already been registered.
   */
  public function usePlugin( $plugin )
  {
    /** Validate $plugin. */
    if( is_string($plugin) )
    {
      /* See if $plugin is a shortened version of the class name. */
      if( ! (class_exists($plugin) and is_subclass_of($plugin, self::CLASS_PLUGIN)) )
      {
        $plugin = self::CLASS_PLUGIN . '_' . ucfirst($plugin);
      }
    }

    if( ! is_subclass_of($plugin, self::CLASS_PLUGIN) )
    {
      throw new InvalidArgumentException(sprintf(
        '%s is not a valid %s %s.',
          $plugin,
          self::CLASS_PLUGIN,
          is_object($plugin) ? 'instance' : 'class'
      ));
    }

    /* Initialize the plugin. */
    if( ! is_object($plugin) )
    {
      $plugin = new $plugin();
    }

    $name = $plugin->getName();
    if( $this->hasPlugin($name) )
    {
      throw new LogicException(sprintf(
        'Plugin "%s" has already been registered for this %s instance.',
          $name,
          get_class($this)
      ));
    }

    /* Plugin has passed inspection.  Attach it. */
    $this->_plugins[$name] = $plugin;
    $plugin->init($this);

    return $this;
  }

  /** Accessor for $_plugins[$name].
   *
   * @param string    $name
   *
   * @return Test_Browser_Plugin
   * @throws InvalidArgumentException if no plugin named $name was registered.
   */
  public function getPlugin( $name )
  {
    if( ! $this->hasPlugin($name) )
    {
      throw new InvalidArgumentException(sprintf(
        'Plugin "%s" has not been registered with this %s instance.',
          $name,
          get_class($this)
      ));
    }

    return $this->_plugins[$name];
  }

  /** Similar to getPlugin(), but can return different values depending on how
   *   the plugin is configured.
   *
   * @see Test_Browser_Plugin::getInstance()
   *
   * @param string    $name
   * @param mixed,... $args
   *
   * @return mixed
   */
  public function getPluginInstance( $name )
  {
    return $this->getPlugin($name)->getInstance(
      array_slice(func_get_args(), 1)
    );
  }

  /** Returns whether a given plugin has been registered.
   *
   * @param string $name
   *
   * @return bool
   */
  public function hasPlugin( $name )
  {
    return isset($this->_plugins[$name]);
  }

  /** Alias for getPlugin().
   *
   * @param string $meth
   * @param array  $args
   *
   * @return Test_Browser_Plugin
   */
  public function __call( $meth, array $args )
  {
    if( strlen($meth) > 3 and substr($meth, 0, 3) == 'get' )
    {
      /** @todo When we move to PHP 5.3, use lcfirst() instead. */
      $plugin = strtolower(substr($meth, 3, 1)) . substr($meth, 4);

      if( $this->hasPlugin($plugin) or ! method_exists('parent', $meth) )
      {
        array_unshift($args, $plugin);
        return call_user_func_array(array($this, 'getPluginInstance'), $args);
      }
    }

    /* A little ugly, but it gets the job done. */
    return call_user_func_array(array('parent', $meth), $args);
  }

  /** Reset plugins before calling sfBrowser->call().
   *
   * @return Test_Browser $this
   */
  public function call( $uri, $method = 'get', $parameters = array(), $changeStack = true )
  {
    foreach( $this->_plugins as $Plugin )
    {
      $Plugin->reset();
    }

    return parent::call($uri, $method, $parameters, $changeStack);
  }

  /** Returns whether a browser call has executed.
   *
   * @return bool
   */
  public function hasContext(  )
  {
    return isset($this->context);
  }
}