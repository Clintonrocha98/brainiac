<?php

declare(strict_types=1);

namespace He4rt\Catalog\DTOs;

/**
 * Credenciais recém-geradas da federação de um Projeto. Trafega os valores em
 * claro UMA única vez (revelação pós-rotação); nunca persistir fora do model.
 */
final readonly class FederationCredentials
{
    public function __construct(
        public string $webhookToken,
        public string $hmacSecret,
    ) {}
}
