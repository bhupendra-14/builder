<?php

namespace App\Traits;

use Illuminate\Validation\ValidationException;

trait ApiResponseTrait
{
    public function successResponse($data = null, $message = null, $statusCode = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message ?? 'Operation completed successfully.',
            'data' => $data,
        ], $statusCode);
    }

    public function errorResponse(string|array $errors = null, int $statusCode = 422)
    {
        return response()->json([
            'success' => false,
            'errors' => is_array($errors) ? $errors : [$errors ?? 'An error occurred.'],
        ], $statusCode);
    }

    /**
     * Paginated response for list endpoints
     */
    public function paginatedResponse($data, string $message = null)
    {
        // Type check to ensure $data is a paginated object
        if (!method_exists($data, 'items') || !method_exists($data, 'currentPage')) {
            throw new \InvalidArgumentException('Data must be a paginated object');
        }
        
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data->items(),
            'pagination' => [
                'current_page' => $data->currentPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'last_page' => $data->lastPage(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
            ],
        ]);
    }

    public function errorExceptionResponse(array $errors, int $statusCode = 500)
    {
        $isProduction = app()->environment('production');

        return response()->json([
            'success' => false,
            'errors' => $isProduction ? ['Invalid Request, please check again.'] : $errors,
        ], $statusCode);
    }

    public function validationErrorResponse(ValidationException|\Illuminate\Contracts\Validation\Validator $validatorOrException)
    {
        if ($validatorOrException instanceof ValidationException) {
            $errors = $validatorOrException->errors();
        } elseif ($validatorOrException instanceof \Illuminate\Contracts\Validation\Validator) {
            $errors = $validatorOrException->errors();
        } else {
            return $this->errorResponse($validatorOrException, 422);
        }

        return response()->json([
            'success' => false,
            'errors' => $errors,
        ], 422);
    }
}
