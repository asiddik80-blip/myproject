<?php

// ============================
// FILE: app/Helper/PathNormalizer.php
// ============================

namespace App\Helper;

class PathNormalizer 
{
    /**
     * Normalize path separators to forward slash
     */
    public static function normalize($path)
    {
        if (empty($path)) {
            return '';
        }
        return str_replace('\\', '/', $path);
    }

    /**
     * Normalize array of paths
     */
    public static function normalizeArray(array $paths)
    {
        return array_map([self::class, 'normalize'], $paths);
    }

    /**
     * Validate if file exists and normalize path
     */
    public static function validateAndNormalize($path)
    {
        $normalized = self::normalize($path);
        return [
            'path' => $normalized,
            'exists' => file_exists($normalized),
            'readable' => $normalized && file_exists($normalized) ? is_readable($normalized) : false
        ];
    }

    /**
     * Validate array of paths and return detailed info
     */
    public static function validateArrayDetailed(array $paths)
    {
        $results = [];
        $summary = [
            'total' => count($paths),
            'valid' => 0,
            'invalid' => 0,
            'unreadable' => 0
        ];

        foreach ($paths as $index => $path) {
            $validation = self::validateAndNormalize($path);
            $results[$index] = $validation;

            if ($validation['exists']) {
                $summary['valid']++;
                if (!$validation['readable']) {
                    $summary['unreadable']++;
                }
            } else {
                $summary['invalid']++;
            }
        }

        return [
            'results' => $results,
            'summary' => $summary
        ];
    }
}
