<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    /**
     * POST /api/v1/uploads/avatar
     */
    public function avatar(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|image|max:5120', // 5MB max
        ]);

        $path = $this->storeFile($request->file('file'), 'avatars', $request->user()->id);

        // Update user avatar
        $request->user()->update(['avatar_path' => $path]);

        return response()->json([
            'path' => $path,
            'message' => 'Avatar uploaded successfully.',
        ]);
    }

    /**
     * POST /api/v1/uploads/image
     * General purpose image upload (deck covers, card images).
     */
    public function image(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|image|max:10240', // 10MB max
            'type' => 'required|in:deck_cover,card_image',
        ]);

        $folder = match ($request->type) {
            'deck_cover' => 'decks',
            'card_image' => 'cards',
        };

        $path = $this->storeFile($request->file('file'), $folder, $request->user()->id);

        return response()->json([
            'path' => $path,
            'message' => 'Image uploaded successfully.',
        ]);
    }

    /**
     * POST /api/v1/uploads/audio
     */
    public function audio(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:mp3,wav,m4a,ogg|max:51200', // 50MB max
        ]);

        $path = $this->storeFile($request->file('file'), 'audios', $request->user()->id);

        return response()->json([
            'path' => $path,
            'message' => 'Audio uploaded successfully.',
        ]);
    }

    /**
     * Store a file to local storage.
     */
    private function storeFile($file, string $folder, int $userId): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        $path = "{$folder}/{$userId}/{$filename}";

        Storage::disk('local')->putFileAs(
            "{$folder}/{$userId}",
            $file,
            $filename
        );

        return $path;
    }
}
