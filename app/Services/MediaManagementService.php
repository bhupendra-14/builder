<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class MediaManagementService
{
    protected ?ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    public function uploadAndCompress(UploadedFile $file, string $folder = 'uncategorized'): array
    {
        $extension = $file->getClientOriginalExtension();
        $mimeType = $file->getMimeType();
        $originalName = $file->getClientOriginalName();
        $fileName = uniqid() . '_' . time() . '.' . $extension;
        
        $path = "assets/{$folder}/{$fileName}";
        $isImage = str_starts_with($mimeType, 'image');

        if ($isImage && in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'webp'])) {
            // Compress Image using Intervention Image v4
            $image = $this->manager->decodePath($file->getRealPath());
            
            // Auto compress to WebP at 80% quality
            // Use v4 webp encoder
            $encoded = $image->encode(new \Intervention\Image\Encoders\WebpEncoder(80));
            
            // Update path to reflect webp
            $path = preg_replace('/\.[^.]+$/', '.webp', $path);
            $mimeType = 'image/webp';
            $fileName = basename($path);
            
            Storage::disk('public')->put($path, (string) $encoded);
        } else {
            // Store raw file for SVGs, PDF, etc.
            $path = $file->storeAs("assets/{$folder}", $fileName, 'public');
        }

        return [
            'file_name' => $fileName,
            'original_name' => $originalName,
            'asset_type' => $isImage ? 'image' : 'document',
            'mime_type' => $mimeType,
            'size_bytes' => Storage::disk('public')->size($path),
            'folder' => $folder,
        ];
    }
    
    public function deleteFile(string $folder, string $fileName)
    {
        $path = "assets/{$folder}/{$fileName}";
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
