<?php

namespace App\Http\Resources\Guardian;

use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Resources\Guardian\ChildResource;

class DetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "user_code" => $this->user_code,
            "email" => $this->email,
            "fcm_id" => $this->fcm_id,
            "mobile_number" => $this->mobile_number,
            "image" => $this->image ? 'https://api.bacbonschool.com/uploads/guardianImages/' . $this->image : 'https://api.bacbonschool.com/uploads/default/user.jpg',
            "address" => $this->address,
            "children" => ChildResource::collection($this->children)
        ];
    }
}
