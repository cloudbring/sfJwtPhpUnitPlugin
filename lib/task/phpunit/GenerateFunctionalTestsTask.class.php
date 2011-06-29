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

/** Generates functional test cases for a project.
 *
 * @author Phoenix Zerin <phoenix.zerin@jwt.com>
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.task.phpunit
 */
class GenerateFunctionalTestsTask extends BasePhpunitGeneratorTask
{
  /** @todo Support for customized factories.yml (e.g., cannot rely on
   *    sfPatternRouting).
   */
  public function configure(  )
  {
    parent::configure();

    $this->name = 'generate-functional';
    $this->briefDescription = 'Generate a functional test case.';

    $this->detailedDescription = <<<END
Generates a skeleton functional test case for your project.
END;

    $this->addArguments(array(
      new sfCommandArgument(
        'route',
        sfCommandArgument::REQUIRED,
        'Route name or URL path to module/action (same syntax as gen_url()).',
        null
      )
    ));

    $this->addOptions(array(
      new sfCommandOption(
        'application',
        'a',
        sfCommandArgument::OPTIONAL,
        'Application name.',
        $this->_guessDefaultAppName()
      )
    ));
  }

  public function execute( $args = array(), $opts = array() )
  {
    $params = $this->_consolidateInput($args, $opts, array(
      'route'   => array(),
      'verbose' => null
    ), true);

    $context    = sfContext::createInstance($this->configuration);
    $controller = $context->getController();

    $route = $controller->convertUrlStringToParameters($params['route']);

    /** @kludge convertUrlStringToParameters() will match the route name, but it
     *    won't auto-populate default parameters.
     */
    if( ! empty($route[0]) )
    {
      /** @kludge No getRoute() method? Really, sfPatternRouting? */
      $routes = $context->getRouting()->getRoutes();
      if( isset($routes[$route[0]]) )
      {
        $route[1] = array_merge(
          $route[1],
          $routes[$route[0]]->getDefaultParameters()
        );
      }
    }

    if( $params['verbose'] )
    {
      $this->logSection('info', sprintf(
        'Best match for route "%s":  %s',
          $params['route'],
          print_r($route, true)
      ));
    }

    if( empty($route[1]['module']) or empty($route[1]['action']) )
    {
      throw new DomainException(sprintf(
        'No module/action found that matches "%s".',
          $params['route']
      ));
    }

    $app    = $this->configuration->getApplication();
    $module = $route[1]['module'];
    $action = $route[1]['action'];

    if( ! $controller->actionExists($module, $action) )
    {
      throw new InvalidArgumentException(sprintf(
        '"%s" matches %s route /%s/%s, but no matching action found.',
          $params['route'],
          $app,
          $module,
          $action
      ));
    }

    /* Determine target file path. */
    $path   = $this->_genPath(array($app, $module, $action . '.php'), false);
    $target = $this->_getBaseDir('test', array('functional')) . $path;

    if( $params['verbose'] )
    {
      $this->logSection('info', sprintf(
        'Target test case file is %s.',
          $target
      ));
    }

    if( file_exists($target) )
    {
      throw new RuntimeException(sprintf(
        'Test case file already exists at sf_test_dir/functional/%s.',
          $path
      ));
    }

    /* Locate the skeleton test case. */
    $skeleton = $this->_findSkeletonFile('functional.php');

    if( $params['verbose'] )
    {
      $this->logSection('info', sprintf(
        'Using skeleton test case at %s.',
          $skeleton
      ));
    }

    /* Time to start doing things. */
    $this->_copySkeletonFile($skeleton, $target);

    $ref = new ReflectionObject($controller->getAction($module, $action));

    /* Replace tokens. */
    $tokens = array(
      'URL'         => sprintf('/%s/%s', $module, $action),
      'ROUTENAME'   => sprintf('%s_%s_%s', $app, $module, $action),
      'APPNAME'     => $app,
      'PROJECTNAME' => $this->_guessPackageName($ref),
      'SUBPACKAGE'  => $this->_guessSubpackageName($ref, 'test')
    );

    $this->getFilesystem()->replaceTokens($target, '##', '##', $tokens);
  }
}