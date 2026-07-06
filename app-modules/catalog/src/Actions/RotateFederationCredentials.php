<?php

declare(strict_types=1);

namespace He4rt\Catalog\Actions;

use He4rt\Catalog\DTOs\FederationCredentials;
use He4rt\Catalog\Models\Project;
use Illuminate\Support\Str;

/**
 * Rotaciona o par de credenciais da federação (token do webhook + segredo
 * HMAC). O retorno é a única oportunidade de ler os valores em claro.
 */
final class RotateFederationCredentials
{
    public function execute(Project $project): FederationCredentials
    {
        $credentials = new FederationCredentials(
            webhookToken: Str::random(48),
            hmacSecret: Str::random(64),
        );

        $project->update([
            'webhook_token' => $credentials->webhookToken,
            'hmac_secret' => $credentials->hmacSecret,
        ]);

        return $credentials;
    }
}
