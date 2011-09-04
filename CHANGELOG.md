# 1.0.4

## Issues

- Resolved [#19]: Need a changelog.
- Resolved [#6]:  Remove MySQL dependency.
- [#3]:  Added --plugin option to runner tasks (generators still outstanding).

## Major Changes

- Added `Test_Browser->signin()`.
- Empty `--filter` value no longer breaks PHPUnit.
- Added error/status code to `assertStatusCode()` failure message.
- Added `sf_fixture_dir` `sfConfig` value (needs documentation).

## Minor Changes

- Added `__toString()` handler to `Test_ObjectWrapper`.
- Convert parameters to strings before sending them to `sfBrowser->call()`.
- Fixed typos in skeleton test case files.
- Minor documentation updates.

# 1.0.0

- Initial release.