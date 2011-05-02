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

/** Runs all functional tests for a project.
 *
 * @author Phoenix Zerin <phoenix.zerin@jwt.com>
 * 
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.task
 */
class FunctionalTestsTask extends BasePhpunitTask
{
  public function configure(  )
  {
    parent::configure();

    $this->addArguments(array(
      new sfCommandArgument(
        'path',
        sfCommandArgument::OPTIONAL | sfCommandArgument::IS_ARRAY,
        'Specify the relative paths to specific test files and/or directories under sf_root_dir/test/functional.  If no arguments are provided, all functional tests will be run.',
        null
      )
    ));

    $this->name = 'functional';
    $this->briefDescription = 'Runs all PHPUnit functional tests for the project.';

    $this->detailedDescription = <<<END
Runs PHPUnit functional tests for the project.
END;

    $this->_type = 'functional';
  }

  public function execute( $args = array(), $opts = array() )
  {
    $params       = $this->_validateInput($args, $opts);
    $this->_paths = (array) $params['path'];

    $this->_runTests($this->_validatePhpUnitInput($args, $opts));
  }
}