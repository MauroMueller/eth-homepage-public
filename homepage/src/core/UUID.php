<?php
/**
 * Helper for UUIDs.
 * 
 * Generates UUIDs (version 4/7) and converts between their raw byte
 * representation and the canonical string form.
 */
class UUID {
    public static function v4() {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return $data;
    }

    public static function v7() {
        $time = intval(microtime(true) * 1000);
        $time = substr(pack('J', $time), 2);
        $random = random_bytes(10);
        $random[0] = chr((ord($random[0]) & 0x0f) | 0x70);
        $random[2] = chr((ord($random[2]) & 0x3f) | 0x80);
        return $time . $random;
    }

    public static function to_string($uuid_bytes) {
        if (strlen($uuid_bytes) != 16)
            throw new InvalidArgumentException('UUID must be 16 bytes.');
        $hex = bin2hex($uuid_bytes);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split($hex, 4));
    }

    public static function to_bytes($uuid_string) {
        $hex = str_replace('-', '', $uuid_string);
        if (strlen($hex) != 32 || !ctype_xdigit($hex))
            throw new InvalidArgumentException('Invalid UUID');
        return hex2bin($hex);
    }
}
?>
