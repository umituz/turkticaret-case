<?php

namespace App\Http\Resources\Base;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class BaseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function toResponse($request)
    {
        return parent::toResponse($request)->setData([
            'data' => $this->toArray($request),
        ]);
    }
}