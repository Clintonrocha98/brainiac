<?php

declare(strict_types=1);

use Spatie\Activitylog\Actions\CleanActivityLogAction;
use Spatie\Activitylog\Actions\LogActivityAction;
use Spatie\Activitylog\Models\Activity;

return [

    /*
     * If set to false, no activities will be saved to the database.
     */
    'enabled' => env('ACTIVITYLOG_ENABLED', default: true),

    /*
     * When the clean-command is executed, all recording activities older than
     * the number of days specified here will be deleted.
     */
    'clean_after_days' => 365,

    /*
     * If no log name is passed to the activity() helper
     * we use this default log name.
     */
    'default_log_name' => 'default',

    /*
     * You can specify an auth driver here that gets user models.
     * If this is null we'll use the current Laravel auth driver.
     */
    'default_auth_driver' => null,

    /*
     * If set to true, the subject returns soft deleted models.
     */
    'include_soft_deleted_subjects' => false,

    /*
     * This model will be used to log activity.
     * It should implement the Spatie\Activitylog\Contracts\Activity interface
     * and extend Illuminate\Database\Eloquent\Model.
     */
    'activity_model' => Activity::class,

    /*
     * Kept for the in-repo migrations that still reference these keys.
     * Spatie v5 itself no longer reads them — table/connection customization
     * moved onto a custom Activity model. Safe to keep as-is.
     */
    'table_name' => env('ACTIVITY_LOGGER_TABLE_NAME', 'activity_log'),

    'database_connection' => env('ACTIVITY_LOGGER_DB_CONNECTION'),

    /*
     * Action classes invoked by the package. Swap these to queue logging
     * or customize cleanup without overriding the whole logger.
     */
    'actions' => [
        'log_activity' => LogActivityAction::class,
        'clean_log' => CleanActivityLogAction::class,
    ],

    /*
     * Attributes globally excluded from every model's activity log.
     */
    'default_except_attributes' => [],
];
