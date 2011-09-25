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

/** Waits for the proper moment, then authenticates the user.
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.test.browser.listener
 */
class Test_Browser_Listener_Signin
  implements Test_Browser_Listener
{
  protected
    /** @var sfGuardUser */
    $_user;

  /** Init the class instance.
   *
   * @param sfGuardUser $user
   *
   * @return void
   */
  public function __construct( sfGuardUser $user )
  {
    $this->_user = $user;
  }

  /** Returns the events that this listener should be registered for.
   *
   * @return array
   */
  public function getEventNames()
  {
    return array('context.load_factories');
  }

  /** Invokes the listener.
   *
   * @param sfEvent $event
   *
   * @return void
   */
  public function invoke( sfEvent $event )
  {
    /* @var $user sfGuardSecurityUser */
    if( ! $user = $event->getSubject()->getUser() )
    {
      throw new RuntimeException('User object not created.');
    }

    if( $user instanceof sfGuardSecurityUser )
    {
      if( $user->isAuthenticated() )
      {
        $user->signOut();
      }

      $user->signIn($this->_user);

      $event->getSubject()->getEventDispatcher()->notify(new sfEvent(
        $this,
        'application.log',
        array(sprintf('User is logged in as "%s".', $user->getUsername()))
      ));
    }
    else
    {
      throw new LogicException(sprintf(
        'Cannot log in %s; sfGuardSecurityUser expected.',
          get_class($user)
      ));
    }
  }
}