# sfJwtPhpUnitPlugin #
## About ##
sfJwtPhpUnitPlugin ("JPUP") was developed at [JWT](http://jwt.com) to assist in
  the development of several Symfony 1.4 projects.

JPUP boasts robust database handling (modeled loosely after
  [Django's test framework](http://docs.djangoproject.com/en/dev/topics/testing/#s-the-test-database)
  and [sfPhpUnitPlugin](https://www.hostedredmine.com/projects/sfphpunitplugin/wiki/1013#Fixtures)),
  unlimited extensibility over Symfony's sfBrowser class and user-friendly tasks
  for running collections of tests.

We found this plugin to be exceptionally useful for testing database-driven
  Symfony applications, and we wanted to share it with the Symfony community.

## ANOTHER PHPUnit Plugin for Symfony? ##
Before embarking upon development for JPUP, we took a look around, and while we
  did discover a number of existing solutions that worked fantastically, we
  found that none of them quite met our needs.

The most critical problems we set out to solve with JPUP are:

- Isolation from production data and files in a project.
- Easy (but powerful!) data manipulation and fixture integration.
- A port of `sfBrowser` that has `sfTestFunctional`'s API but doesn't use Lime.
- Using Symfony tasks to run multiple tests in one go.

## Compatibility ##
JPUP was developed specifically for projects using Symfony 1.4/Doctrine/MySQL.

Database fixtures are designed exclusively for Doctrine, and there's a teeny
  bit of MySQL-specific querying done to speed up the database reset process
  between tests.

One of the goals we hope to achieve by releasing this plugin to the community is
  to ascertain whether there is a need to support additional
  ORMs/databases/features.  Your feedback, suggestions and pull requests are
  always welcome!

## Installation ##
See INSTALL.md.

## Usage ##
See USAGE.md.

## Contributing ##
We welcome any and all suggestions, requests, (constructive) criticism, code,
  fixes, forks, success stories... in short, if you think it would improve the
  quality of JPUP (or at least make us feel good about it), we'd love to see it.