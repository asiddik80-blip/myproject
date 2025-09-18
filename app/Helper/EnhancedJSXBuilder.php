<?php 


// ============================
// FILE: app/Helper/EnhancedJSXBuilder.php
// ============================

namespace App\Helper;

use Illuminate\Support\Facades\Log;

class EnhancedJSXBuilder
{
    /**
     * Build JSX file dengan file list yang diberikan
     */
    public static function build(array $fileList, $templatePath, $outputDir)
    {
        // Pastikan output directory exists
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        // Read template
        if (!file_exists($templatePath)) {
            throw new \Exception("JSX template not found: {$templatePath}");
        }

        $template = file_get_contents($templatePath);
        
        // Replace placeholders
        $filesJson = json_encode($fileList, JSON_UNESCAPED_SLASHES);
        
        // Extract debug paths dari semua file input
        $debugPaths = [];
        foreach ($fileList as $file) {
            $debugPaths = array_merge($debugPaths, $file['input'] ?? []);
        }
        $debugPathsJson = json_encode($debugPaths, JSON_UNESCAPED_SLASHES);
        
        // Replace template placeholders
        $jsxContent = str_replace('{{FILES_ARRAY}}', $filesJson, $template);
        $jsxContent = str_replace('{{ALL_SPLIT_PDF_PATHS}}', $debugPathsJson, $jsxContent);
        
        // Generate output filename
        $outputFile = $outputDir . '/generated_script_' . time() . '.jsx';
        
        // Write file
        file_put_contents($outputFile, $jsxContent);
        
        Log::info("JSX file generated: {$outputFile}");
        
        return $outputFile;
    }

    /**
     * Build JSX with comprehensive path validation
     */
    public static function buildWithValidation(array $fileList, $templatePath, $outputDir, array $debugPaths = [])
    {
        Log::info("EnhancedJSXBuilder: Starting JSX generation with validation");
        
        // Step 1: Validate all paths
        $validation = JSXPathValidator::validateForJSX($fileList, $debugPaths);
        
        // Step 2: Log validation results
        $validationPassed = JSXPathValidator::logValidationResults($validation);
        
        if (!$validationPassed) {
            Log::error("JSX Path validation failed. Check logs for details.");
            Log::error("Validation summary: " . json_encode($validation['summary']));
            
            // Don't throw exception, just log warning and proceed
            Log::warning("Proceeding with JSX generation despite validation warnings...");
        }

        // Step 3: Build JSX 
        $jsxFile = self::build($fileList, $templatePath, $outputDir);
        
        // Step 4: Log final JSX info
        Log::info("JSX generated successfully: {$jsxFile}");
        Log::info("Total files in JSX: " . count($fileList));
        
        $totalPages = 0;
        foreach ($fileList as $file) {
            $totalPages += $file['totalPages'] ?? count($file['input'] ?? []);
        }
        
        Log::info("Total pages across all files: " . $totalPages);

        return $jsxFile;
    }
}