<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Tag;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $tags = Tag::with('content')->get();

            return response()->json([
                'data' => $tags
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
                'name' => 'required|string',
            ]);

            $data['slug'] = Str::slug($data['name']);

            $tag = Tag::create($data);

            return response()->json([
                'data' => $tag
            ], 200);

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
            $tag = Tag::with('content')->where('id', $id)->first();

            return response()->json([
                'data' => $tag
            ], 200);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ]);
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
    public function update(Request $request, string $id)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255'
            ]);

            $tag = Tag::findOrFail($id);

            // update slug based on new name
            $data['slug'] = Str::slug($data['name']);

            $tag->update($data);

            return response()->json([
                'message' => 'Tag updated successfully',
                'data' => $tag
            ], 200);
        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ], 500);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $tag = Tag::findOrFail($id);
            $tag->delete();

            return response()->json([
                'message' => 'Tas was deleted successfully !'
            ], 200);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ], 500);
        }
    }
}
