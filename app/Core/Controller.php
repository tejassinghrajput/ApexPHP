<?php

namespace App\Core;

/**
 * The Base Controller.
 *
 * All other controllers in the application should extend this class.
 * This class can be used to share logic, such as middleware handling,
 * validation, or authorization checks across all controllers.
 */
abstract class Controller
{
    /**
     * A helper method to easily return a JSON response from a controller.
     *
     * @param mixed $data The data to be encoded as JSON.
     * @param int $statusCode The HTTP status code for the response.
     * @param array $headers Additional headers for the response.
     * @return JsonResponse
     */
    protected function json(mixed $data, int $statusCode = 200, array $headers = []): JsonResponse
    {
        return new JsonResponse($data, $statusCode, $headers);
    }

    // Future methods for things like rendering views, redirection, etc., would go here.
}
