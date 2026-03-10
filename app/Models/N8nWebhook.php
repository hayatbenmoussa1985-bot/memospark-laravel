<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class N8nWebhook extends Model
{
    protected $table = 'n8n_webhooks';

    protected $fillable = [
        'event_type',
        'payload',
        'status',
        'response',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'response' => 'array',
        ];
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'received');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    /**
     * Mark the webhook as processed.
     */
    public function markAsProcessed(array $response = null): void
    {
        $this->update([
            'status' => 'processed',
            'response' => $response,
        ]);
    }

    /**
     * Mark the webhook as failed.
     */
    public function markAsFailed(array $response = null): void
    {
        $this->update([
            'status' => 'failed',
            'response' => $response,
        ]);
    }
}
