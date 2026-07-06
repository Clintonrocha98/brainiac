<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Projects\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use He4rt\Catalog\Actions\RotateFederationCredentials;
use He4rt\Catalog\Models\Project;

/**
 * Rotaciona as credenciais da federação de um Projeto e revela os novos
 * valores UMA única vez, numa notificação persistente. As credenciais nunca
 * são exibidas fora desse momento.
 */
class RotateFederationCredentialsAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('panel-admin::catalog.projects.actions.rotate.label'))
            ->icon(Heroicon::OutlinedArrowPath)
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading(__('panel-admin::catalog.projects.actions.rotate.heading'))
            ->modalDescription(__('panel-admin::catalog.projects.actions.rotate.description'))
            ->action(static function (Project $record): void {
                $credentials = resolve(RotateFederationCredentials::class)->execute($record);

                Notification::make()
                    ->success()
                    ->persistent()
                    ->title(__('panel-admin::catalog.projects.actions.rotate.success_title'))
                    ->body(sprintf(
                        '<p>%s</p><p class="mt-2 font-mono text-xs break-all"><strong>%s:</strong> %s</p><p class="font-mono text-xs break-all"><strong>%s:</strong> %s</p>',
                        e(__('panel-admin::catalog.projects.actions.rotate.success_body')),
                        e(__('panel-admin::catalog.projects.actions.rotate.token_label')),
                        e($credentials->webhookToken),
                        e(__('panel-admin::catalog.projects.actions.rotate.secret_label')),
                        e($credentials->hmacSecret),
                    ))
                    ->send();
            });
    }

    public static function getDefaultName(): ?string
    {
        return 'rotateFederationCredentials';
    }
}
