<?php

namespace Marble\Admin\Support;

class Totp
{
    private const BASE32_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generate a random base32 secret key.
     */
    public static function generateSecret(int $length = 16): string
    {
        $chars  = str_split(self::BASE32_CHARS);
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $chars[random_int(0, 31)];
        }
        return $secret;
    }

    /**
     * Verify a TOTP code against a secret, allowing ±1 time window.
     */
    public static function verify(string $secret, string $code, int $window = 1): bool
    {
        $code      = preg_replace('/\s+/', '', $code);
        $timestamp = time();

        for ($i = -$window; $i <= $window; $i++) {
            if (self::generate($secret, $timestamp + ($i * 30)) === $code) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate a 6-digit TOTP code for a given timestamp (defaults to now).
     */
    public static function generate(string $secret, ?int $timestamp = null): string
    {
        $timestamp = $timestamp ?? time();
        $counter   = (int) floor($timestamp / 30);
        $key       = self::base32Decode($secret);
        $msg       = pack('J', $counter);
        $hash      = hash_hmac('sha1', $msg, $key, true);
        $offset    = ord($hash[19]) & 0xf;
        $code      = (
            (ord($hash[$offset])     & 0x7f) << 24 |
            (ord($hash[$offset + 1]) & 0xff) << 16 |
            (ord($hash[$offset + 2]) & 0xff) << 8  |
            (ord($hash[$offset + 3]) & 0xff)
        ) % 1000000;

        return str_pad((string) $code, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Build the otpauth:// URI for QR code generation.
     */
    public static function otpauthUri(string $secret, string $email, string $issuer): string
    {
        return sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s&algorithm=SHA1&digits=6&period=30',
            rawurlencode($issuer),
            rawurlencode($email),
            $secret,
            rawurlencode($issuer)
        );
    }

    /**
     * Generate a set of one-time backup codes.
     */
    public static function generateBackupCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(3))) . '-' . strtoupper(bin2hex(random_bytes(3)));
        }
        return $codes;
    }

    /**
     * Decode a base32 string to raw bytes.
     */
    private static function base32Decode(string $input): string
    {
        $input  = strtoupper(preg_replace('/=+$/', '', $input));
        $chars  = self::BASE32_CHARS;
        $output = '';
        $buffer = 0;
        $bits   = 0;

        for ($i = 0; $i < strlen($input); $i++) {
            $pos = strpos($chars, $input[$i]);
            if ($pos === false) {
                continue;
            }
            $buffer = ($buffer << 5) | $pos;
            $bits  += 5;
            if ($bits >= 8) {
                $bits  -= 8;
                $output .= chr(($buffer >> $bits) & 0xff);
            }
        }

        return $output;
    }
}
