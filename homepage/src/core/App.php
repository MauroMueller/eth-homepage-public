<?php
/**
 * Handles app state.
 * 
 * Should mostly be read after setting everything in bootstrap.php.
 */
class App {
    private static $services = [];

    public static function set($key, $value) {
        self::$services[$key] = $value;
    }

    public static function get($key) {
        return self::$services[$key] ?? null;
    }

    public static function config() {
        return self::get('config');
    }

    public static function asset_registry() {
        return self::get('asset_registry');
    }

    public static function db() {
        return self::get('db');
    }

    public static function mailer() {
        return self::get('mailer');
    }

    public static function session_repository() {
        return self::get('session_repository');
    }

    public static function user_repository() {
        return self::get('user_repository');
    }

    public static function token_repository() {
        return self::get('token_repository');
    }

    public static function auth() {
        return self::get('auth');
    }

    public static function preferences() {
        return self::get('preferences');
    }

    public static function authorization() {
        return self::get('authorization');
    }

    public static function lang() {
        return self::get('lang');
    }

    public static function breadcrumbs() {
        return self::get('breadcrumbs');
    }

    public static function sidebar() {
        return self::get('sidebar');
    }

    public static function file_service() {
        return self::get('file_service');
    }
}
?>
