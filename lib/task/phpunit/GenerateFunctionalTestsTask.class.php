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
    $allowed = array(
      'route'  => array()
    );

    $params = $this->_consolidateInput($args, $opts, $allowed, true);

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

    if( empty($route[1]['module']) or empty($route[1]['action']) )
    {
      throw new DomainException(sprintf(
        'No module/action found that matches "%s".',
          $params['route']
      ));
    }

    $module = $route[1]['module'];
    $action = $route[1]['action'];

    if( ! $controller->actionExists($module, $action) )
    {
      throw new InvalidArgumentException(sprintf(
        '"%s" matches %s route /%s/%s, but no matching action found.',
          $params['route'],
          $this->configuration->getApplication(),
          $module,
          $action
      ));
    }

    /* Determine target file path. */
    /* Verify the file doesn't already exist. */
    /* Check for skeleton at sf_data_dir/skeleton/phpunit/functional.php. */
    /* Create file structure if necessary. */
    /* Copy file. */
    /* Replace tokens. */
  }
}