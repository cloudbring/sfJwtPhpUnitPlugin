# About This Guide
This guide documents the major functionality that JPUP provides.  It can be
  considered a user reference guide with cookbook elements.

This guide does not contain API documentation, nor does it cover topics that
  might be of interest to developers who wish to make changes to JPUP itself.

## Prerequisites
This guide assumes that you are already familiar with PHPUnit and unit-testing
  PHP applications.

For an introduction to unit testing and how to write PHPUnit test cases, see
  [the PHPUnit manual](http://www.phpunit.de/manual/current/en/).

# Where to Put Test Files
The organization of PHPUnit tests is very similar to the way Symfony tests are
  laid out.

All tests should be stored in the `test` directory under the project root
  directory (matches the value of `sfConfig::get('sf_test_dir')`).

## Unit Tests
Unit tests should be stored under `sf_test_dir/unit`.  For best results, create
  subdirectories and name your test case files to match the corresponding
  library files that they are testing.

For example, the unit tests for `lib/model/Profile.class.php` should be stored
  in `sf_test_dir/unit/lib/model/Profile.class.php`.

* JPUP does not care what the files are named, but by following this file naming
  convention, you can leverage readline's autocomplete feature when you are
  running tests via PHPUnit Symfony tasks.

## Functional Tests
Functional tests should be stored under `sf_test_dir/functional`.  Use
  subdirectories to group functional tests by application and module.

For example, the functional tests for `accountActions->executeRegister()` (e.g.,
  http://www.example.com/account/register) should be stored in
  `sf_test_dir/functional/frontend/account/register.php`.

Note that this differs from the way Symfony's built-in test framework organizes
  functional tests; Symfony's module generator creates e.g.,
  `sf_test_dir/functional/frontend/accountActionsTest.php`.

* Just as with unit tests, JPUP does not actually care how you organize your
  tests.  You can create a single functional test case for each module if you want;
  we just found that it's more efficient for us to locate and run tests when they
  are organized by module *and* action.

# Writing Tests
## Writing Unit Tests
Writing a unit test for JPUP is very similar to writing [test cases for
  vanilla PHPUnit](http://www.phpunit.de/manual/current/en/writing-tests-for-phpunit.html),
  but there are a few differences to keep in mind:

- Have your test class extend `Test_Case_Unit`, **not**
  `PHPUnit_Framework_TestCase`.

- For setup and teardown functionality, define the `_setUp()` and `_tearDown()`
  methods, respectively, in your test class (note the leading underscore in the
  method names).

Here is an example of what a unit test class looks like:

<pre>
# sf_test_dir/unit/lib/Widget/WidgetService.class.php

&lt;?php
class WidgetServiceTest extends Test_Case_Unit
{
  private
    /** @var WidgetService_Http_Client_Mock */
    $_client;

  protected function _setUp(  )
  {
    /* Inject mock HTTP adapter so that we can simulate/control Widget
     *  Service responses.
     */
    $this->_client = new WidgetService_Http_Client_Mock();
    WidgetService::setHttpClient($this->_client);
  }

  public function testGetNumberOfLikes(  )
  {
    $like_count   = '25';
    $object_type  = 'page';
    $object_id    = '123';

    /* Seed the response from the Widget server. */
    $this->_client->seed(
      sprintf(
        '/likes/count?object_type=%s&object_id=%d',
          $object_type,
          $object_id
      ),

      json_encode(array(
        'status'  => 'OK',
        'likes'   => $like_count
      ))
    );

    /* Execute the API method and check the result. */
    $this->assertEquals(
      $like_count,
      WidgetService::getNumberOfLikes($object_type, $object_id),
      'Expected correct number of likes returned.'
    );
  }
}
</pre>

### Generating Unit Tests Automatically
JPUP comes packaged with a Symfony task named `phpunit:generate-unit` to build
  unit tests for you automatically.

To use `phpunit:generate-unit`, you must first create the class skeleton, as the
  task will use Symfony's auto-loader to locate the class file.  You can also
  generate unit tests for a class that you have already added methods to (the
  task will create skeleton tests for you).

For example, suppose you wanted to create a test case for this class:

<pre>
# sf_lib_dir/HelloWorld.class.php

&lt;?php
/** My first PHP class!
 *
 * @package myproject
 * @subpackage helloworld
 */
class HelloWorld
{
  /** Returns everyone's favorite phrase.
   *
   * @return string
   */
  public function getString(  )
  {
    return 'Hello, World!';
  }

  /** Returns a generic string representation of the object.
   *
   * @return string
   */
  public function __toString(  )
  {
    return $this->getString();
  }
}
</pre>

To build a skeleton test case for the `HelloWorld` class as defined in the above
  example, execute the following command:

<pre>
./symfony phpunit:generate-unit HelloWorld
</pre>

Note that you must pass the **class name** to the task.  This is a requirement
  to avoid ambiguity when a class file contains multiple class definitions.

* JPUP does not support generating tests for multiple classes in the same file.
    If you want test cases for multiple classes that are defined in the same
    file, you will either need to create the test cases by hand or split out the
    classes into separate files.

`phpunit:generate-unit` will create a test case for you in `sf_test_dir/unit`,
  and it will create subdirectories as needed so that the directory structure
  matches the location of the class file.

In the example above, the test case file will be created at
  `sf_test_dir/unit/lib/HelloWorld.class.php` that looks something like this:

<pre>
# sf_test_dir/unit/lib/HelloWorld.class.php

&lt;?php
/** Unit tests for HelloWorld.
 *
 * @author PHX
 *
 * @package myproject
 * @subpackage test.helloworld
 */
class HelloWorldTest extends Test_Case_Unit
{
  protected function _setUp(  )
  {
  }

  public function testGetString(  )
  {
    $this->markTestIncomplete('Not implemented yet.');
  }
}
</pre>

Note that JPUP automatically populates the `@package`, `@subpackage` and
  `@author` phpdoc tags from the class docblock, and it creates skeleton tests
  for any public, non-magic methods it finds in the class.

* If the class does not have a `@package` tag, JPUP will try to use the name of
    the project as defined in `sf_config_dir/properties.ini`.

* If the class does not have a `@subpackage` tag, JPUP will try to guess one
    based on the class file's location in the project's directory structure.

* If the class does not have an `@author` tag, JPUP will try to use the name of
    the project author as defined in `sf_config_dir/properties.ini`.

* The template for the skeleton test case is located in
    `sf_root_dir/plugins/sfJwtPhpUnitPlugin/lib/task/phpunit/skeleton/unit.php`.

    If desired, you can create your own template.  JPUP will first check for a
      skeleton file at `sf_data_dir/skeleton/phpunit/unit.php`.

* You may customize the values of any tokens, such as the package or subpackage
    names (or any additional tokens in your custom skeleton file) by passing
    `--token` arguments to the task.

  For example, to change the `@package` of the test case to "MyAwesomeProject",
    you would invoke the task like this:

<pre>
./symfony phpunit:generate-unit --token='package:MyAwesomeProject' HelloWorld
</pre>

## Writing Functional Tests
Functional tests are very similar to unit tests as described above, but you also
  have access to `$this->_browser` which is an instance of a modified version of
  the `sfBrowser` class:  `Test_Browser`.

Also, functional test classes should extend the `Test_Case_Functional` class
  rather than `Test_Case_Unit`.

Here is an example of a functional test class:

<pre>
# sf_test_dir/functional/frontend/account/register.php

&lt;?php
class frontend_account_registerTest extends Test_Case_Functional
{
  public function testSuccess(  )
  {
    /* Activate additional browser plugins. */
    $this->_browser->usePlugin('form', 'mailer');

    $username = 'mytester';
    $password = 'password';
    $email    = 'tester@jwt.com';

    /* Send browser to the registration form page. */
    $this->_browser->get('/account/register');
    $this->assertStatusCode(200);

    /* Simulate form submission. */
    $this->_browser->click('Submit', array(
      'username' => $username,
      'password' => $password,
      'email'    => $email
    ));

    /* Check assertions. */
    $this->assertFalse(
      $this->_browser->getForm()->hasErrors(),
      'Expected form to have no errors.'
    );

    $this->assertNotNull(
      Doctrine::getTable('Profile')->retrieveByUsername($username),
      'Expected Profile record to be created successfully.'
    );

    $Mailer = $this->_browser->getMailer();
    $this->assertEquals(
      1,
      $Mailer->countMessages(),
      'Expected welcome email to be dispatched.'
    );

    $this->assertEquals(
      $email,
      $Mailer->getMessage(0)->getTo(),
      'Expected welcome email to be sent to the user.'
    );

    $this->assertEquals(
      'account/home',
      $this->_browser->getResponse()->getRedirectUrl(),
      'Expected browser to be redirected to account homepage.'
    );

    /* Follow the redirect. */
    $this->_browser->followRedirect();
    $this->assertStatusCode(200);

    $this->assertEquals(
      sprintf('Welcome, %s!', $username),
      $this->_browser->getContent()->select('#welcome')->getValue(),
      "Expected success page to display user's new username."
    );
  }
}
</pre>

### Generating Functional Tests Automatically
JPUP comes packaged with a Symfony task named `phpunit:generate-functional` to
  build functional tests for you automatically.

To use `phpunit:generate-functional`, you must first create the module and
  action you wish to test and wire it into your application's `routing.yml`.

Once that is done, invoke the task like this:

<pre>
./symfony phpunit:generate-functional &lt;route>
</pre>

Where `<route>` is either a route name (prefixed with `@`) or a module/action
  pair.

For example, consider if your application's `routing.yml` looked like this:

<pre>
# sf_app_dir/config/routing.yml

# default rules
homepage:
  url:   /
  param: { module: default, action: index }

# generic rules
# please, remove them by adding more specific rules
default_index:
  url:   /:module
  param: { action: index }

default:
  url:   /:module/:action/*
</pre>

You could generate a functional test case for `main/index` using either of the
  following commands:

<pre>
./symfony phpunit:generate-functional @homepage
./symfony phpunit:generate-functional main/index
</pre>

* By default, JPUP will look at the routing for the "frontend" application.  If
    you want to generate a functional test for a different application, you will
    need to specify it using the `--application` parameter:

<pre>
./symfony phpunit:generate-functional --application=backend @activitylogs
</pre>

JPUP will generate a skeleton test case for you that looks something like this:

<pre>
# sf_test_dir/functional/frontend/main/index.php

&lt;?php
/** Functional tests for /main/index.
 *
 * @author PHX
 *
 * @package myproject
 * @subpackage test.main
 */
class frontend_main_indexTest extends Test_Case_Functional
{
  protected
    $_application = 'frontend',
    $_url;

  protected function _setUp(  )
  {
    $this->_url = '/main/index';
  }

  public function testSmokeCheck(  )
  {
    $this->_browser->get($this->_url);
    $this->assertStatusCode(200);
  }
}
</pre>

Note that, just like `phpunit:generate-unit`, `phpunit:generate-functional`
  automatically populates the `@package`, `@subpackage` and `@author` phpdoc
  tags from the class docblock.

* If the action class does not have a `@package` tag, JPUP will try to use the
    name of the project as defined in `sf_config_dir/properties.ini`.

* If the action class does not have a `@subpackage` tag, JPUP will try to guess
    one based on the module and action names.

* If the class does not have an `@author` tag, JPUP will try to use the name of
    the project author as defined in `sf_config_dir/properties.ini`.

* The template for the skeleton test case is located in
    `sf_root_dir/plugins/sfJwtPhpUnitPlugin/lib/task/phpunit/skeleton/functional.php`.

    If desired, you can create your own template.  JPUP will first check for a
      skeleton file at `sf_data_dir/skeleton/phpunit/functional.php`.

* You may customize the values of any tokens, such as the package or subpackage
    names (or any additional tokens in your custom skeleton file) by passing
    `--token` arguments to the task.

  For example, to change the `@package` of the test case to "MyAwesomeProject",
    you would invoke the task like this:

<pre>
./symfony phpunit:generate-functional --token='package:MyAwesomeProject' main/index
</pre>

### Signing In
Testing applications that require login is a tricky proposition.  It's easy
  enough to sign a user in, but every time the browser makes a request, it
  destroys and rebuilds the application context, which logs the user back out!

`Test_Browser` provides a `signin()` method to solve this problem.  Simply pass
  in a username or email address, and the browser will make sure the user is
  logged in during the next and subsequent requests:

<pre>
# sf_test_dir/functional/frontend/admin/dashboard.php

&lt;?php
class frontend_admin_dashboardTest extends Test_Case_Functional
{
  protected
    $_url = '/admin/dashboard';

  public function testMustBeLoggedIn(  )
  {
    $this->_browser->get($this->_url);
    $this->assertStatusCode(401);
  }

  public function testUserCanAccessIfSignedIn(  )
  {
    $this->loadFixture('admin_user.php');
    $this->_browser->signin('administrator');

    $this->_browser->get($this->_url);
    $this->assertStatusCode(200);
  }

  public function testSigninOnlyLastsForTheDurationOfTheTest(  )
  {
    $this->_browser->get($this->_url);
    $this->assertStatusCode(401);
  }
}
</pre>

Note from the last test in the example above that the user will only remain
  signed in for the duration of the test in which the call to `signin()` was
  made.  If you want the user to be logged in during another test, you will need
  to call `signin()` again, or move that code into your test case's `_setUp()`
  method.

### Interacting with the Symfony Application Context
While building JPUP, we found that there were a number of features that
  the `sfTestFunctional` classes afforded that are extraordinarily useful for
  testing but are not accessible to `sfBrowser`.

After a lot of experimentation, we started developing a series of plugins that
  encapsulate properties of the Symfony context, adding extra features and
  exposing more of the objects' internals without restricting their respective
  APIs.

Here is the full list of plugins that come with JPUP:

- Content
  - Usage:  `$this->_browser->getContent()`
  - HTML response text container.
  - Also includes methods for decoding serialized content and interacting with
    Symfony's `sfDomCssSelector`.

- Error
  - Usage:  `$this->_browser->getError()`
  - Makes accessible the uncaught exception from the request if applicable
    (useful for debugging HTTP 500 response codes).

- Form
  - Usage:  `$this->_browser->getForm()`
  - Makes accessible the `sfForm` instance bound to the Symfony action if
    applicable.

- Logger
  - Usage:  `$this->_browser->getLogger()`
  - Injects an `sfVarLogger` into the context so that log messages can be
    inspected.

- Mailer
  - Usage:  `$this->_browser->getMailer()`
  - SwiftMailer log (`sfMailerMessageLoggerPlugin`).
  - Also provides methods for searching messages by header or position.

- Request
  - Usage:  `$this->_browser->getRequest()`
  - Symfony Request object (`sfWebRequest`).
  - Also provides methods for determining whether and where a request was
    forwarded.

- Response
  - Usage:  `$this->_browser->getResponse()`
  - Symfony Response object (`sfWebResponse`).
  - Also provides methods for determining whether and where a response was
    redirected.

- ViewCache
  - Usage:  `$this->_browser->getViewCache()`
  - Makes accessible the view cache manager (`sfViewCacheManager`).

The Form, Mailer and ViewCache plugins are not used that frequently and need
  to be activated before they can be used (by calling
  `$this->_browser->usePlugin('...')`; see individual plugin sections for
  more information).

For complete API documentation of browser plugins, see the PHPDoc-generated
  documentation.

Here are some examples of use cases where `Test_Browser` plugins come in handy:

#### Testing Service Calls
A number of applications expose services that return serialized or JSON-encoded
  values instead of HTML content.  Manually parsing the content these requests
  would quickly become tedious; fortunately, the Content plugin provides methods
  to handle this automatically:

<pre>
# sf_test_dir/functional/frontend/do/like.php

&lt;?php
class frontend_do_likeTest extends Test_Case_Functional
{
  public function testSuccess(  )
  {
    $this->_browser->post('/do/like', array(
      'user_id'     => '1',
      'object_id'   => '123'
    ));

    $this->assertStatusCode(200);

    /* (string) $this->_browser->getContent() would evaluate to:
     * '{"status":"OK","likes":"1"}'
     *
     * To work with the decoded JSON code, use:
     */
    $decoded = $this->_browser->getContent()->decodeJson();

    $this->assertEquals(
      'OK',
      $decoded->status,
      'Expected success status value.'
    );

    $this->assertEquals(
      1,
      $decoded->likes,
      'Expected correctly-incremented number of likes.'
    );
  }
}
</pre>

* The Content plugin also has a `deserialize()` method for output encoded with
  PHP's `serialize()` function.

* Note:  `$this->_browser->getContent()` returns an instance of
  `Test_Browser_Plugin_Content`.  If you want the raw text from the response,
  cast it as a string or invoke its `__toString()` method.

#### Testing Form Submissions
To access a submitted form, use the Form plugin:

<pre>
# sf_test_dir/functional/frontend/contactus/reportissue.php

&lt;?php
class frontend_main_reportissueTest extends Test_Case_Functional
{
  public function testSuccess(  )
  {
    /* The Form plugin has to be activated before it can be used. */
    $this->_browser->usePlugin('form');

    $this->_browser->get('/contactus/reportissue');
    $this->assertStatusCode(200);

    $this->_browser->click('Submit', array(
      'issue' => array(
        'firstname'   => 'Functional',
        'lastname'    => 'Tester',
        'email'       => 'functional_tester@jwt.com',
        'description' => 'This is a test form submission.'
      )
    ));

    /* Access the submitted form. */
    $Form = $this->_browser->getForm();

    $this->assertFalse(
      $Form->hasErrors(),
      'Expected form to have no errors.'
    );
  }
}
</pre>

* As it is not used very often, the Form plugin is not enabled by default.  To
  use it in your test, call `$this->_browser->usePlugin('form')`.

* The Form plugin will only fetch a single `sfForm` instance from the action
  stack.  If an action utilizes more than one bound form, this plugin will only
  return one of them (whichever one was assigned to the action's variable holder
  first).

* If a form is not added to the action's variable holder (e.g., by assigning it
  to `$this->form` in the action), it will not be accessible to JPUP.

#### Testing Emails
To interact with Symfony's built in mailer, use the Mailer plugin:

<pre>
# sf_test_dir/functional/frontend/contactus/reportissue.php

&lt;?php
class frontend_main_reportissueTest extends Test_Case_Functional
{
  public function testSuccess(  )
  {
    /* The Mailer plugin has to be activated before it can be used. */
    $this->_browser->usePlugin('mailer');

    $email = 'functional_tester@jwt.com';

    $this->_browser
      ->get('/contactus/reportissue')
      ->click('Submit', array(
          'issue' => array(
            'firstname'   => 'Functional',
            'lastname'    => 'Tester',
            'email'       => $email,
            'description' => 'This is a test form submission.'
          )
        ));

    ... snip ...

    /* Test emails sent from form submission. */
    $Mailer = $this->_browser->getMailer();

    $this->assertEquals(
      2,
      $Mailer->countMessages(),
      'Expected correct number of emails to be sent.'
    );

    /* Get the email that was sent to the user. */
    $Message = $Mailer->getMessageWith('to', $email);
    $this->assertNotNull(
      $Message,
      'Expected an email to be sent to the user.'
    );

    $this->assertEquals(
      sfConfig::get('app_webmaster_email'),
      $Message->getFrom(),
      'Expected email sent to user to be from system administrator.'
    );
  }
}
</pre>

* As it is not used very often, the Mailer plugin is not enabled by default.
  To use it in your test, call `$this->_browser->usePlugin('mailer')`.

* The Mailer plugin only interacts with Symfony's built-in SwiftMailer emailer.
  If you are using your own mailer, you will need to write your own interface
  for interacting with it for testing.

#### Testing Redirects
The Request and Response plugins provide access to forwarding and redirecting
  information, respectively.

* For an explanation of the difference between redirecting and forwarding,
  see [this blog post](http://firebird84vn.wordpress.com/2007/06/30/skipping-to-another-action/).

<pre>
# sf_test_dir/functional/frontend/contactus/reportissue.php

&lt;?php
class frontend_contactus_reportissueTest extends Test_Case_Functional
{
  public function testSubmission(  )
  {
    $this->_browser->get('/contactus/reportissue');
    $this->assertStatusCode(200);

    $this->_browser->click('Submit', array(...));

    ... snip ...

    $this->assertEquals(
      '/contactus/reportissue/thankyou',
      $this->_browser->getResponse()->getRedirectURL(),
      'Expected browser to be redirected to the confirmation page.'
    );
  }

  public function testForwardIfNoSubmission(  )
  {
    $this->_browser->get('/contactus/reportissue/thankyou');

    $this->assertEquals(
      'contactus/reportissue',
      $this->_browser->getRequest()->getForwardString(),
      'Expected request to be forwarded to form.'
    );
  }
}
</pre>

* The test browser will not follow redirects automatically.  To follow a
  redirect, call `$this->_browser->followRedirect()`.

  This only applies to redirects; forwards are followed automatically.

#### Troubleshooting 500 Errors
When applications generate 500 errors, Symfony will forward the request to a
  generic error page, which makes troubleshooting these problems in functional
  tests particularly frustrating.

Fortunately, the Error plugin makes it easy to get information (including a
  stack trace) about any uncaught exceptions the application generates:

<pre>
# sf_test_dir/functional/frontend/contactus/reportissue.php

&lt;?php
class frontend_main_reportissueTest extends Test_Case_Functional
{
  public function testSuccess(  )
  {
    $this->_browser
      ->get('/contactus/reportissue')
      ->click('Submit', array(
          'issue' => array(
            'firstname'   => 'Functional',
            'lastname'    => 'Tester',
            'email'       => $email,
            'description' => 'This is a test form submission.'
          )
        ));

    /* For some reason, the request is generating a 500 error.  Find out what
     *  the problem is:
     */
    echo
      PHP_EOL, PHP_EOL, $this->_browser->getError()
      PHP_EOL, PHP_EOL, $this->_browser->getError()->getTraceAsString();


    /* This assertion will still fail, but not before we get to see what's going
     *  on.
     */
    $this->assertStatusCode(200);
  }
}
</pre>

* Since 500 errors are generally not considered to be desirable behavior, you
  will probably end up using this plugin to debug your application rather than
  as part of a test or assertion.

#### Inspecting Log Messages
Occasionally, it can be useful to look at the log messages generated by a
  particular request.  By default, Symfony will log messages to
  `sf_log_dir/appname_test.log`, but this file tends to get rather large, and
  it can be cumbersome to scroll through it to identify the failed request.

JPUP provides an alternative interface for interacting with Symfony's logger.
  The Logger plugin will inject an `sfVarLogger` instance into the context so
  that you can inspect log messages in your test.

To inject the logger, you will need to first call
  `$this->_browser->usePlugin('logger')`.

Once the logger has been injected, you can inspect the log messages generated by
  the most recent request like this:

<pre>
# sf_test_dir/functional/frontend/contactus/reportissue.php

&lt;?php
class frontend_main_reportissueTest extends Test_Case_Functional
{
  public function testSuccess(  )
  {
    /* Inject the logger so that we can inspect log messages. */
    $this->_browser->usePlugin('logger');

    $this->_browser
      ->get('/contactus/reportissue')
      ->click('Submit', array(
          'issue' => array(
            'firstname'   => 'Functional',
            'lastname'    => 'Tester',
            'email'       => $email,
            'description' => 'This is a test form submission.'
          )
        ));

    /* For some reason, the request is generating a 404.  Maybe the application
     *  logs will hold some clues.
     */
    echo $this->_browser->getLogger(), PHP_EOL;

    /* This assertion will still fail, but not before we get to see what's going
     *  on.
     */
    $this->assertStatusCode(200);
  }
}
</pre>

Note that the logger will be injected after the context loads its factories,
  so there will not be any log messages from factory initialization (such as
  connecting routes).

You can work around this by instructing Symfony to load an `sfVarLogger` in
  your `factories.yml` file.  The Logger plugin will first check to see if an
  `sfVarLogger` has been added to the context before injecting its own.

<pre>
# sf_apps_dir/frontend/config/factories.yml:

test:
  logger:
    class:    sfAggregateLogger
    param:
      level:    debug
      loggers:
        sf_var_logger:
          class:  sfVarLogger
          param:
            level:  debug
</pre>

With the above configuration in place in your application's `factories.yml`
  file, you will be able to inspect all application log messages in your test
  case, including any generated while initializing the context's factory
  objects (but be aware that this comes at a cost; the `sfVarLogger` will be
  created and populated regardless of whether you use it!).

# Database Interaction
Note that JPUP is currently only compatible with Doctrine.

## Configuration
Before running any tests, JPUP first verifies to make sure that a distinct
  "test" DSN has been specified in databases.yml and that the active Doctrine
  connection is using the correct DSN.

* If any problems are detected with the test database configuration, JPUP
  outputs an error message and exits immediately to prevent any tests from
  running (and potentially corrupting production data).

To specify a separate DSN for testing, add the following lines to your
  databases.yml file:

<pre>
# config/databases.yml

test:
  doctrine:
    class: sfDoctrineDatabase
    param:
      dsn: "mysql:host=&lt;hostname>;dbname=&lt;test db name>"
      username: "&lt;username>"
      password: "&lt;password>"
</pre>

Replace the values in brackets above with ones that correspond to your database setup.

* Make sure that you also create the test database and assign user privileges if
    necessary.

* If your application does not use a database, you can disable these checks
  (and all database connectivity) by setting `use_database` to `false` in
  `apps/*/config/settings.yml`:

<pre>
# apps/frontend/config/settings.yml

all:
  .settings:
    ... snip ...
    use_database:           false
</pre>

## Pre-Test Checks
Before running each test, JPUP automatically flushes the database:

* Before the first test runs, JPUP completely destroys and rebuilds the
  test database:

    * Drop and recreate the test database.

       *  This step is roughly equivalent to calling `php ./symfony
          doctrine:drop-db --env=test` followed by `php ./symfony
          doctrine:build-db --env=test`.

    * Rebuild all tables from model classes.

       *  This step is roughly equivalent to calling `php ./symfony
          doctrine:insert-sql --env=test`.

       *  Because tables are built from model classes, there is no need to worry
          about applying database change scripts or having an up-to-date
          schema.yml file for testing (though it is still a good idea to have
          these things for many other reasons!).

* For efficiency, subsequent tests will truncate all tables instead of
  rebuilding the entire database.

Once the data has been flushed, JPUP loads global test data fixtures, if any
  exist (see below).

Note that JPUP flushes the database **before** each test, not **after** it.
  This is by design; if you want to inspect the state of the database after a
  failed test, use `exit()` to halt test execution before the failing assertion,
  then use your favorite DB client application to examine the test database.

## Loading Fixtures
For efficiency, only global test fixtures are loaded by default (more on this
  in a bit).

To load additional test fixtures, call `$this->loadFixture()` in your test case,
  e.g.:

<pre>
# sf_test_dir/unit/Hello.php

&lt;?php
class HelloTest extends Test_Case_Unit
{
  protected function _setUp(  )
  {
    $this->loadFixture('hello.yml');
  }
}
</pre>

### Caveats
* The database gets flushed in between tests, so you will need to make sure your
  test case loads the appropriate fixtures before every test that uses them.

    * Consider leveraging `_setUp()` for fixtures that must be loaded for every
      test in a test case.

* All test fixtures must be located in `sf_test_dir/fixtures`.  The fixture loader will
  not load any fixture files outside this directory.

  Note that this means that you cannot load production data fixtures (located in
  `sf_root_dir/data/fixtures`) in your test cases.  This is intentional; JPUP is
  designed to test code, not data.

* By default, `loadFixture()` will not load a fixture more than once during a
  test.  There is a way to force it to load a fixture multiple times (but be
  wary of infinite loops!).  See the API documentation for more information.

### Global Fixtures
As mentioned above, there is a category of fixture that does get loaded
  automatically before each test, known as global fixtures.

Global fixtures are named like `_global.*` (e.g., `_global.yml`, `_global.php`,
  etc.).  In other words there can be one global fixture for each fixture type.

Global fixtures are loaded using the same mechanism as other fixtures, so they
  must be located in `sf_test_dir/fixtures` and have the same limitations and
  features as any other fixture.

### Fixture Types
JPUP supports loading two different fixture types:  YAML and PHP.

#### YAML
JPUP can load YAML fixture files similarly to the way Symfony's
  `doctrine:data-load` task operates.  To load a YAML fixture file, provide the
  name of the file to `$this->loadFixture()`:

<pre>
# sf_test_dir/unit/MyTest.php

&lt;?php
class MyTest extends Test_Case_Unit
{
  protected function _setUp(  )
  {
    $this->loadFixture('users.yml');
  }
}
</pre>

Here's what the YAML fixture looks like:

<pre>
# sf_test_dir/fixtures/users.yml

User:
  admin:
    username: admin
    password: password
    active:   1
  editor:
    username: editor
    password: password
    active:   1
  inactive:
    username: haxor
    password: 1337
    active:   0
</pre>

As with other Symfony YAML files, you can include PHP code and Symfony config
  values:

<pre>
# sf_test_dir/fixtures/users.yml

User:
  admin:
    username: %APP_DEFAULT_ADMIN_USERNAME%
    password: &lt;?php echo sha1('saltpasswordsalt'); ?>
    active:   1
</pre>

If you need to execute a large amount of PHP code, or if you need to set up
  inter-fixture relationships, you might find it more effective to use a PHP
  fixture file instead.

#### PHP
Load a PHP fixture file identically to the way you would load a YAML fixture
  file, except that the filename will have a '.php' extension rather than
  '.yml':

<pre>
# sf_test_dir/unit/lib/MyClass.php

&lt;?php
class MyClassTest extends Test_Case_Unit
{
  protected function _setUp(  )
  {
    $this->loadFixture('articles.php');
  }
}
</pre>

A PHP fixture file can contain any PHP code.

* With great power comes great responsibility.  Try to keep your fixtures short,
  simple and focused.  The last thing you want is to have to write test cases
  for your data fixtures!

##### Loading Other Fixtures
You can load other fixtures from a PHP fixture file by calling
  `$this->loadFixture()` just like you would from a test case:

<pre>
# sf_test_dir/fixtures/articles.php

&lt;?php
$this->loadFixture('sites.yml');
$this->loadFixture('categories.php');
</pre>

##### Sharing Variables
PHP fixture files can share variables between one other.  To make a variable
  accessible to other fixture files, assign it as a property of `$this` in the
  fixture file:

<pre>
# sf_test_dir/fixtures/articles.php

&lt;?php
$this->Article = new Article();
$this->Article->setTitle('Hello, world!');
$this->Article->save();
</pre>

Because `articles.php` defines `$this->Article`, any subsequently-loaded fixture
  can access it:

<pre>
# sf_test_dir/fixtures/categories.php

&lt;?php
/* Load dependency fixture. */
$this->loadFixture('articles.php');

$Cat = new Category();
$Cat->setTitle('Standard Content');

/* Associate the Article object from sf_test_dir/fixtures/articles.php with
 *  the Category object.
 */
$Cat->setArticle($this->Article);

$Cat->save();
</pre>

If you write a fixture that relies on other fixtures being loaded, it is
  recommended that you explicitly call `$this->loadFixture()` in the fixture
  itself to make sure that its dependencies get loaded.

  * In other words, don't rely on the test case to manage fixture dependencies.

  * Remember that `loadFixture()` will not load a fixture more than once per
    test by default, so when in doubt it is always better to include too many
    calls to `loadFixture()` than too few.

##### Accessing Fixture Variables in Test Cases
You can also access shared fixture variables in test cases.  Use
  `$this->getFixtureVar()` to access them:

<pre>
# sf_test_dir/fixtures/site.php

&lt;?php
$this->TestSite = new Site();
$this->TestSite->setName('Tanis Dig');
$this->TestSite->save();
</pre>

<pre>
# sf_test_dir/unit/model/SiteTable.php

&lt;?php
class SiteTableTest extends Test_Case_Unit
{
  public function testFetchByName(  )
  {
    $this->loadFixture('site.php');

    /* References $this->TestSite from the fixture file. */
    $controlID = $this->getFixtureVar('TestSite')->getId();

    $this->assertEquals(
      $controlID,
      Doctrine::getTable('Site')->fetchByName('Tanis Dig')->getId(),
      'Expected fetched Site object to have correct ID.'
    );
  }
}
</pre>

##### Defining Constants
Because the database gets flushed before every test, it might be necessary to
  load a given test fixture several times over the course of a test case.  This
  makes it a little tricky to define constants in a test fixture.

The PHP fixture loader provides a solution:

<pre>
# sf_test_dir/fixtures/articles.php

&lt;?php
/* Defines the constant TEST_ARTICLE_TITLE if not already defined. */
$this->define('TEST_ARTICLE_TITLE', 'Hello, World!');

$Node = new Article();
$Node->setTitle(TEST_ARTICLE_TITLE);
$Node->save();
</pre>

The constant is, naturally, accessible in test cases as well:

<pre>
# sf_test_dir/unit/ArticleTable.php

&lt;?php
class ArticleTableTest extends Test_Case_Unit
{
  public function testFetchByTitle(  )
  {
    $this->loadFixture('articles.php');

    /* References TEST_ARTICLE_TITLE constant defined in the articles.php
     *  fixture.
     */
    $Article = Doctrine::getTable('Article')->fetchByTitle(TEST_ARTICLE_TITLE);
    $this->assertFalse(
      $Article->isNew(),
      'Expected fetchByTitle() to find the existing article.'
    );
  }
}
</pre>

As with shared fixture variables, constants defined in fixture files are not
  namespaced.  If you are trying to `define()` a constant that was already set
  in another test fixture (or is a built-in PHP constant, set by the Symfony
  framework, etc.), it will silently fail.

To avoid this problem, it is recommended that you adopt a naming convention
 (such as prepending "TEST_" to all test constant names).

## Flushing the Database Manually
You can flush the database manually in your test by calling
  `$this->flushDatabase()`.

For example, consider this functional test that verifies that content can be
  exported from one environment and re-imported onto a second environment.

After generating the export files, we flush the database and load a new
  fixture to simulate a separate instance of the application.

<pre>
# sf_test_dir/functional/backend/migrate/index.php

&lt;?php
class backend_migrate_indexTest extends Test_Case_Functional
{
  public function testContentMigration(  )
  {
    /* Init the source environment. */
    $this->loadFixture('content_migration_source.php');
    sfConfig::set('app_which_env', 'test-');

    /* Generate content migration files. */
    $this->_browser->get('/backend.php/migrate/export', array(
      'site_id' => '1',
      'dest'    => 'test2-'
    ));

    /* Verify the export files were created successfully. */
    ... assertions go here ...

    /* Pretend we're now on the destination environment. */
    $this->flushDatabase();

    $this->loadFixture('content_migration_destination.php');
    sfConfig::set('app_which_env', 'test2-');

    /* Load content migration files into destination. */
    $this->_browser->get('/backend.php/migrate/import', array(
      'site_id' => '1',
      'from'    => 'test-'
    ));

    /* Verify that objects were imported successfully. */
    ... assertions go here ...
  }
}
</pre>

* `flushDatabase()` takes an optional `$rebuild` parameter that will force it to
  drop and rebuild the entire database rather than just truncating all the data.

  Note that JPUP does **not** rebuild the database between tests; it only
  flushes the data.  If you are testing a script that modifies the structure of
  the database, be sure to call `$this->flushDatabase(true)` when the test is
  finished (preferably in such a way that a failed assertion won't cause it to
  get skipped!).

# File Uploads
JPUP requires that your project have a separate uploads directory for testing so
  that test execution doesn't overwrite production files.

Even if the code you are testing does not use file uploads explicitly, JPUP
  still requires that you define a separate test upload directory, as the
  `Test_Browser` class might automatically write to that directory during a
  `post()` operation.

* Plus, it's one less thing to worry about when you start testing actions that do
  accept file uploads.

To specify an uploads directory for JPUP, add the following line to the
  settings.yml file for your application:

<pre>
# apps/frontend/config/settings.yml

test:
  .settings
    ... snip ...
    # Add this line:
    upload_dir:             %SF_TEST_CACHE_DIR%/uploads
</pre>

* You will also need to make sure that the test uploads directory exists and is
  writable.

## Removing Uploaded Files
JPUP will automatically remove all files in the test uploads directory before
  each test.

If you need to clear out the uploads directory mid-test, you can call
  `$this->flushUploads()`:

<pre>
# sf_test_dir/functional/frontend/account/profile.php

&lt;?php
class frontend_account_profileTest extends Test_Case_Functional
{
  public function testDetectMissingAvatar(  )
  {
    /* User uploads an avatar as normal. */
    $this->_browser
      ->get('/account/profile/avatar')
      ->click('Submit', array(
          'avatar' => sfConfig::get('sf_fixture_dir') . '/uploads/avatar.jpg'
        ));

    /* Pretend the upload inexplicably failed. */
    $this->flushUploads();

    /* Follow the redirect back to the profile page. */
    $this->_browser->followRedirect();

    $this->assertEquals(
      sfConfig::get('app_missing_avatar'),
      $this->_browser->getContent()->select('#avatar')->getAttribute('src'),
      'Expected "missing avatar" graphic to display in place of missing avatar.'
    );
  }
}
</pre>

# sfConfig
JPUP automatically restores `sfConfig` values between tests, so you do not have
  to manually reset any `sfConfig` changes during your tests.

If you wish to revert all `sfConfig` values mid-test, call
  `$this->flushConfigs()`:

<pre>
# sf_test_dir/unit/lib/ConfigWatcher.class.php

&lt;?php
class ConfigWatcherTest extends Test_Case_Unit
{
  public function testSuccess(  )
  {
    ConfigWatcher::init();

    /* Change a config value. */
    $key = 'app_some_value';
    sfConfig::set($key, 100);

    $this->assertTrue(
      ConfigWatcher::isModified($key),
      'Expected ConfigWatcher to notice when config value was modified.'
    );

    /* Reset the config value. */
    $this->flushConfigs();

    $this->assertFalse(
      ConfigWatcher::isModified($key),
      'Expected ConfigWatcher to notice when modified config value was reset.'
    );
  }
}
</pre>

# Error Reporting
By default, Symfony turns off `E_NOTICE` errors for the `test` environment.
  This can prevent PHPUnit from catching genuine logic errors, so JPUP requires
  that `error_reporting` be set to its most verbose setting in settings.yml.

To fix `error_reporting`, look for the following setting in
  `apps/*/config/settings.yml`:

<pre>
# apps/frontend/config/settings.yml

test:
  .settings:
    error_reporting:        &lt;?php echo ((E_ALL | E_STRICT) ^ E_NOTICE)."\n" ?>
</pre>

And change it to look like this:
<pre>
# apps/frontend/config/settings.yml

test:
  .settings:
    error_reporting:        &lt;?php echo (E_ALL | E_STRICT)."\n"; ?>
</pre>

* This feature cannot currently be bypassed.  If your application relies on
  code that generates `E_NOTICE` errors (and you don't want to fix them), you
  will need to make use of PHP's [`@` operator](http://php.net/language.operators.errorcontrol).

# Bootstrap Script
When running PHPUnit Symfony tasks, JPUP will look for and execute the
  bootstrap file in `sf_test_dir/bootstrap/phpunit.php`.  If you have any code that
  needs to be executed before any tests are run, put it in this bootstrap file.

For example, if your project does not have a `frontend` application, you will
  need to specify a different default application for your tests in the
  bootstrap script as noted in the following section.

* The code in this file is executed before an `sfContext` instance gets
  initialized.

* This file is optional.  If JPUP does not find the bootstrap script, it will
  not trigger any errors.

# Specifying the Application Name
By default, JPUP runs tests using the `frontend` application context.  If your
  test (unit or functional) should be run with a different configuration, add an
  `$_application` property to your test class:

<pre>
# sf_test_dir/functional/backend/config/set.php

&lt;?php
class backend_config_SetTest extends Test_Case_Functional
{
  protected
    $_application = 'backend';

  ... snip ...
}
</pre>

* This feature is primarily intended for functional tests.  Unit tests, by
  definition, should not need to rely on a specific application's configuration.
  If you find yourself using this feature in your unit tests, you should
  strongly consider whether you can use [dependency injection](http://components.symfony-project.org/dependency-injection/trunk/book/01-Dependency-Injection)
  or some other pattern to decouple your library class from your application.

* If your project does not have a `frontend` application, you will need to
  specify a different default value in your bootstrap script:

<pre>
# sf_test_dir/bootstrap/phpunit.php

&lt;?php
Test_Case::setDefaultApplicationName('appname');
</pre>

# Running Tests
JPUP includes a number of Symfony tasks that you can use to run your tests:

- To run ALL tests: `php ./symfony phpunit:all`
- To run unit tests: `php ./symfony phpunit:unit`
- To run functional tests: `php ./symfony phpunit:functional`

Note:  JPUP is not compatible with Symfony's built-in test tasks.  Do not expect
  `php ./symfony test:*` tasks to work with PHPUnit test cases!

### phpunit:all
<pre>
Usage:
 symfony phpunit:all [-f|--filter="..."] [-g|--groups="..."] [-v|--verbose]

Options:
 --filter   (-f) Regex used to filter tests; only tests matching the filter will be run.
 --groups   (-g) Only run tests from the specified group(s).
 --verbose  (-v) If set, PHPUnit will output additional information (e.g. test names).

Description:
 Runs all PHPUnit tests for the project.
</pre>

* JPUP currently runs all tests in the same PHP instance.  Be aware that a fatal
  runtime error will be generated if there are tests for two classes in
  different applications with the same name (e.g., `mainActions`).

### phpunit:unit
<pre>
Usage:
 symfony phpunit:unit [-f|--filter="..."] [-g|--groups="..."] [-v|--verbose] [path1] ... [pathN]

Arguments:
 path       Specify the relative paths to specific test files and/or directories under sf_test_dir/unit.  If no arguments are provided, all unit tests will be run.

Options:
 --filter   (-f) Regex used to filter tests; only tests matching the filter will be run.
 --groups   (-g) Only run tests from the specified group(s).
 --verbose  (-v) If set, PHPUnit will output additional information (e.g. test names).

Description:
 Runs PHPUnit unit tests for the project.
</pre>

* The `path` argument should be a relative path under `sf_test_dir/unit`.  It can
  reference directories or files, and you may omit '.php' or '.class.php' (where
  applicable) from file paths.

  For example, to run the unit tests located in
  `sf_test_dir/unit/lib/WidgetService.class.php` and
  `sf_test_dir/unit/lib/model/doctrine/*`, both of these commands will work:

  * `./symfony phpunit:unit lib/WidgetService lib/model/doctrine`
  * `./symfony phpunit:unit lib/WidgetService.class.php lib/model/doctrine/*`

* If you organize and name your unit test files to mirror your production code
  files, you can leverage readline's autocompletion feature to save yourself
  some typing.

### phpunit:functional
<pre>
Usage:
 symfony phpunit:functional [-f|--filter="..."] [-g|--groups="..."] [-v|--verbose] [path1] ... [pathN]

Arguments:
 path       Specify the relative paths to specific test files and/or directories under sf_test_dir/functional.  If no arguments are provided, all functional tests will be run.

Options:
 --filter   (-f) Regex used to filter tests; only tests matching the filter will be run.
 --groups   (-g) Only run tests from the specified group(s).
 --verbose  (-v) If set, PHPUnit will output additional information (e.g. test names).

Description:
 Runs PHPUnit functional tests for the project.
</pre>

* The `path` argument functions identically to its counterpart for
  `phpunit:unit`, except that the specified paths should be relative to
  `sf_test_dir/functional`.

  For example, to run the functional tests located in
  `sf_test_dir/functional/frontend/api/publish.php` and
  `sf_test_dir/functional/frontend/ajax/*`, both of these commands will work:

  * `./symfony phpunit:functional frontend/api/publish frontend/ajax`
  * `./symfony phpunit:functional frontend/api/publish.php frontend/ajax/*`

* JPUP currently runs all tests in the same PHP instance.  Be aware that a fatal
  error will be generated if there are tests for two classes in different
  applications with the same name (e.g., `mainActions`).