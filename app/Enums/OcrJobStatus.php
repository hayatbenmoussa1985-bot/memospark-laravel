<?php

namespace App\Enums;

enum OcrJobStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';

    /**
     * Check if the job is still in progress.
     */
    public function isInProgress(): bool
    {
        return in_array($this, [self::Pending, self::Processing]);
    }

    /**
     * Check if the job has finished (success or failure).
     */
    public function isFinished(): bool
    {
        return in_array($this, [self::Completed, self::Failed]);
    }

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Processing => 'Processing',
            self::Completed => 'Completed',
            self::Failed => 'Failed',
        };
    }
}
