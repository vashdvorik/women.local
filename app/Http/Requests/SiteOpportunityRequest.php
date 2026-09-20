<?php

namespace App\Http\Requests;

class SiteOpportunityRequest extends ArticleRequest
{
    protected function extraRules(): array
    {
        // Тег есть и у новостей — он в базовых правилах. Здесь — только срок.
        return [
            'deadline_at' => ['nullable', 'date'],
        ];
    }
}
