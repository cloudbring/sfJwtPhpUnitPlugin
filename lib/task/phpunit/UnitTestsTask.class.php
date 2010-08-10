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

    $this->addOptions(array(
      new sfCommandOption(
        'subtype',
        's',
        sfCommandOption::PARAMETER_REQUIRED,
        'If set, only tests from the specified subdirectory within the unit tests directory will be run.',
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
    $params = $this->_validateInput($args, $opts);

    if( ! empty($params['subtype']) )
    {
      $this->_type .= '/' . $params['subtype'];
      unset($params['subtype']);
    }

    $this->_runTests($this->_type, $this->_validatePhpUnitInput($args, $opts));
  }
}