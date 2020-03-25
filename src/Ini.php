<?php

namespace Pendenga\File;

/**
 * Class Ini
 * @package Pendenga\File
 */
abstract class Ini
{
    /**
     * @var array
     */
    static $ini;

    /**
     * @var array
     */
    static $ini_sections;

    /**
     * @param string $ini_name
     * @throws FileNotFoundException
     */
    public static function load($ini_name = 'config.ini')
    {
        if (!isset(self::$ini[$ini_name])) {
            self::$ini[$ini_name] = parse_ini_file(self::findFile($ini_name));
        }
        if (!isset(self::$ini_sections[$ini_name])) {
            self::$ini_sections[$ini_name] = parse_ini_file(self::findFile($ini_name), true);
        }
    }

    /**
     * Make sure we find the ini file next to the vendor directory
     * @param string $ini_name
     * @return string
     * @throws FileNotFoundException
     */
    public static function findFile($ini_name = 'config.ini')
    {
        $prefix = '';
        // only try so many levels
        foreach (range(0, 20) as $i) {
            $vendor_path = __DIR__ . $prefix . '/vendor';
            if (is_dir($vendor_path)) {
                $ini_path = __DIR__ . $prefix . '/' . $ini_name;
                if (!file_exists($ini_path)) {
                    throw new FileNotFoundException('ini file not found');
                }

                return $ini_path;
            } else {
                $prefix .= '/..';
            }
        }
        throw new FileNotFoundException('vendor dir not found');
    }

    /**
     * @param string $key
     * @param string $ini_name
     * @return mixed
     * @throws FileNotFoundException
     */
    public static function get($key, $ini_name = 'config.ini')
    {
        self::load($ini_name);

        return self::$ini[$ini_name][$key];
    }

    /**
     * @param string $key
     * @param string $ini_name
     * @return mixed
     * @throws FileNotFoundException
     */
    public static function section($key, $ini_name = 'config.ini')
    {
        self::load($ini_name);

        return self::$ini_sections[$ini_name][$key];
    }
}
