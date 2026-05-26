<?php

namespace App\Http\Controllers\Viticulturist;

use App\Http\Controllers\Controller;
use App\Models\CampaignDocument;
use Illuminate\Support\Facades\Storage;

class CampaignDocumentController extends Controller
{
    public function download(CampaignDocument $document)
    {
        if ($document->viticulturist_id !== auth()->id()) {
            abort(403, __('No tienes permiso para descargar este documento.'));
        }

        if (! $document->file_path || ! Storage::disk('private')->exists($document->file_path)) {
            abort(404, __('El archivo no existe.'));
        }

        return Storage::disk('private')->download(
            $document->file_path,
            $document->original_filename ?? basename($document->file_path)
        );
    }
}
