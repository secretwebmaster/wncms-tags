# Changelog
All notable changes to this project will be documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [v1.7.0] - 2025-11-13
### Changed
- Replace `order_column` with `sort` field across Tag model
- Update nullable parameter types for full PHP 8.2 compatibility
- Fix TagsServiceProvider migration publish path
- Remove outdated migrations and adopt new `database/migrations` structure
- Improve consistency with WNCMS `sort` / `direction` standard

---

## [v1.6.2] - 2025-09-16
### Changed
- Add Laravel 12 support
- Update dev dependencies

---

## [v1.6.1] - 2025-07-20
### Fixed
- Hotfix: missing `getModelClass()`

---

## [v1.6.0] - 2025-07-20
### Added
- Allow Tag model to be overridden by child class extending it

---

## [v1.5.0] - 2025-01-04
### Improved
- Tag model extendibility enhancements

---

## [v1.4.1] - 2024-11-15
### Changed
- Update keyword handling logic

---

## [v1.4.0] - 2024-10-03
### Added
- Search by slug when creating tag from string

---

## [v1.3.0] - 2024-09-22
### Added
- Self-related parent-child relationship support in Tag model

---

## [v1.2.0] - 2024-09-13
### Added
- Experimental Tagify compatibility feature

---

## [v1.1.0] - 2024-09-11
### Changed
- Update README documentation

---

## [v1.0.1] - 2024-09-09
### Changed
- Update composer.json metadata

---

## [v1.0.0] - 2024-09-09
### Added
- Initial stable release of wncms-tags

---

## [Initial Development]
- Initial commit setup  
- Namespace update  
- Added PHPUnit test  
- Migration rename and cleanup
