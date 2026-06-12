<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //return parent::toArray($request);
        return [
            'id' => $this->id,
            'subject' => $this->title,
            'details' => $this->description,
            'status' => $this->status,
            'created_at_human' => $this->created_at->diffForHumans(),
            'short_desc' => $this->when( $this->description, fn() => str($this->description)->limit(20) ),
        ];
    }
}
