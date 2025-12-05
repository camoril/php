# Changelog

All notable changes to this project will be documented in this file.

## [1.0.0] - 2025-12-04

### Added
- Modernized UI using Tailwind CSS via CDN.
- Responsive design for mobile and desktop.
- Strict type declarations in PHP logic.
- `CHANGELOG.md` file.

### Changed
- Refactored `index.php` to include both logic and view, removing the need for a custom template engine.
- Replaced table-based layout with CSS Grid and Flexbox.
- Improved input validation and error handling.
- Updated amortization calculation logic for better readability and precision handling.

### Removed
- `loan-calculator.tpl` (Legacy template file).
- Custom template parsing functions (`load_template`, `replace_vars`, `glb`, `strip`).
- Usage of `extract()` and `GLOBALS` for variable management.
