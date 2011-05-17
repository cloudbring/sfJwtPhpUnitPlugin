To install the plugin into a Symfony project:

1. Install the plugin files into `plugins/`.
2. Install PHPUnit 3.5 if necessary.  Make sure it is accessible from PHP's
    include_path.
3. Add a `test` entry to `config/databases.yml` or disable `use_database` in
  `apps/*/config/settings.yml`.
4. Add an `upload_dir` entry for the `test` environment in `settings.yml` for
  each application in your project.
5. Set `error_reporting` for the `test` environment to `(E_ALL | E_STRICT)` in
  `settings.yml` for each application in your project.
6. Add the following code to `ProjectConfiguration::setup()` in
  `config/ProjectConfiguration.class.php`:

<pre>
if( PHP_SAPI == 'cli' )
{
  $this->enablePlugins('sfJwtPhpUnitPlugin');
}
</pre>

Note:  Because this plugin only implements Symfony tasks and should have no
  effect upon the normal operation of your project, it only needs to be loaded
  when in CLI mode.

Now you're ready to start writing tests!