<?php

declare(strict_types=1);

namespace He4rt\Catalog\Federation\Http;

use He4rt\Catalog\DTOs\Snapshot;
use He4rt\Catalog\DTOs\SnapshotEntry;
use He4rt\Catalog\Enums\Area;
use He4rt\Catalog\Enums\Format;
use He4rt\Catalog\Enums\Purpose;
use He4rt\Catalog\Federation\ReconcileSnapshot;
use He4rt\Catalog\Federation\VerifyWebhookSignature;
use He4rt\Catalog\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ReceiveSnapshotController
{
    public function __invoke(
        Request $request,
        VerifyWebhookSignature $verifier,
        ReconcileSnapshot $reconcile,
    ): JsonResponse {
        $acronym = (string) $request->input('acronym');
        $project = Project::query()->where('acronym', $acronym)->firstOrFail();

        abort_unless(
            is_string($project->hmac_secret) && $verifier->matches(
                $request->getContent(),
                (string) $request->header('X-Signature'),
                $project->hmac_secret,
            ),
            Response::HTTP_FORBIDDEN,
        );

        /** @var array<int, array<string, string>> $rawEntries */
        $rawEntries = $request->input('entries', []);

        $entries = array_map(static fn (array $e): SnapshotEntry => new SnapshotEntry(
            qualifiedId: $e['qualified_id'],
            nativeId: $e['native_id'],
            title: $e['title'],
            summary: $e['summary'],
            purpose: Purpose::from($e['purpose']),
            format: Format::from($e['format']),
            department: Area::from($e['department']),
            bodyMarkdown: $e['body_markdown'],
            gitPointer: $e['git_pointer'] ?? null,
        ), $rawEntries);

        $reconcile->execute(new Snapshot($acronym, $entries));

        return response()->json(['status' => 'ok']);
    }
}
