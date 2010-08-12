<?php
/** Runs all functional tests for a project.
 *
 * @package sfJwtPhpUnitPlugin
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
        'If set, only run tests from the specified application.',
        null
      ),

      new sfCommandOption(
        'module',
        'm',
        sfCommandOption::PARAMETER_REQUIRED,
        'If set, only run tests from the specified module.',
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

    if( empty($params['application']) )
    {
      if( ! empty($params['module']) )
      {
        throw new InvalidArgumentException(
          'Specify a value for --application when using the --module option.'
        );
      }
    }
    else
    {
      $this->_type .= '/' . $params['application'];
      if( ! empty($params['module']) )
      {
        $this->_type .= '/' . $params['module'];
      }
    }

    $this->_runTests($this->_type, $this->_validatePhpUnitInput($args, $opts));
  }
}