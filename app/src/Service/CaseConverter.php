<?php

namespace App\Service;

/**
 * Class CaseConverter
 */
class CaseConverter
{
    /**
     * Check if a string is in camelCase
     *
     * @param string $string
     * @return bool
     */
    public static function isCamelCase(string $string): bool
    {
        $matches = [];
        preg_match_all('/[A-Z]/', $string, $matches);

        foreach ($matches[0] as $match) {
            if (strpos($string, $match)!== 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a string is in PascalCase
     *
     * @param string $string
     * @return bool
     */
    public static function isPascalCase(string $string): bool
    {
        $matches = [];
        preg_match_all('/[A-Z]/', $string, $matches);

        foreach ($matches[0] as $match) {
            if (strpos($string, $match) === 0) {
                continue;
            }

            if (strpos($string, $match)!== false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a string is in snake_case
     *
     * @param string $string
     * @return bool
     */
    public static function isSnakeCase(string $string): bool
    {
        return preg_match('/^[a-z_]+$/', $string) === 1;
    }

    /**
     * Convert snake_case to camelCase
     *
     * @param string $string
     * @return string
     */
    public static function snakeToCamel(string $string): string
    {
        $parts = explode('_', $string);
        $result = array_shift($parts);

        foreach ($parts as $part) {
            $result.= ucfirst($part);
        }

        return $result;
    }

    /**
     * Convert snake_case to PascalCase
     *
     * @param string $string
     * @return string
     */
    public static function snakeToPascal(string $string): string
    {
        $parts = explode('_', $string);

        return implode('', array_map('ucfirst', $parts));
    }

    /**
     * Convert PascalCase to camelCase
     *
     * @param string $string
     * @return string
     */
    public static function pascalToCamel(string $string): string
    {
        $string[0] = strtolower($string[0]);

        return $string;
    }

    /**
     * Convert PascalCase to snake_case
     *
     * @param string $string
     * @return string
     */
    public static function pascalToSnake(string $string): string
    {
        return ltrim(strtolower(preg_replace('/[A-Z]/', '_\0', $string)), '_');
    }

    /**
     * Convert camelCase to PascalCase
     *
     * @param string $string
     * @return string
     */
    public static function camelToPascal(string $string): string
    {
        $string[0] = strtoupper($string[0]);

        return $string;
    }

    /**
     * Convert camelCase to snake_case
     *
     * @param string $string
     * @return string
     */
    public static function camelToSnake(string $string): string
    {
        return ltrim(strtolower(preg_replace('/[A-Z]/', '_\0', $string)), '_');
    }

    /**
     * Convert a string to PascalCase
     *
     * @param string $string
     * @return string
     */
    public static function toPascalCase(string $string): string
    {
        $parts = explode(' ', $string);

        return implode('', array_map('ucfirst', $parts));
    }

    /**
     * Convert a string to camelCase
     *
     * @param string $string
     * @return string
     */
    public static function toCamelCase(string $string): string
    {
        $parts = explode(' ', $string);
        $result = array_shift($parts);

        foreach ($parts as $part) {
            $result.= ucfirst($part);
        }

        return $result;
    }

    /**
     * Convert a string to snake_case
     *
     * @param string $string
     * @return string
     */
    public static function toSnakeCase(string $string): string
    {
        $parts = explode(' ', $string);

        return implode('_', array_map('strtolower', $parts));
    }
}
