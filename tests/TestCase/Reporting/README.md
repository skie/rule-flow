# JSON Output for JsonLogic Tests

This feature allows you to export test results from the JsonLogic test suite to a JSON file for analysis or comparison. Results are accumulated across multiple test runs.

## Usage

To enable JSON output for test results, use the provided shell script:

```bash
# Run with default settings
./tests/run_jsonlogic_tests.sh

# Set custom output file
./tests/run_jsonlogic_tests.sh --output /path/to/output.json

# Filter specific tests
./tests/run_jsonlogic_tests.sh --filter YourTestClass
```

Alternatively, you can set the environment variables manually:

```bash
# Enable JSON output
export JSON_OUTPUT=1

# Optional: Set the output file path (defaults to ROOT/tests/results.json)
export JSON_OUTPUT_PATH=/path/to/output.json

# Run the tests
vendor/bin/phpunit --filter JsonLogicSuitesTest
```

## How It Works

1. The test runner sets `JSON_OUTPUT=1` to enable JSON reporting
2. Each test result is tracked per test suite
3. Results are read from the existing output file (if it exists)
4. New results are merged with existing ones
5. The updated results are saved back to the JSON file with a new timestamp

This approach allows you to:
- Run tests in smaller batches while accumulating results
- Test different subsets of the test suite
- Update only specific test results
- Maintain historical data across test runs

## Output Format

The output JSON file has the following structure:

```json
{
  "test_suites": {
    "arithmetic/plus.json": {
      "cakedc": {
        "passed": 10,
        "total": 10,
        "success_rate": 100
      }
    },
    "comparison/equal.json": {
      "cakedc": {
        "passed": 15,
        "total": 20,
        "success_rate": 75
      }
    }
  },
  "totals": {
    "cakedc": {
      "passed": 25,
      "total": 30,
      "success_rate": 83.33
    }
  },
  "timestamp": "2023-12-08 15:30:45"
}
```

## Engine Support

Currently, the test suite supports the following engines:
- `cakedc`: The CakeDC Admin plugin's JsonLogicEvaluator

Additional engines can be added by extending the `JsonLogicSuitesTest` class and setting the `engine` property. This would allow comparing different JsonLogic implementations.

## Result Accumulation Behavior

- New test results for a suite will override existing results for that suite
- Test suites not included in the current run will be preserved
- Totals are automatically recalculated from all test suite results
- The timestamp is updated with each run

To reset all results, simply delete the output JSON file before running tests.

## Troubleshooting

If the output file is empty, check that:

1. The tests are running correctly and producing results
2. The `JSON_OUTPUT` environment variable is set to `1`
3. The directory for the output file exists and is writable
4. PHPUnit is configured to run the correct test classes

## Integration with Other Tools

The output JSON file can be used with other tools for:

1. Comparing different JsonLogic implementations
2. Generating performance reports
3. Tracking test suite coverage over time
4. Creating visual dashboards of test results
