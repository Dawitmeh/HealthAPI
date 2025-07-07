<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Content;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class ContentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $contents = Content::with('category', 'tags')->get();

            // Append full URL for the file
            $contents->transform(function ($content) {
                $content->file_url = asset('storage/' . $content->file_url);
                return $content;
            });

            return response()->json([
                'data' => $contents
            ], 200);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'category_id' => 'required|exists:categories,id',
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'type' => 'required',
                'file_url' => 'required|string',
                'is_published' => 'nullable',
            ]);

            if (isset($data['file_url'])) {
                $relativePath = $this->saveVideo($data['file_url']);
                $data['file_url'] = $relativePath;
            }

            $data['slug'] = Str::slug($data['title']);

            $content = Content::create([
                'category_id' => $request->input('category_id'),
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'type' => $request->input('type'),
                'file_url' => $data['file_url'],
                'slug' => $data['slug'],
                'is_published' => $request->input('is_published'),
                'published_at' => Carbon::now()
            ]);

            return response()->json([
                'data' => $content
            ], 201);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $content = Content::with('category', 'tags.tag')->where('id', $id)->first();

            if (!$content) {
                return response()->json([
                    'error' => 'Content not found'
                ], 404);
            }

            $content->file_url = asset('storage/' . $content->file_url);

            return response()->json([
                'data' => $content
            ]);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $data = $request->validate([
                'category_id'   => 'required|exists:categories,id',
                'title'         => 'required|string|max:255',
                'description'   => 'required|string',
                'type'          => 'required|string',
                'file_url'      => 'nullable|string',
                'is_published'  => 'nullable|boolean',
            ]);

            $content = Content::findOrFail($id);

            // Update video if new file_url provided
            if (!empty($data['file_url'])) {
                $data['file_url'] = $this->saveVideo($data['file_url']);
            } else {
                unset($data['file_url']); // retain old one if not updating
            }

            // Update slug based on new title
            $data['slug'] = Str::slug($data['title']);

            $content->update([
                'category_id' => $request->input('category_id'),
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'type' => $request->input('type'),
                'file_url' => $data['file_url'],
                'is_published' => $request->input('is_published'),
                'published_at' => Carbon::now()
            ]);

            return response()->json([
                'message' => 'Content updated successfully.',
                'data' => $content
            ], 200);

        } catch (Exception $ex) {
            return response()->json([
                'error' => 'Failed to update content.',
                'details' => $ex->getMessage()
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $content = Content::findOrFail($id);
            $content->delete();

            return response()->json([
                'message' => 'Content Deleted'
            ], 200);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    public function saveVideo($video) 
    {
        // check if input is valid base64 video string
        if (preg_match('/^data:video\/(\w+);base64,/', $video, $type)) {

            $video = substr($video, strpos($video, ',') + 1);

            // get file extension
            $type = strtolower($type[1]); // mp4, webm, ogg

            // validate file type
            if (!in_array($type, ['mp4', 'webm', 'ogg'])) {
                throw new Exception('Invalid video type');
            }

            // decode base64 string
            $video = str_replace(' ', '+', $video);
            $video = base64_decode($video);

            if ($video === false) {
                throw new Exception('base64_decode failed');
            }
        } else {
            throw new Exception('Invalid video data URI');
        }

        // define paths
        $fileName = Str::random() . '.' . $type;
        $relativePath = 'videos/' . $fileName;
        $storagePath = storage_path('app/public/' . $relativePath);

        // make sure directory exists
        if (!File::exists(dirname($storagePath))) {
             File::makeDirectory(dirname($storagePath), 0755, true);
        }
        
        // save video file
        file_put_contents($storagePath, $video);

        return $relativePath;
    }
}
