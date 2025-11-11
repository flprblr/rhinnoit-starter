<?php

namespace App\Support\Inertia;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Pagination\LengthAwarePaginator;

class PaginatorResource implements Arrayable
{
    public static function make(LengthAwarePaginator $paginator): self
    {
        return new self($paginator);
    }

    public function __construct(private LengthAwarePaginator $paginator) {}

    public function toArray(): array
    {
        $collection = $this->paginator->getCollection()->values();

        $meta = [
            'current_page' => $this->paginator->currentPage(),
            'from' => $this->paginator->firstItem(),
            'last_page' => $this->paginator->lastPage(),
            'per_page' => $this->paginator->perPage(),
            'to' => $this->paginator->lastItem(),
            'total' => $this->paginator->total(),
        ];

        $links = collect($this->paginator->toArray()['links'] ?? [])
            ->map(fn ($link) => [
                'url' => $link['url'],
                'label' => $link['label'],
                'active' => $link['active'],
                'page' => $this->extractPage($link['url']),
            ])
            ->all();

        return [
            'data' => $collection->all(),
            'meta' => $meta,
            'links' => $links,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    private function extractPage(?string $url): ?int
    {
        if (! $url) {
            return null;
        }

        $parts = parse_url($url);

        if (! isset($parts['query'])) {
            return null;
        }

        parse_str($parts['query'], $query);

        return isset($query['page']) ? (int) $query['page'] : null;
    }
}
