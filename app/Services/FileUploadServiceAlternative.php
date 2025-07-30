<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileUploadServiceAlternative
{
    public function upload(UploadedFile $file, $path = 'documents')
    {
        try {
            // Generate unique filename with timestamp and original name
            $filename = time() . '_' . $file->getClientOriginalName();
            
            // First, try to use Laravel's storage system (public disk)
            $filePath = $file->storeAs($path, $filename, 'public');
            
            // Also copy to public/storage for direct access if symlink doesn't work
            $publicPath = public_path('storage/' . $path);
            if (!file_exists($publicPath)) {
                mkdir($publicPath, 0755, true);
            }
            
            $publicFilePath = $publicPath . '/' . $filename;
            copy($file->getRealPath(), $publicFilePath);
            
            // Return the relative path for database storage
            return $filePath;
        } catch (\Exception $e) {
            // Fallback: Store directly in public directory
            try {
                $publicPath = public_path('uploads/' . $path);
                if (!file_exists($publicPath)) {
                    mkdir($publicPath, 0755, true);
                }
                
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move($publicPath, $filename);
                
                // Return path relative to public
                return 'uploads/' . $path . '/' . $filename;
            } catch (\Exception $e2) {
                // Log the error for debugging
                \Log::error('File upload failed: ' . $e2->getMessage());
                return false;
            }
        }
    }

    public function delete($filePath)
    {
        try {
            // Try Laravel storage first
            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            
            // Also try public directory
            $publicPath = public_path('storage/' . $filePath);
            if (file_exists($publicPath)) {
                unlink($publicPath);
            }
            
            // Try fallback location
            $fallbackPath = public_path($filePath);
            if (file_exists($fallbackPath)) {
                unlink($fallbackPath);
            }
            
            return true;
        } catch (\Exception $e) {
            \Log::error('File deletion failed: ' . $e->getMessage());
            return false;
        }
    }
}
