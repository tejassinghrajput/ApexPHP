<?php

namespace App\Core;

/**
 * Represents a response that should be sent as JSON.
 *
 * This class handles setting the correct HTTP headers and encoding the data
 * into a JSON string.
 */
class JsonResponse
{
    /**
     * @var mixed The data to be sent in the response.
     */
    protected mixed $data;

    /**
     * @var int The HTTP status code for the response.
     */
    protected int $statusCode;

    /**
     * @var array Additional HTTP headers for the response.
     */
    protected array $headers;

    /**
     * JsonResponse constructor.
     *
     * @param mixed $data The data to be encoded.
     * @param int $statusCode The HTTP status code.
     * @param array $headers Additional headers.
     */
    public function __construct(mixed $data = null, int $statusCode = 200, array $headers = [])
    {
        $this->data = $data;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    /**
     * Sends the response to the client.
     *
     * This method sets the HTTP status code, sends all headers,
     * echoes the JSON-encoded data, and then terminates the script.
     *
     * @return void
     */
    public function send(): void
    {
        // Set the HTTP status code.
        http_response_code($this->statusCode);

        // Set headers, ensuring Content-Type is always application/json.
        $this->headers['Content-Type'] = 'application/json; charset=utf-8';
        foreach ($this->headers as $key => $value) {
            header("{$key}: {$value}");
        }

        // Output the JSON encoded data.
        // The JSON_PRETTY_PRINT option is nice for development.
        if (env('APP_DEBUG', false)) {
            echo json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        } else {
            echo json_encode($this->data);
        }

        // Terminate script execution to prevent any further output.
        exit;
    }
}
