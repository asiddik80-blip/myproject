<?php 

// ============================
// FILE: app/Helper/JSXPathValidator.php
// ============================

namespace App\Helper;

use Illuminate\Support\Facades\Log;

class JSXPathValidator
{
    /**
     * Validate paths before sending to JSX
     */
    public static function validateForJSX(array $fileList, array $debugPaths = [])
    {
        $validation = [
            'files' => [],
            'summary' => [
                'total_files' => count($fileList),
                'valid_files' => 0,
                'invalid_files' => 0,
                'total_input_paths' => 0,
                'valid_input_paths' => 0,
                'invalid_input_paths' => 0
            ],
            'debug_consistency' => [
                'debug_paths_count' => count($debugPaths),
                'input_paths_count' => 0,
                'is_consistent' => false
            ]
        ];

        $allInputPaths = [];

        foreach ($fileList as $index => $fileData) {
            $inputPaths = $fileData['input'] ?? [];
            $outputPath = $fileData['output'] ?? '';

            $pathValidation = PathNormalizer::validateArrayDetailed($inputPaths);
            
            // Ensure output directory exists
            $outputDir = dirname($outputPath);
            if (!is_dir($outputDir)) {
                try {
                    mkdir($outputDir, 0755, true);
                } catch (\Exception $e) {
                    Log::warning("Could not create output directory: {$outputDir}. Error: " . $e->getMessage());
                }
            }
            
            $outputValidation = [
                'path' => PathNormalizer::normalize($outputPath),
                'dir_exists' => is_dir($outputDir),
                'dir_writable' => is_dir($outputDir) ? is_writable($outputDir) : false
            ];

            $validation['files'][$index] = [
                'input_validation' => $pathValidation,
                'output_validation' => $outputValidation,
                'is_valid' => $pathValidation['summary']['invalid'] === 0 && $outputValidation['dir_exists']
            ];

            // Update summary
            $validation['summary']['total_input_paths'] += $pathValidation['summary']['total'];
            $validation['summary']['valid_input_paths'] += $pathValidation['summary']['valid'];
            $validation['summary']['invalid_input_paths'] += $pathValidation['summary']['invalid'];

            if ($validation['files'][$index]['is_valid']) {
                $validation['summary']['valid_files']++;
            } else {
                $validation['summary']['invalid_files']++;
            }

            $allInputPaths = array_merge($allInputPaths, $inputPaths);
        }

        // Debug consistency check
        $validation['debug_consistency']['input_paths_count'] = count($allInputPaths);
        $validation['debug_consistency']['is_consistent'] = 
            count($allInputPaths) === count($debugPaths);

        return $validation;
    }

    /**
     * Generate comprehensive log for JSX path validation
     */
    public static function logValidationResults($validation, $logFile = null)
    {
        $messages = [
            "=== JSX PATH VALIDATION RESULTS ===",
            "Total files: " . $validation['summary']['total_files'],
            "Valid files: " . $validation['summary']['valid_files'],
            "Invalid files: " . $validation['summary']['invalid_files'],
            "Total input paths: " . $validation['summary']['total_input_paths'],
            "Valid input paths: " . $validation['summary']['valid_input_paths'],
            "Invalid input paths: " . $validation['summary']['invalid_input_paths'],
            "Debug paths count: " . $validation['debug_consistency']['debug_paths_count'],
            "Input-debug consistency: " . ($validation['debug_consistency']['is_consistent'] ? 'YES' : 'NO'),
        ];

        if ($validation['summary']['invalid_files'] > 0) {
            $messages[] = "FILES WITH ISSUES:";
            foreach ($validation['files'] as $index => $fileValidation) {
                if (!$fileValidation['is_valid']) {
                    $inputIssues = $fileValidation['input_validation']['summary']['invalid'] ?? 0;
                    $outputIssues = !($fileValidation['output_validation']['dir_exists'] ?? false) ? 1 : 0;
                    $messages[] = "  File {$index}: Input issues: {$inputIssues}, Output issues: {$outputIssues}";
                    
                    // Detail missing files
                    if ($inputIssues > 0) {
                        $results = $fileValidation['input_validation']['results'] ?? [];
                        foreach ($results as $pathIndex => $result) {
                            if (!($result['exists'] ?? false)) {
                                $messages[] = "    - Missing: " . ($result['path'] ?? 'unknown');
                            }
                        }
                    }
                }
            }
        }

        $messages[] = "=== END VALIDATION RESULTS ===";

        foreach ($messages as $message) {
            Log::info($message);
            if ($logFile && is_resource($logFile)) {
                fwrite($logFile, $message . PHP_EOL);
            }
        }

        return $validation['summary']['invalid_files'] === 0;
    }
}
