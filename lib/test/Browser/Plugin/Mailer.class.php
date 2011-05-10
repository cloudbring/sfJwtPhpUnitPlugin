<?php
/**
 * Copyright (c) 2011 J. Walter Thompson dba JWT
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/** Exposes the Symfony email logger.
 *
 * @author Phoenix Zerin <phoenix.zerin@jwt.com>
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
  public function getMessagesWith( $field, $value )
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

  /** Similar to getMessagesWith(), but only returns the first message matching
   *   the criterion.
   *
   * @param string $field
   * @param string $value
   *
   * @return Swift_Message|null
   */
  public function getMessageWith( $field, $value )
  {
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
            return $Message;
          }
        }
      }
    }

    return null;
  }

  /** Similar to getMessagesWith(), but uses a regular expression to match
   *   values.
   *
   * @param string          $field
   * @param string(regexp)  $regexp
   *
   * @return array(Swift_Message)
   */
  public function getMessagesLike( $field, $regexp )
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
          if( preg_match($regexp, $Header->getFieldBody()) )
          {
            $messages[] = $Message;
            break;
          }
        }
      }
    }

    return $messages;
  }

  /** Similar to getMessagesLike(), but only returns the first message matching
   *   the criterion.
   *
   * @param string          $field
   * @param string(regexp)  $regexp
   *
   * @return Swift_Message|null
   */
  public function getMessageLike( $field, $regexp )
  {
    /* @var $Message Swift_Message */
    foreach( $this->getMessages() as $Message )
    {
      if( $headers = $Message->getHeaders()->getAll($field) )
      {
        /* @var $Header Swift_Mime_Header */
        foreach( $headers as $Header )
        {
          if( preg_match($regexp, $Header->getFieldBody()) )
          {
            return $Message;
          }
        }
      }
    }

    return null;
  }
}