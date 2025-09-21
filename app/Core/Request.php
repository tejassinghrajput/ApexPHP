<?php

namespace App\Core;

/**
 * Represents an HTTP request.
 *
 * This class provides a simple, object-oriented way to access information
 * about the incoming HTTP request, such as the URI, method, and headers.
 */
class Request
{
    /**
     * Get the request URI path.
     *
     * @return string The URI path (e.g., 'users/1').
     */
    public function uri(): string
    {
        return trim(
            parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH),
            '/'
        );
    }

    /**
     * Get the request method.
     *
     * @return string The HTTP method (e.g., 'GET', 'POST').
     */
    public function method(): string
    {
        // For now, we only care about the main method.
        // A more advanced implementation would handle method spoofing for forms.
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Get all request headers.
     *
     * @return array
     */
    public function headers(): array
    {
        return getallheaders();
    }
}
