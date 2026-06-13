<?php

namespace App\Services\Api;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

class ApiJsonResponse
{
    /** @param array<string, mixed> $meta */
    public function success(mixed $data = [], array $meta = [], int $status = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'meta' => $meta,
            'error' => null,
        ], $status);
    }

    /** @param array<string, mixed> $meta */
    public function error(string $code, string $message, int $status, array $meta = []): JsonResponse
    {
        return response()->json([
            'data' => null,
            'meta' => $meta,
            'error' => ['code' => $code, 'message' => $message],
        ], $status);
    }

    /** @return array<string, mixed> */
    public function pagination(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ];
    }
}
