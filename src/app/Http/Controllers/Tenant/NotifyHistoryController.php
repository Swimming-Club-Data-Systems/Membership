<?php

namespace App\Http\Controllers\Tenant;

use App\Business\CollectionTransforms\NotifyHistoryTransform;
use App\Http\Controllers\Controller;
use App\Models\Tenant\NotifyHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class NotifyHistoryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view', NotifyHistory::class);

        $emails = null;

        if ($request->query('query')) {
            // Enable search once ready
            // $emails = NotifyHistory::search($request->query('query'))->paginate(config('app.per_page'));
        } else {
            $emails = NotifyHistory::orderBy('Date', 'desc')->with(['author'])->paginate(config('app.per_page'));
        }

        $emails->getCollection()->transform(function (NotifyHistory $item) {
            return NotifyHistoryTransform::transform($item);
        });

        return Inertia::render('Notify/EmailHistory', [
            'emails' => $emails->onEachSide(3),
        ]);
    }

    public function show(NotifyHistory $email)
    {
        $this->authorize('view', NotifyHistory::class);
        return response()->json($email->jsonSerialize());
    }

    public function downloadFile(NotifyHistory $email, Request $request)
    {
        $this->authorize('view', NotifyHistory::class);
        $file = Arr::first($email->JSONData['Attachments'] ?? [], function ($value) use ($request) {
            return $value['URI'] == $request->input('file');
        });

        if ($file) {
            $filename = $file['Filename'] ?? 'file';
            $mime = $file['MIME'];

            $disposition = 'attachment; filename="' . addslashes($filename) . '"';
            if ($mime == "application/pdf" || str_contains($mime, "image") || str_contains($mime, "image")) {
                $disposition = 'inline';
            }

            try {
                $url = Storage::temporaryUrl(
                    $email->Tenant . '/' . $request->input('file'),
                    now()->addMinutes(5),
                    [
                        'ResponseContentDisposition' => $disposition,
                    ]
                );
                return redirect($url);
            } catch (\Exception $e) {
                abort(404, 'File not found in file storage');
            }
        } else {
            abort(404);
        }
    }
}
