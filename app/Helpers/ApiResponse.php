<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

/**
 * Centralized API response helper.
 *
 * Enforces the standard response format defined in frontend-api.md:
 * Success: { "success": true, "message": "...", "data": {} }
 * Error:   { "success": false, "message": "...", "errors": {} }
 */
class ApiResponse
{
    /**
     * @param  array<string, mixed>|object|null  $data
     */
    public static function success(mixed $data = null, string $message = 'Success', int $status = 200, ?array $meta = null): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if ($meta !== null) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $status);
    }

    /**
     * @param  array<string, mixed>|null  $errors
     */
    public static function error(string $message = 'Error', int $status = 400, ?array $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }
}
