<?php

namespace App\Http\Resources\User\Address;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'type' => $this->type,
            'is_default' => $this->is_default,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'company' => $this->company,
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'country_uuid' => $this->country_uuid,
            'country' => $this->whenLoaded('country', function () {
                return [
                    'uuid' => $this->country->uuid,
                    'name' => $this->country->name,
                    'iso_code' => $this->country->iso_code,
                ];
            }),
            'phone' => $this->phone,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}