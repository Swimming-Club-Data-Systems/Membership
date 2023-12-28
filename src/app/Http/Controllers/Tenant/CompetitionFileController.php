<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use App\Models\Tenant\Competition;
use App\Models\Tenant\CompetitionFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompetitionFileController extends Controller
{
    public function upload(Competition $competition, Request $request)
    {
        $this->authorize('update', $competition);

        $request->validate([
            'file' => ['file', 'size:10240'],
        ]);

        /** @var Tenant $tenant */
        $tenant = tenant();

        $file = $request->file('file');

        $name = $file->getClientOriginalName();
        $size = $file->getSize();
        $mime = $file->getMimeType();

        $path = Storage::putFile($tenant->storagePath().'uploads', $file);

        $sequence = $competition->files()->max('sequence') + 1;

        $upload = $competition->files()->create([
            'path' => $path,
            'disk' => config('filesystems.default'),
            'original_name' => $name,
            'public_name' => $name,
            'mime_type' => $mime,
            'size' => $size,
            'public' => true,
            'sequence' => $sequence,
        ]);

        return $upload;
    }

    public function view(Competition $competition, CompetitionFile $file, Request $request)
    {
        $this->authorize('view', $competition);

        // Check request came from SCDS system and not another website
        if (trim($request->server('HTTP_REFERER'), '/') != $request->schemeAndHttpHost()) {
            abort(404);
        }

        $filename = $file->public_name;
        $mime = $file->mime_type;

        $disposition = 'attachment; filename="'.addslashes($filename).'"';
        if ($mime == 'application/pdf' || str_contains($mime, 'image') || str_contains($mime, 'image')) {
            $disposition = 'inline';
        }

        try {
            $url = Storage::temporaryUrl(
                $file->path,
                now()->addMinutes(5),
                [
                    'ResponseContentDisposition' => $disposition,
                ]
            );

            return redirect($url);
        } catch (\Exception $e) {
            abort(404, 'File not found in file storage');
        }
    }

    public function update(Competition $competition, CompetitionFile $file, Request $request)
    {
        $this->authorize('update', $competition);

        $request->validate([
            'name' => ['required', 'max:255'],
        ]);

        $file->public_name = $request->input('name');
        $file->save();

        return redirect(route('competitions.show', $competition));
    }

    public function delete(Competition $competition, CompetitionFile $file, Request $request)
    {
        $this->authorize('update', $competition);

        $file->delete();

        return redirect(route('competitions.show', $competition));
    }
}
