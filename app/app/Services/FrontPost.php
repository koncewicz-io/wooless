<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;

class FrontPost
{
    public function __construct()
    {}

    public function postsCategoriesResponse(ResponseInterface $body, $activeCategoryIds): Collection
    {
        $data = json_decode($body->getBody()->getContents(), true);

        return collect($data)
            ->select(['id', 'name'])
            ->map(function ($item) use ($activeCategoryIds) {
                $item['active'] = in_array($item['id'], $activeCategoryIds);
                return $item;
            });
    }

    public function postsResponse(ResponseInterface $body): Collection
    {
        $data = json_decode($body->getBody()->getContents(), true);

        return collect($data)
            ->map(function ($post) {
                return [
                    'id' => $post['id'],
                    'title' => $post['title']['rendered'],
                    'shortDescription' => $post['excerpt']['rendered'],
                    'imageSrc' => isset($post['_embedded']['wp:featuredmedia']) && isset($post['_embedded']['wp:featuredmedia'][0])
                        ? $post['_embedded']['wp:featuredmedia'][0]['source_url']
                        : null
                ];
            });
    }
}
