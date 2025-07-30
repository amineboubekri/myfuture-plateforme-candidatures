<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileUploadService
{
    public function upload(UploadedFile $file, $path = 'documents')
    {
        try {
            // Generate unique filename with timestamp and original name
            $filename = time() . '_' . $file->getClientOriginalName();
            
            // Store file using Laravel's storage system (public disk)
            $filePath = $file->storeAs($path, $filename, 'public');
            
            // Return the relative path for database storage
            return $filePath;
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('File upload failed: ' . $e->getMessage());
            return false;
        }
    }

    public function delete($filePath)
    {
        try {
            // Use Laravel's storage system to delete file
            if (Storage::disk('public')->exists($filePath)) {
                return Storage::disk('public')->delete($filePath);
            }
            
            return false;
        } catch (\Exception $e) {
            \Log::error('File deletion failed: ' . $e->getMessage());
            return false;
        }
    }
}
