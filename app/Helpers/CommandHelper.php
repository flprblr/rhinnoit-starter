<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CommandHelper
{
    private static $startTimes = [];

    private static $logChannel = 'single';

    private static $fallbackChannel = 'single';

    /**
     * Sets the logging channel for commands
     */
    public static function setLogChannel(string $channel): void
    {
        self::$logChannel = $channel;
    }

    /**
     * Gets the current logging channel
     */
    public static function getLogChannel(): string
    {
        return self::$logChannel;
    }

    /**
     * Log with a fallback to the default channel if the custom channel does not exist
     */
    private static function logWithFallback(string $level, string $message, array $context = []): void
    {
        // Check if the channel exists in the configuration
        $channels = config('logging.channels', []);

        if (isset($channels[self::$logChannel])) {
            // Channel exists, use it
            Log::channel(self::$logChannel)->{$level}($message, $context);
        } else {
            // Channel does not exist, use the fallback
            Log::channel(self::$fallbackChannel)->{$level}($message, $context);
        }
    }

    /**
     * Starts time tracking for a command
     */
    public static function start(Command $command, ?string $commandName = null): void
    {
        $commandName = $commandName ?? $command->getName();
        $startTime = Carbon::now();

        self::$startTimes[$commandName] = $startTime;

        $message = "{$commandName} Started.";
        self::logWithFallback('info', $message);
        $command->info($message);
    }

    /**
     * Ends time tracking and records the execution time
     */
    public static function finish(Command $command, ?string $commandName = null, bool $success = true): void
    {
        $commandName = $commandName ?? $command->getName();

        if (! isset(self::$startTimes[$commandName])) {
            Log::warning("No start time found for command '{$commandName}'");

            return;
        }

        $startTime = self::$startTimes[$commandName];
        $endTime = Carbon::now();
        $executionTime = abs($endTime->timestamp - $startTime->timestamp);
        $formattedTime = self::formatExecutionTime($executionTime);

        if ($success) {
            $message = "{$commandName} Executed in {$formattedTime}.";
            self::logWithFallback('info', $message);
            $command->info($message);
        } else {
            $message = "{$commandName} Failed after {$formattedTime}.";
            self::logWithFallback('error', $message);
            $command->error($message);
        }

        // Clear the start time
        unset(self::$startTimes[$commandName]);
    }

    /**
     * Retrieves the elapsed time without stopping the tracking
     */
    public static function getElapsedTime(string $commandName): ?int
    {
        if (! isset(self::$startTimes[$commandName])) {
            return null;
        }

        $startTime = self::$startTimes[$commandName];
        $currentTime = Carbon::now();

        return abs($currentTime->timestamp - $startTime->timestamp);
    }

    /**
     * Formats the time in minutes:seconds
     */
    public static function formatTime(int $seconds): string
    {
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        return sprintf('%02d:%02d', $minutes, $remainingSeconds);
    }

    /**
     * Formats the execution time in a human-readable way
     */
    public static function formatExecutionTime(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;

        $parts = [];

        if ($hours > 0) {
            $parts[] = $hours.' '.($hours === 1 ? 'hour' : 'hours');
        }

        if ($minutes > 0) {
            $parts[] = $minutes.' '.($minutes === 1 ? 'minute' : 'minutes');
        }

        if ($remainingSeconds > 0 || empty($parts)) {
            $parts[] = $remainingSeconds.' '.($remainingSeconds === 1 ? 'second' : 'seconds');
        }

        return implode(' ', $parts);
    }

    /**
     * Logs progress without tracking time
     */
    public static function logProgress($command, string $message, string $level = 'info'): void
    {
        $commandName = is_string($command) ? $command : $command->getName();
        $logMessage = "{$commandName} {$message}";
        self::logWithFallback($level, $logMessage);
    }

    /**
     * Logs progress without time tracking (console only)
     */
    public static function consoleProgress(Command $command, string $message, string $level = 'info'): void
    {
        $commandName = $command->getName();
        $consoleMessage = "{$commandName} {$message}";
        $command->{$level}($consoleMessage);
    }

    /**
     * Logs progress without time tracking (console and logs)
     */
    public static function progress(Command $command, string $message, string $level = 'info'): void
    {
        $commandName = $command->getName();

        // Message for console
        $consoleMessage = "{$commandName} {$message}";
        $command->{$level}($consoleMessage);

        // Message for logs
        $logMessage = "{$commandName} {$message}";
        self::logWithFallback($level, $logMessage);
    }

    /**
     * Logs progress without time tracking (console and logs) - alias of progress
     */
    public static function stepProgress(Command $command, string $message, string $level = 'info'): void
    {
        self::progress($command, $message, $level);
    }

    /**
     * Ends the command successfully
     */
    public static function success(Command $command, ?string $commandName = null): int
    {
        self::finish($command, $commandName, true);

        return Command::SUCCESS;
    }

    /**
     * Ends the command with an error
     */
    public static function failure(Command $command, ?string $commandName = null): int
    {
        self::finish($command, $commandName, false);

        return Command::FAILURE;
    }

    /**
     * Complete wrapper for exception handling with returns
     */
    public static function handle(Command $command, callable $callback, ?string $commandName = null): int
    {
        self::start($command, $commandName);

        try {
            $callback();

            return self::success($command, $commandName);
        } catch (\Exception $e) {
            $command->error('Error executing the command: '.$e->getMessage());
            self::logWithFallback('error', 'Error in '.($commandName ?? class_basename($command)).': '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return self::failure($command, $commandName);
        }
    }
}
