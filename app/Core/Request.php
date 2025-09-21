<?php

namespace App\Core;

/**
 * Represents an HTTP request.
 */
class Request
{
    /**
     * @var array|null The decoded JSON payload from the request body.
     */
    private ?array $jsonPayload = null;

    /**
     * Get the request URI path.
     */
    public function uri(): string
    {
        return trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    }

    /**
     * Get the request method.
     */
    public function method(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Get the entire JSON request payload as an associative array.
     */
    public function json(): array
    {
        if ($this->jsonPayload === null) {
            if ($this->method() === 'POST' || $this->method() === 'PUT' || $this->method() === 'PATCH') {
                $input = file_get_contents('php://input');
                $this->jsonPayload = json_decode($input, true) ?? [];
            } else {
                $this->jsonPayload = [];
            }
        }
        return $this->jsonPayload;
    }

    /**
     * Get a specific value from the JSON request payload.
     *
     * @param string $key The key to retrieve.
     * @param mixed|null $default The default value if the key is not found.
     * @return mixed
     */
    public function input(string $key, $default = null)
    {
        return $this->json()[$key] ?? $default;
    }

    /**
     * Get all request headers.
     */
    public function headers(): array
    {
        return getallheaders();
    }
}
