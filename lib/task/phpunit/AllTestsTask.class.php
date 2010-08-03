<?php
/** Runs all tests for a project.
 *
 * @package jwt
 * @subpackage lib.task
 */
class AllTestsTask extends BasePhpunitTask
{
  public function configure(  )
  {
    parent::configure();

    $this->name = 'all';
    $this->briefDescription = 'Runs all PHPUnit tests for the project.';

    $this->detailedDescription = <<<END
Runs all PHPUnit tests for the project.
END;

    $this->_type = '';
  }
}