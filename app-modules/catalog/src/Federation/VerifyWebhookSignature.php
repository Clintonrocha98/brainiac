<?php

declare(strict_types=1);

namespace He4rt\Catalog\Federation;

final class VerifyWebhookSignature
{
    public function matches(string $payload, string $signature, string $secret): bool
    {
        return hash_equals(hash_hmac('sha256', $payload, $secret), $signature);
    }
}
