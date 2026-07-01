<?php

declare(strict_types=1);

namespace App\Filament\Plugins\KnowledgeBase;

use Guava\FilamentKnowledgeBase\Enums\NodeType;
use Guava\FilamentKnowledgeBase\Models\FlatfileNode;

/**
 * @property-read string $panel_id
 */
class BetterFlatfileNode extends FlatfileNode
{
    public function getUrl(): string
    {
        if ($this->getType() === NodeType::Link) {
            $url = $this->getData()['url'] ?? '';

            return is_string($url) ? $url : '';
        }

        return BetterViewDocumentation::getUrl(parameters: [
            'record' => $this,
        ], panel: $this->panel_id);
    }
}
