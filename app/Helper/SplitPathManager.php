<?php

// ============================
// FILE: app/Helper/SplitPathManager.php
// ============================

namespace App\Helper;

use Illuminate\Support\Facades\Log;

class SplitPathManager
{
    /**
     * Get actual split paths using glob pattern
     */
    public static function getActualSplitPaths($baseFilename, $splitDir)
    {
        // Sesuaikan dengan naming convention yang digunakan
        $normalizedFilename = $baseFilename . '_normalized';
        $splitFolder = $splitDir . '/' . $normalizedFilename;
        
        $pattern = $splitFolder . '/' . $normalizedFilename . '_page_*.pdf';
        $paths = glob($pattern);
        
        Log::debug("SplitPathManager::getActualSplitPaths", [
            'base_filename' => $baseFilename,
            'normalized_filename' => $normalizedFilename,
            'split_folder' => $splitFolder,
            'pattern' => $pattern,
            'found_count' => count($paths ?: [])
        ]);
        
        if ($paths) {
            sort($paths);
            return PathNormalizer::normalizeArray($paths);
        }
        
        return [];
    }

    /**
     * Verify split paths consistency between session and actual files
     */
    public static function verifySplitPathsConsistency(array $sessionPaths, array $actualPaths)
    {
        $sessionNormalized = PathNormalizer::normalizeArray($sessionPaths);
        $actualNormalized = PathNormalizer::normalizeArray($actualPaths);

        $sessionCount = count($sessionNormalized);
        $actualCount = count($actualNormalized);

        $missing = array_diff($sessionNormalized, $actualNormalized);
        $extra = array_diff($actualNormalized, $sessionNormalized);

        return [
            'is_consistent' => $sessionCount === $actualCount && empty($missing) && empty($extra),
            'session_count' => $sessionCount,
            'actual_count' => $actualCount,
            'missing_from_actual' => array_values($missing),
            'extra_in_actual' => array_values($extra),
            'count_match' => $sessionCount === $actualCount,
            'paths_match' => empty($missing) && empty($extra)
        ];
    }

    /**
     * Get split paths info for all files
     */
    public static function getAllFilesSpiltInfo(array $normalizedResults, $splitDir)
    {
        $allSplitInfo = [];
        $flatPaths = [];

        foreach ($normalizedResults as $index => $result) {
            // Extract original filename dari normalized path
            $normalizedPath = $result['normalized_path'];
            $baseFilename = pathinfo($normalizedPath, PATHINFO_FILENAME);
            
            // Remove _normalized suffix untuk mendapatkan original filename
            $originalBaseFilename = str_replace('_normalized', '', $baseFilename);
            
            Log::debug("SplitPathManager processing file {$index}", [
                'original_name' => $result['original_name'],
                'normalized_path' => $normalizedPath,
                'base_filename' => $baseFilename,
                'original_base_filename' => $originalBaseFilename
            ]);
            
            $actualPaths = self::getActualSplitPaths($originalBaseFilename, $splitDir);
            $validation = PathNormalizer::validateArrayDetailed($actualPaths);
            
            $allSplitInfo[$index] = [
                'original_name' => $result['original_name'],
                'base_filename' => $originalBaseFilename,
                'normalized_filename' => $baseFilename,
                'split_paths' => $actualPaths,
                'page_count' => count($actualPaths),
                'validation' => $validation
            ];

            $flatPaths = array_merge($flatPaths, $actualPaths);
        }

        Log::info("SplitPathManager::getAllFilesSpiltInfo completed", [
            'total_files' => count($allSplitInfo),
            'total_flat_paths' => count($flatPaths)
        ]);

        return [
            'by_file' => $allSplitInfo,
            'flat_paths' => $flatPaths,
            'total_pages' => count($flatPaths)
        ];
    }
}
