<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Reporting;

/**
 * TestSummary class for collecting test results and outputting to JSON
 */
class TestSummary
{
    /**
     * Results organized by test suite
     *
     * @var array
     */
    private array $resultsBySuite = [];

    /**
     * Totals for each engine
     *
     * @var array
     */
    private array $totals = [];

    /**
     * Constructor - initializes from existing file if available
     *
     * @param string|null $filename The path to the existing results file to load
     */
    public function __construct(?string $filename = null)
    {
        // Load existing results if filename is provided and file exists
        if ($filename !== null && file_exists($filename)) {
            $this->loadFromJson($filename);
        }
    }

    /**
     * Load test results from a JSON file
     *
     * @param string $filename The path to the file to load
     * @return void
     */
    public function loadFromJson(string $filename): void
    {
        if (!file_exists($filename)) {
            return;
        }

        $jsonData = file_get_contents($filename);
        if ($jsonData === false) {
            return;
        }

        $data = json_decode($jsonData, true);
        if (!is_array($data)) {
            return;
        }

        // Load test suite results
        if (isset($data['test_suites']) && is_array($data['test_suites'])) {
            $this->resultsBySuite = $data['test_suites'];

            // Normalize suite keys to use forward slashes
            $normalizedSuites = [];
            foreach ($this->resultsBySuite as $suite => $engines) {
                $normalizedSuite = str_replace('\\', '/', $suite);
                $normalizedSuites[$normalizedSuite] = $engines;
            }
            $this->resultsBySuite = $normalizedSuites;
        }

        // Load totals
        if (isset($data['totals']) && is_array($data['totals'])) {
            $this->totals = $data['totals'];
        }
    }

    /**
     * Add a test result for a suite and engine
     *
     * @param string $suiteName The name of the test suite
     * @param string $engine The name of the engine used
     * @param int $passed The number of passed tests
     * @param int $failed The number of failed tests
     * @param int $total The total number of tests
     * @return void
     */
    public function addResult(string $suiteName, string $engine, int $passed, int $failed, int $total): void
    {
        // Normalize suite name to use forward slashes
        $normalizedSuiteName = str_replace('\\', '/', $suiteName);

        // Initialize suite results if not exists
        if (!isset($this->resultsBySuite[$normalizedSuiteName])) {
            $this->resultsBySuite[$normalizedSuiteName] = [];
        }

        // Add suite result
        $stats = [
            'passed' => $passed,
            'failed' => $failed,
            'total' => $total,
            'success_rate' => $total > 0 ? ($passed / $total * 100) : 0,
        ];

        $this->resultsBySuite[$normalizedSuiteName][$engine] = $stats;

        // Update totals
        if (!isset($this->totals[$engine])) {
            $this->totals[$engine] = [
                'passed' => 0,
                'failed' => 0,
                'total' => 0,
                'success_rate' => 0,
            ];
        }

        // Recalculate engine totals from all suites
        $engineTotals = [
            'passed' => 0,
            'failed' => 0,
            'total' => 0,
        ];

        foreach ($this->resultsBySuite as $suite) {
            if (isset($suite[$engine])) {
                $engineTotals['passed'] += $suite[$engine]['passed'];
                $engineTotals['failed'] += $suite[$engine]['failed'];
                $engineTotals['total'] += $suite[$engine]['total'];
            }
        }

        $this->totals[$engine]['passed'] = $engineTotals['passed'];
        $this->totals[$engine]['failed'] = $engineTotals['failed'];
        $this->totals[$engine]['total'] = $engineTotals['total'];
        $this->totals[$engine]['success_rate'] = $engineTotals['total'] > 0
            ? ($engineTotals['passed'] / $engineTotals['total'] * 100)
            : 0;
    }

    /**
     * Save the test results to a JSON file
     *
     * @param string $filename The path to the file to save
     * @return void
     */
    public function saveJson(string $filename): void
    {
        // First try to load any existing results to merge
        $existingResults = new TestSummary($filename);

        // Merge the existing results with our new ones (new ones take precedence)
        if (!empty($existingResults->resultsBySuite)) {
            foreach ($existingResults->resultsBySuite as $suiteName => $suiteData) {
                // Normalize suite name
                $normalizedSuiteName = str_replace('\\', '/', $suiteName);

                if (!isset($this->resultsBySuite[$normalizedSuiteName])) {
                    $this->resultsBySuite[$normalizedSuiteName] = $suiteData;

                    // Also update totals for engines in this suite
                    foreach ($suiteData as $engine => $stats) {
                        if (!isset($this->totals[$engine])) {
                            $this->totals[$engine] = [
                                'passed' => 0,
                                'failed' => 0,
                                'total' => 0,
                                'success_rate' => 0,
                            ];
                        }

                        $this->totals[$engine]['passed'] += $stats['passed'];
                        $this->totals[$engine]['failed'] += $stats['failed'];
                        $this->totals[$engine]['total'] += $stats['total'];
                    }
                }
            }

            // Recalculate success rates for all engines
            foreach ($this->totals as $engine => $stats) {
                $this->totals[$engine]['success_rate'] = $stats['total'] > 0
                    ? ($stats['passed'] / $stats['total'] * 100)
                    : 0;
            }
        }

        // Sort the test suites alphabetically for better readability
        ksort($this->resultsBySuite);

        $result = [
            'test_suites' => $this->resultsBySuite,
            'totals' => $this->totals,
            'timestamp' => date('Y-m-d H:i:s'),
        ];

        $jsonData = json_encode($result, JSON_PRETTY_PRINT);
        file_put_contents($filename, $jsonData);
    }
}
