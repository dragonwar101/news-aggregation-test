<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'url' => $this->url,
            'image_url' => $this->image_url,
            'slug' => $this->slug,
            'author' => $this->when($this->isSingle(), $this->author),
            'content' => $this->when($this->isSingle(), $this->content),
            'source' => $this->source,
            'original_source' => $this->original_source,
            'published_at' => $this->when($this->isSingle(), $this->published_at),
        ];
    }

    private function isSingle(): bool
    {
        return request()->route()->getActionMethod() === 'show';
    }
}
