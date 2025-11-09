<?php

namespace App\Http\Controllers;

use App\Http\Resources\UploadResource;
use App\Jobs\ProcessUpload;
use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function index()
    {
        return view('uploads.index');
    }

    public function store(Request $request)
{
    $request->validate([
        'file' => ['required','file','mimes:csv,txt'],
    ]);

    // Ensure the directory exists
    Storage::disk('local')->makeDirectory('uploads');

    // Save with a deterministic name
    $filename = Str::uuid().'.csv';
    $path = $request->file('file')->storeAs('uploads', $filename, 'local'); // returns 'uploads/<uuid>.csv'

    $upload = \App\Models\Upload::create([
        'original_name' => $request->file('file')->getClientOriginalName(),
        'stored_path'   => $path, // e.g. 'uploads/xxxx.csv'
        'status'        => 'pending',
    ]);

    \App\Jobs\ProcessUpload::dispatch($upload);

    return redirect()->back()->with('status','File queued for processing.');
}

    // JSON endpoint for polling
    public function list()
{
    $items = \App\Models\Upload::latest()->take(50)->get()->map(function ($u) {
        return [
            'id'             => $u->id,
            'created_at'     => optional($u->created_at)->toIso8601String(),
            'created_human'  => optional($u->created_at)->diffForHumans(),
            'original_name'  => $u->original_name,
            'status'         => $u->status,
            'rows_processed' => $u->rows_processed ?? 0,
            // keep errors but send at most 300 chars to UI
            'error'          => $u->error ? mb_strimwidth($u->error, 0, 300, '…') : null,
        ];
    });

     return UploadResource::collection(
        Upload::latest()->limit(50)->get()
    );
}


}
