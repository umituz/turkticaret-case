<?php

namespace App\Http\Resources\Base;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BaseCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Check if this is a paginator instance
        if (method_exists($this->resource, 'total')) {
            return [
                'data' => $this->collection,
                'links' => [
                    'first' => $this->url(1),
                    'last' => $this->url($this->lastPage()),
                    'prev' => $this->previousPageUrl(),
                    'next' => $this->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $this->currentPage(),
                    'from' => $this->firstItem(),
                    'last_page' => $this->lastPage(),
                    'path' => $this->path(),
                    'per_page' => $this->perPage(),
                    'to' => $this->lastItem(),
                    'total' => $this->total(),
                    'count' => $this->collection->count(),
                ],
            ];
        }

        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->collection->count(),
                'count' => $this->collection->count(),
            ],
        ];
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function toResponse($request): JsonResponse
    {
        return new JsonResponse($this->toArray($request));
    }
}
