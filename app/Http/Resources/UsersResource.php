<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class UsersResource extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $users = [];

        foreach($this->collection as $user) {
            array_push($users, [
                'id' => $user->id,
                'name' => $user->name,
                'type' => $user->type
            ]);
        }

        return $users;
    }
}
