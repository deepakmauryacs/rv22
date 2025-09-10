<?php

namespace App\Services;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;


class ExportService
{
    public function storeAndDownload($export, string $fileName): array
    {
        $query = method_exists($export, 'query') ? $export->query() : null;

        if ($query && !$query->exists()) {
            return [
                'success' => false,
                'fetchRow' => false,
                'message' => 'No record found for export.'
            ];
        }

        $filePath = 'exports/' . $fileName;
        $stored = Excel::store($export, $filePath, 'public');
        // File::move(storage_path('app/public/'.$filePath), public_path('uploads/exl/'.$fileName));
        

        if (!$stored) {
            return [
                'success' => false,
                'message' => 'Store failed'
            ];
        }
        $sourcePath = storage_path('app/public/' . $filePath);
        $destinationDir = public_path('uploads/exl');
        $destinationPath = $destinationDir . '/' . $fileName;
        if (!File::exists($destinationDir)) {
            File::makeDirectory($destinationDir, 0775, true); 
        }
        File::move($sourcePath, $destinationPath);
        return [
            'success' => true,
            'download_url' => asset('public/uploads/exl/' . $fileName)
        ];
    }
    
    public function deleteExportFile(string $fileUrl): array
    {
        $relativePath = str_replace(asset('/'), '', $fileUrl); 
        $relativePath = ltrim(preg_replace('/^public\//', '', $relativePath), '/');
        $fullPath = public_path($relativePath);
        if (file_exists($fullPath)) {
            try {
                unlink($fullPath);
                return ['success' => true];
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'message' => 'File deletion error: ' . $e->getMessage()
                ];
            }
        }

        return [
            'success' => false,
            'message' => 'File not found at ' . $relativePath
        ];
    }

}
