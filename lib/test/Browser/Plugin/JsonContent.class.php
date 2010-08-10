<?php
/** Adds features for working with JSON content in test browser responses.
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test
 */
class Test_Browser_Plugin_JsonContent extends Test_Browser_Plugin
{
  /** Returns the identifying name for the plugin.
   *
   * @return string
   */
  public function getName(  )
  {
    return 'jsonContent';
  }

  /** (Re-)Initialize the plugin.
   *
   * @return void
   * @throws RuntimeException if browser content is not valid JSON.
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
      $res      = json_decode($content);

      if( $res === null )
      {
        throw new RuntimeException(sprintf(
          'Invalid JSON Content:%2$s%2$s%1$s',
            $content,
            PHP_EOL
        ));
      }

      $this->setEncapsulatedObject($res);
    }
  }
}