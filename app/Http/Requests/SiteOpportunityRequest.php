<?php

namespace App\Http\Requests;

class SiteOpportunityRequest extends ArticleRequest
{
    protected function extraRules(): array
    {
        // Тег есть и у публикаций — он в базовых правилах. Здесь — только срок.
        return [
            'deadline_at' => ['nullable', 'date'],
            'show_on_site' => ['nullable', 'boolean'],
            'show_in_cabinet' => ['nullable', 'boolean'],
        ];
    }
}
