<?php
/** Adds features for working with serialized content in test browser responses.
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test
 */
class Test_Browser_Plugin_SerializedContent extends Test_Browser_Plugin
{
  const

    /** The result of unserialize() might not be an object, but the base
     *   Test_ObjectWrapper is not designed to work with non-objects.
     *
     * To work around this, setup() will create a generic object with a single
     *  property that contains the result of the unserialize() call.
     *
     * This constant holds the name of that property.
     */
    KEY = 'result';

  /** Returns the identifying name for the plugin.
   *
   * @return string
   */
  public function getName(  )
  {
    return 'serializedContent';
  }

  /** (Re-)Initialize the plugin.
   *
   * @return void
   * @throws RuntimeException if browser response is not serialized content.
   */
  public function setup(  )
  {
    if( $error = $this->getBrowser()->getError(true) )
    {
      throw new RuntimeException(sprintf(
        'Cannot parse response; an unhandled Exception occurred: %s',
          $error
      ));
    }
    else
    {
      $content  = $this->getBrowser()->getResponse()->getContent();
      $res      = @unserialize($content);

      if( $res === false and $content !== serialize(false) )
      {
        throw new RuntimeException(sprintf(
          'Invalid serialized content:%2$s%2$s%1$s',
            $content,
            PHP_EOL
        ));
      }

      $this->setEncapsulatedObject((object) array(self::KEY => $res));
    }
  }

  /** Handles a call from getPluginInstance() in the browser instance.
   *
   * @return mixed
   */
  protected function _doGetInstance(  )
  {
    return $this->getEncapsulatedObject()->{self::KEY};
  }
}