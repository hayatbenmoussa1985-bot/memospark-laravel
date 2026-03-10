<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\N8nWebhook;
use App\Models\OcrJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    /**
     * POST /api/v1/webhooks/n8n
     * Callback endpoint for n8n workflows.
     * Secured via X-API-Key header.
     */
    public function n8n(Request $request): JsonResponse
    {
        // Verify API key
        $apiKey = $request->header('X-API-Key');
        if ($apiKey !== config('services.n8n.api_key')) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $request->validate([
            'event_type' => 'required|string',
            'payload' => 'required|array',
        ]);

        // Log the webhook
        $webhook = N8nWebhook::create([
            'event_type' => $request->event_type,
            'payload' => $request->payload,
            'status' => 'received',
        ]);

        // Process based on event type
        try {
            match ($request->event_type) {
                'ocr_completed' => $this->handleOcrCompleted($request->payload),
                'deck_generated' => $this->handleDeckGenerated($request->payload),
                default => null,
            };

            $webhook->markAsProcessed();
        } catch (\Exception $e) {
            $webhook->markAsFailed(['error' => $e->getMessage()]);
        }

        return response()->json(['message' => 'Webhook processed.']);
    }

    private function handleOcrCompleted(array $payload): void
    {
        if (!isset($payload['job_id'])) return;

        $job = OcrJob::find($payload['job_id']);
        if (!$job) return;

        if (isset($payload['error'])) {
            $job->markAsFailed($payload['error']);
            return;
        }

        if (isset($payload['deck_id'])) {
            $job->markAsCompleted($payload['deck_id']);
        }
    }

    private function handleDeckGenerated(array $payload): void
    {
        // Handle AI-generated deck callback from n8n
        // Implementation depends on n8n workflow specifics
    }
}
