<?php
/** Runs all functional tests for a project.
 *
 * @package jwt
 * @subpackage lib.task
 */
class FunctionalTestsTask extends BasePhpunitTask
{
  public function configure(  )
  {
    parent::configure();

    $this->addOptions(array(
      new sfCommandOption(
        'application',
        'a',
        sfCommandOption::PARAMETER_REQUIRED,
        'Run tests from the specified application.',
        'frontend'
      ),

      new sfCommandOption(
        'module',
        'm',
        sfCommandOption::PARAMETER_REQUIRED,
        'If set, only tests from the specified module will be run.',
        null
      )
    ));

    $this->name = 'functional';
    $this->briefDescription = 'Runs all PHPUnit functional tests for the project.';

    $this->detailedDescription = <<<END
Runs all PHPUnit functional tests for the project.
END;

    $this->_type = 'functional';
  }

  public function execute( $args = array(), $opts = array() )
  {
    $params = $this->_validateInput($args, $opts);

    $this->_type .= '/' . $params['application'];
    if( ! empty($params['module']) )
    {
      $this->_type .= '/' . $params['module'];
    }

    $this->_runTests($this->_type, $this->_validatePhpUnitInput($args, $opts));
  }
}