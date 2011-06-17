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

/** Generates unit test cases for a project.
 *
 * @author Phoenix Zerin <phoenix.zerin@jwt.com>
 *
 * @package sfJwtPhpUnitPlugin
 * @subpackage lib.task.phpunit
 */
class GenerateUnitTestsTask extends BasePhpunitGeneratorTask
{
  /**
   * @todo Add tokens option.
   * @todo Add option to not generate skeleton tests.
   */
  public function configure(  )
  {
    parent::configure();

    $this->name = 'generate-unit';
    $this->briefDescription = 'Generate a unit test case.';

    $this->detailedDescription = <<<END
Generates a skeleton unit test case for your project.
END;

    $this->addArguments(array(
      new sfCommandArgument(
        'class',
        sfCommandArgument::REQUIRED,
        'Name of the class to generate unit tests for.'
      )
    ));
  }

  public function execute( $args = array(), $opts = array() )
  {
    $params = $this->_consolidateInput($args, $opts, array(
      'class' => null
    ));

    $path = $this->_findClassFile($params['class']);

    $ref = new ReflectionClass($params['class']);
    if( $ref->isAbstract() )
    {
      throw new InvalidArgumentException(sprintf(
        'Cannot generate tests for abstract class %s.',
          $params['class']
      ));
    }

    $source = $this->_getBaseDir('root') . $path;
    $target = $this->_getBaseDir('test') . $path;

    /* Verify the test file doesn't already exist. */
    if( file_exists($target) )
    {
      throw new RuntimeException(sprintf(
        'Test file already exists at sf_test_dir/%s.',
          $path
      ));
    }

    /* Check for custom skeleton file at
     *  sf_data_dir/skeleton/phpunit/unit.php.
     */
    $skeleton = $this->_getBaseDir('data', array('skeleton', 'phpunit')) . 'unit.php';
    if( ! is_file($skeleton) )
    {
      $skeleton = $this->_genPath(array(
        dirname(__FILE__),
        'skeleton',
        'unit.php'
      ), false);

      if( ! is_file($skeleton) )
      {
        throw new RuntimeException(sprintf(
          'Cannot find skeleton class file at %s.',
            $skeleton
        ));
      }
    }

    /* Set up the directory structure if necessary. */
    $fs = $this->getFilesystem();
    if( ! $fs->mkdirs(dirname($target), 0755) )
    {
      throw new RuntimeException(sprintf(
        'Failed to create directory %s.',
          $target
      ));
    }

    /* Copy the skeleton file into place. */
    $fs->copy($skeleton, $target);
    if( ! is_file($target) )
    {
      throw new RuntimeException(sprintf(
        'Failed to copy skeleton class file %s to %s.',
          $skeleton,
          $target
      ));
    }

    /* Replace tokens. */
    $fs->replaceTokens($target, '##', '##', array(
      'CLASSNAME'   => $ref->getName(),
      'PACKAGE'     => $this->_guessPackageName($ref),
      'SUBPACKAGE'  => $this->_guessSubpackageName($ref, 'test'),

      'TESTS'       => $this->_generateTests($ref)
    ));
  }

  /** Finds the source file for a given class.
   *
   * @param string(classname) $classname The name of the class to check.
   *
   * @return string(relpath) The path to the class file, relative to
   *  sf_root_dir.
   *
   * @throws InvalidArgumentException if $classname is not a valid class.
   * @throws RuntimeException         if the class file is outside sf_root_dir.
   */
  protected function _findClassFile( $classname )
  {
    /* Induce the autoloader to rebuild in case cache was recently cleared. */
    $this->reloadAutoload();

    /* Locate the file for the class and make sure it's local to the project. */
    if( $file = sfSimpleAutoload::getInstance()->getClassPath($classname) )
    {
      $root = $this->_getBaseDir();

      if( substr($file, 0, strlen($root)) == $root )
      {
        return substr($file, strlen($root));
      }
      else
      {
        throw new RuntimeException(sprintf(
          'Class file %s for class %s is outside sf_root_dir.',
            $file,
            $classname
        ));
      }
    }
    else
    {
      throw new InvalidArgumentException(sprintf(
        'No class file found for class "%s" (is the autoload cache stale?).',
          $classname
      ));
    }
  }

  /** Generates skeleton tests for a class.
   *
   * @param ReflectionClass $ref
   *
   * @return string(php) Intended for use in token replacement.
   */
  protected function _generateTests( ReflectionClass $ref )
  {
    $template = <<<END


  public function test%s(  )
  {
    \$this->markTestIncomplete('Not implemented yet.');
  }
END;

    $str = '';

    /* @var $meth ReflectionMethod */
    foreach( $ref->getMethods(ReflectionMethod::IS_PUBLIC) as $meth )
    {
      $str .= sprintf($template, $meth->getName());
    }

    return $str;
  }
}