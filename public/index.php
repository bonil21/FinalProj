<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

// Raise default PHP execution timeout to avoid 30-second request failures.
// You can override this per environment via APP_MAX_EXECUTION_TIME.
$maxExecutionTime = (int) ($_SERVER['APP_MAX_EXECUTION_TIME'] ?? $_ENV['APP_MAX_EXECUTION_TIME'] ?? getenv('APP_MAX_EXECUTION_TIME') ?: 300);
if ($maxExecutionTime >= 0) {
    @ini_set('max_execution_time', (string) $maxExecutionTime);
    if (\function_exists('set_time_limit')) {
        @set_time_limit($maxExecutionTime);
    }
}

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
