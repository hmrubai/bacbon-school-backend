<?php

namespace App\Http\Resources\Guardian;

use Illuminate\Http\Resources\Json\JsonResource;

class ChildResource extends JsonResource
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
        "id" => $this->user_id,
        "relation" => $this->relation,
        "name" => $this->name,
        "mobile_number" => $this->mobile_number,
        "image" => $this->image ? 'https://api.bacbonschool.com/uploads/userImages/' . $this->image : 'https://api.bacbonschool.com/uploads/default/user.jpg',
        "user_code" => $this->user_code,
        "is_accepted_by_student" => $this->is_accepted_by_student
        ];
    }
}
