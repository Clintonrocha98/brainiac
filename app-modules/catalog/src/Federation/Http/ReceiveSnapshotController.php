<?php

declare(strict_types=1);

namespace He4rt\Catalog\Federation\Http;

use He4rt\Catalog\DTOs\Snapshot;
use He4rt\Catalog\DTOs\SnapshotEntry;
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

        /** @var array<int, array<string, mixed>> $rawEntries */
        $rawEntries = $request->input('entries', []);

        $entries = array_map(SnapshotEntry::fromPayload(...), $rawEntries);

        $repoUrl = $request->input('repo_url');
        $defaultBranch = $request->input('default_branch');

        $reconcile->execute(new Snapshot(
            acronym: $acronym,
            entries: $entries,
            repoUrl: is_string($repoUrl) ? $repoUrl : null,
            defaultBranch: is_string($defaultBranch) ? $defaultBranch : null,
        ));

        return response()->json(['status' => 'ok']);
    }
}
