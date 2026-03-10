<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\OcrJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OcrController extends Controller
{
    /**
     * POST /api/v1/ocr/upload
     * Upload an image for OCR processing.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|max:10240', // 10MB
            'job_type' => 'required|in:flashcards,qcm',
        ]);

        // Store image
        $file = $request->file('image');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = "ocr/{$request->user()->id}/{$filename}";

        Storage::disk('local')->putFileAs(
            "ocr/{$request->user()->id}",
            $file,
            $filename
        );

        // Create OCR job
        $job = OcrJob::create([
            'user_id' => $request->user()->id,
            'image_path' => $path,
            'job_type' => $request->job_type,
            'status' => 'pending',
        ]);

        // TODO: Trigger n8n webhook for OCR processing
        // $this->triggerN8nWebhook($job);

        return response()->json([
            'job' => [
                'id' => $job->id,
                'status' => $job->status->value,
                'job_type' => $job->job_type,
            ],
        ], 201);
    }

    /**
     * GET /api/v1/ocr/jobs/{id}
     * Check OCR job status.
     */
    public function status(Request $request, int $id): JsonResponse
    {
        $job = OcrJob::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $response = [
            'id' => $job->id,
            'status' => $job->status->value,
            'job_type' => $job->job_type,
            'created_at' => $job->created_at->toIso8601String(),
        ];

        if ($job->status->value === 'completed' && $job->result_deck_id) {
            $response['result_deck_id'] = $job->result_deck_id;
            $response['result_deck_uuid'] = $job->resultDeck?->uuid;
        }

        if ($job->status->value === 'failed') {
            $response['error_message'] = $job->error_message;
        }

        return response()->json(['job' => $response]);
    }
}
