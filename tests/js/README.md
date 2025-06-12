# RuleFlow JavaScript Tests

This directory contains QUnit tests for the RuleFlow plugin's JavaScript validation libraries.

## Test Files

- **`form-watcher.test.js`** - Tests for the core FormWatcher class
- **`form-watcher-auto.test.js`** - Tests for the FormWatcher Auto library with ValidationRuleLoader
- **`form-watcher-dynamic.test.js`** - Tests for the FormWatcher Dynamic library with pattern matching

## Running Tests

### Option 1: Open in Browser
1. Open `qunit-test-runner.html` directly in your web browser
2. All tests will run automatically and display results

### Option 2: Local Server (Recommended)
1. Start a local web server in the plugin root directory:
   ```bash
   # Using Python 3
   python -m http.server 8000

   # Using PHP
   php -S localhost:8000

   # Using Node.js (if you have http-server installed)
   npx http-server
   ```
2. Navigate to `http://localhost:8000/tests/js/qunit-test-runner.html`

### Option 3: Using npm script
```bash
npm test
```
This will display instructions for running the tests.

## Test Coverage

### FormWatcher Core Tests
- FormWatcher initialization
- Form data extraction (simple and collection fields)
- Field value retrieval
- Validator registration and execution
- Validation callbacks
- Validate all fields functionality
- Path parsing for complex field names
- Data structure creation
- Options configuration
- Destroy functionality

### FormWatcher Auto Tests
- ValidationRuleLoader functionality
- JSON Logic rule parsing from elements
- Validation attribute parsing
- Validator creation from rules
- Form initialization with auto-detection
- Field name conversion (internal ↔ HTML)
- Global API availability
- Configuration management
- Rule analysis functionality
- JSON Logic operations
- Integration testing with real validation
- Form submission prevention

### FormWatcher Dynamic Tests
- Dynamic validation manager
- FormWatcher enhancement
- Dynamic form initialization
- Global API for dynamic features
- Configuration management
- Field name conversion
- Dynamic field addition simulation
- Validation with existing collection fields
- Integration with base FormWatcher
- Error handling for invalid forms
- Destroy functionality
- Multiple form support
- Form submission prevention with dynamic fields
- Validation rule attributes parsing

## Dependencies

The tests use the following external libraries loaded via CDN:
- **QUnit 2.19.4** - Testing framework
- **JSON Logic JS 2.0.2** - JSON Logic rule evaluation

## Test Structure

Each test module follows this pattern:
1. **Setup** (`beforeEach`) - Creates test fixtures and form elements
2. **Test Cases** - Individual test functions with assertions
3. **Cleanup** (`afterEach`) - Destroys watchers and cleans up

## Debugging Tests

To debug failing tests:
1. Open browser developer tools
2. Check the console for error messages
3. Use the QUnit interface to rerun specific tests
4. Add `console.log()` statements to test files if needed

## Adding New Tests

When adding new functionality:
1. Add test cases to the appropriate test file
2. Follow the existing naming conventions
3. Include both positive and negative test cases
4. Test edge cases and error conditions
5. Update this README if new test categories are added
