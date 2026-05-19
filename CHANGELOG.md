# Changelog

All notable changes to `:package_name` will be documented in this file.

## Unreleased

### Added

- `class` column option now accepts a `Closure(Model): ?string` in addition to a plain string. The closure is resolved against each row and applied to the cell only (the header skips row-dependent callables). Enables per-row styling such as greying out inactive rows. Backward compatible with the existing string usage.
