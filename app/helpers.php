<?php 


// app/helpers.php

if (!function_exists('successResponse')) {
    /**
     * Return a success JSON response.
     *
     * @param string $message
     * @param mixed $data
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    function successResponse($message = '', $data = null, $status = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }
}

if (!function_exists('errorResponse')) {
    /**
     * Return an error JSON response.
     *
     * @param string $message
     * @param mixed $errors
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    function errorResponse($message = '', $errors = null, $status = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}
