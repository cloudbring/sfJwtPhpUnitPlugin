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

    $this->addOptions(array(
      new sfCommandOption(
        'no-tests',
        null,
        sfCommandOption::PARAMETER_NONE,
        'If set, an empty test case will be generated (no skeleton tests).'
      )
    ));
  }

  public function execute( $args = array(), $opts = array() )
  {
    $params = $this->_consolidateInput($args, $opts, array(
      'class'     => null,
      'no-tests'  => null,
      'token'     => array(),
      'verbose'   => null
    ), true);

    $path = $this->_findClassFile($params['class']);

    $ref = new ReflectionClass($params['class']);
    if( $ref->isAbstract() )
    {
      throw new InvalidArgumentException(sprintf(
        'Cannot generate tests for abstract class %s.',
          $params['class']
      ));
    }

    $source = $this->_getBaseDir() . $path;
    $target = $this->_getBaseDir('test', array('unit')) . $path;

    if( $params['verbose'] )
    {
      $this->logSection('info', sprintf(
        'Source class file is %s.',
          $source
      ));

      $this->logSection('info', sprintf(
        'Target test case file is %s.',
          $target
      ));
    }

    /* Verify the test file doesn't already exist. */
    if( file_exists($target) )
    {
      throw new RuntimeException(sprintf(
        'Test case file already exists at sf_test_dir/unit/%s.',
          $path
      ));
    }

    /* Locate the skeleton test case. */
    $skeleton = $this->_findSkeletonFile('unit.php');

    if( $params['verbose'] )
    {
      $this->logSection('info', sprintf(
        'Using skeleton test case at %s.',
          $skeleton
      ));
    }

    /* Validate custom tokens. */
    $customTokens = $this->_parseCustomTokens((array) $params['token']);

    /* Time to start doing things. */
    $this->_copySkeletonFile($skeleton, $target);

    /* Replace tokens. */
    $tokens = array(
      'CLASSNAME'   => $ref->getName(),
      'PACKAGE'     => $this->_guessPackageName($ref),
      'SUBPACKAGE'  => $this->_guessSubpackageName($ref, 'test'),
      'AUTHOR'      => $this->_guessAuthorNames($ref, false),

      'TESTS'       => ($params['no-tests'] ? '' : $this->_generateTests($ref))
    );

    if( ! empty($customTokens) )
    {
      $tokens = array_merge($tokens, $customTokens);
    }

    $this->getFilesystem()->replaceTokens($target, '##', '##', $tokens);
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
      $name = $meth->getName();

      /* Skip magic methods and anything marked as non-api. */
      if( $name[0] == '_' )
      {
        continue;
      }

      $str .= sprintf($template, ucfirst($name));
    }

    return $str;
  }
}