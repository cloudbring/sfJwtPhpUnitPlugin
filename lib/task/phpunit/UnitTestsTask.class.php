<?php
/** Runs all unit tests for a project.
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.task
 */
class UnitTestsTask extends BasePhpunitTask
{
  public function configure(  )
  {
    parent::configure();

    $this->addArguments(array(
      new sfCommandArgument(
        'path',
        sfCommandArgument::OPTIONAL | sfCommandArgument::IS_ARRAY,
        'Specify the relative path to the test file or directory.',
        null
      )
    ));

    $this->name = 'unit';
    $this->briefDescription = 'Runs all PHPUnit unit tests for the project.';

    $this->detailedDescription = <<<END
Runs all PHPUnit unit tests for the project.
END;

    $this->_type = 'unit';
  }

  public function execute( $args = array(), $opts = array() )
  {
    $params       = $this->_validateInput($args, $opts);
    $this->_paths = (array) $params['path'];

    $this->_runTests($this->_validatePhpUnitInput($args, $opts));
  }
}