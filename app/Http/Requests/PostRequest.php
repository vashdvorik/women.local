<?php

namespace App\Http\Requests;

class PostRequest extends ArticleRequest
{
    protected function extraRules(): array
    {
        return [
            'show_on_site' => ['nullable', 'boolean'],
            'show_in_cabinet' => ['nullable', 'boolean'],
            'show_in_learning' => ['nullable', 'boolean'],
        ];
    }
}
