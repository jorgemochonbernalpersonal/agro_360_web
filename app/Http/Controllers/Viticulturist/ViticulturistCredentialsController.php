<?php

namespace App\Http\Controllers\Viticulturist;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ViticulturistCredentialsController extends Controller
{
    public function download(Request $request)
    {
        $viticulturistId = $request->query('id');

        $pdfPath = session('viticulturist_credentials_pdf');
        $viticulturistName = session('viticulturist_created_name');

        if (! $pdfPath && $viticulturistId) {
            $files = glob(storage_path('app/temp') . '/credentials_' . $viticulturistId . '_*.pdf');

            if (! empty($files)) {
                usort($files, fn ($a, $b) => filemtime($b) - filemtime($a));
                $pdfPath = $files[0];

                $viticulturist = User::find($viticulturistId);
                if ($viticulturist) {
                    $viticulturistName = $viticulturist->name;
                }
            }
        }

        if (! $pdfPath || ! file_exists($pdfPath)) {
            return redirect()
                ->route('viticulturist.personal.index')
                ->with('error', __('El PDF de credenciales no está disponible. El archivo puede haber expirado.'));
        }

        $filename = 'credenciales_' . Str::slug($viticulturistName ?? 'viticultor') . '_' . now()->format('Y-m-d') . '.pdf';

        session()->forget(['viticulturist_credentials_pdf', 'viticulturist_created_id', 'viticulturist_created_name']);

        return response()->download($pdfPath, $filename)->deleteFileAfterSend(true);
    }
}
