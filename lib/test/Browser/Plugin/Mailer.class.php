<?php
/** Exposes the Symfony email logger.
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test.browser.plugin
 */
class Test_Browser_Plugin_Mailer extends Test_Browser_Plugin
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
    return 'getMailer';
  }

  /** Returns a reference to the email logger from the browser context.
   *
   * @return Test_Browser_Plugin_Mailer($this)
   */
  public function invoke(  )
  {
    if( ! $this->hasEncapsulatedObject() )
    {
      $this->setEncapsulatedObject(
        $this->getBrowser()->getContext()->getMailer()->getLogger()
      );
    }

    return $this;
  }

  /** Accesses a specific message from the queue.
   *
   * @param int $pos
   *
   * @return Swift_Message|null
   */
  public function getMessage( $pos )
  {
    $messages = $this->getMessages();
    return isset($messages[$pos]) ? $messages[$pos] : null;
  }

  /** Retrieve the messages matching a given field value.
   *
   * @param string $field
   * @param string $value
   *
   * @return array(Swift_Message)
   */
  public function getMessagesMatching( $field, $value )
  {
    $messages = array();

    /* @var $Message Swift_Message */
    foreach( $this->getMessages() as $Message )
    {
      if( $headers = $Message->getHeaders()->getAll($field) )
      {
        /* @var $Header Swift_Mime_Header */
        foreach( $headers as $Header )
        {
          if( $Header->getFieldBody() == $value )
          {
            $messages[] = $Message;
            break;
          }
        }
      }
    }

    return $messages;
  }
}