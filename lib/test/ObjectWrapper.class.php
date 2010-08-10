<?php
/** Emulates multiple inheritence by wrapping an instance of an object.
 *
 * It's not true multiple inheritence, as the wrapper does not have access to
 *  protected properties/methods of the wrapped instance; the wrapper can only
 *  add or overwrite functionality.
 *
 * NB:  Note that all passthrough methods will silently fail if no object is
 *  attached.
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib
 */
abstract class Test_ObjectWrapper
{
  private
    $_encapsulatedObject;

  /** Accessor for $_encapsulatedObject.
   *
   * @return Object|null
   */
  public function getEncapsulatedObject(  )
  {
    return $this->_encapsulatedObject;
  }

  /** Modifier for $_encapsulatedObject.
   *
   * Note that this method will overwrite any existing encapsulated object!
   *
   * @param Object $Object
   *
   * @return ObjectWrapper($this)
   * @access protected Should only be invoked by subclass.
   */
  protected function setEncapsulatedObject( $Object )
  {
    $this->_encapsulatedObject = $Object;
    return $this;
  }

  /** Returns whether $_encapsulatedObject has been set yet.
   *
   * @return bool
   */
  public function hasEncapsulatedObject(  )
  {
    return isset($this->_encapsulatedObject);
  }

  /** Pass-through for generic accessor.
   *
   * @param string $key
   *
   * @return mixed
   */
  public function __get( $key )
  {
    return
      $this->hasEncapsulatedObject()
        ? $this->getEncapsulatedObject()->$key
        : null;
  }

  /** Pass-through for generic modifier.
   *
   * @param string $key
   * @param mixed  $val
   *
   * @return mixed
   */
  public function __set( $key, $val )
  {
    return
      $this->hasEncapsulatedObject()
        ? $this->getEncapsulatedObject()->$key = $val
        : null;
  }

  /** Pass-through for isset() handler.
   *
   * @param string $key
   *
   * @return bool
   */
  public function __isset( $key )
  {
    return
      $this->hasEncapsulatedObject()
        ? isset($this->getEncapsulatedObject()->$key)
        : false;
  }

  /** Pass-through for unset() handler.
   *
   * @param string $key
   *
   * @return void
   */
  public function __unset( $key )
  {
    if( $this->hasEncapsulatedObject() )
    {
      unset($this->getEncapsulatedObject()->$key);
    }
  }

  /** Pass-through for generic method handler.
   *
   * @param string $meth
   * @param array  $args
   *
   * @return mixed
   */
  public function __call( $meth, $args )
  {
    return
      $this->hasEncapsulatedObject()
        ? call_user_func_array(
            array($this->getEncapsulatedObject(), $meth),
            $args
          )
        : null;
  }
}