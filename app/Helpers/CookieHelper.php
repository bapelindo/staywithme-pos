<?php
namespace App\Helpers;

class CookieHelper {

    /**
     * Set a cookie.
     *
     * @param string $name The name of the cookie.
     * @param string $value The value of the cookie.
     * @param int $expiry The expiration time of the cookie (Unix timestamp). Default 30 days.
     * @param string $path The path on the server in which the cookie will be available on. Default '/'.
     * @param string $domain The domain that the cookie is available to. Default empty (current domain).
     * @param bool $secure Indicates that the cookie should only be transmitted over a secure HTTPS connection. Default true.
     * @param bool $httponly When true, the cookie will be made accessible only through the HTTP protocol. Default true.
     * @param string $samesite Controls when cookies are sent with cross-site requests. 'Lax', 'Strict', or 'None'. Default 'Lax'.
     */
    public static function setCookie(
        string $name,
        string $value,
        int $expiry = 2592000, // 30 days
        string $path = '/',
        string $domain = '',
        bool $secure = true,
        bool $httponly = true,
        string $samesite = 'Lax'
    ): bool {
        $options = [
            'expires' => time() + $expiry,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httponly,
            'samesite' => $samesite,
        ];
        return setcookie($name, $value, $options);
    }

    /**
     * Get the value of a cookie.
     *
     * @param string $name The name of the cookie.
     * @return string|null The value of the cookie, or null if not set.
     */
    public static function getCookie(string $name): ?string {
        return $_COOKIE[$name] ?? null;
    }

    /**
     * Delete a cookie.
     *
     * @param string $name The name of the cookie.
     * @param string $path The path on the server in which the cookie will be available on. Default '/'.
     * @param string $domain The domain that the cookie is available to. Default empty (current domain).
     * @param bool $secure Indicates that the cookie should only be transmitted over a secure HTTPS connection. Default true.
     * @param bool $httponly When true, the cookie will be made accessible only through the HTTP protocol. Default true.
     * @param string $samesite Controls when cookies are sent with cross-site requests. 'Lax', 'Strict', or 'None'. Default 'Lax'.
     */
    public static function deleteCookie(
        string $name,
        string $path = '/',
        string $domain = '',
        bool $secure = true,
        bool $httponly = true,
        string $samesite = 'Lax'
    ): bool {
        $options = [
            'expires' => time() - 3600, // Set expiry to an hour ago
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httponly,
            'samesite' => $samesite,
        ];
        return setcookie($name, '', $options);
    }
}
