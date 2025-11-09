<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UploadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'original_name'  => $this->original_name,
            'stored_path'    => $this->stored_path,
            'status'         => $this->status,
            'rows_processed' => $this->rows_processed,
            'error'          => $this->error,
            'created_at'     => $this->created_at,
            'created_human'  => $this->created_at?->diffForHumans(),
        ];
    }
}
