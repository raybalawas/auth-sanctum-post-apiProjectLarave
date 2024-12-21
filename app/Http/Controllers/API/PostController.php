<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseController as BaseController;


class PostController extends BaseController
{
    public function index()
    {
        $posts = Post::all();
        return $this->sendResponse($posts, 'All posts data retrieved successfully.');
    }

    public function create(Request $request)
    {

        $validatePosts = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'description' => 'required',
                'image' => 'required|mimes:gif,jpg,jpeg,png|max:2048',
            ]
        );


        if ($validatePosts->fails()) {
            return $this->sendError('Post creation failed', $validatePosts->errors()->all(), 400);
        }

        $img = $request->file('image');
        $imageName = uniqid() . '.' . $img->getClientOriginalExtension();
        $img->move(public_path('postImgs/'), $imageName);


        $post = Post::create([
            'title' => $request->name,
            'description' => $request->description,
            'image' => $imageName,
        ]);
        // dd($post);

        return $this->sendResponse($post, 'Post created successfully!');
    }

    public function show(string $id)
    {
        $post = Post::find($id);

        if (!$post) {
            return $this->sendError('Post not found.');
        }
        return $this->sendResponse($post, 'Post retrieved successfully!');
    }

    public function update(Request $request, string $id)
    {
        $validatePosts = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'description' => 'required',
                'image' => 'nullable|mimes:gif,jpg,jpeg,png|max:2048'
            ]
        );

        if ($validatePosts->fails()) {
            return $this->sendError('Post update failed', $validatePosts->errors()->all(), 400);
        }

        $post = Post::findOrFail($id);

        if ($request->hasFile('image')) {

            $oldImagePath = public_path('postImgs/') . $post->image;
            if (file_exists($oldImagePath) && $post->image) {
                unlink($oldImagePath);
            }

            $img = $request->file('image');
            $imageName = uniqid() . '.' . $img->getClientOriginalExtension();
            $img->move(public_path('postImgs/'), $imageName);
            $post->image = $imageName;
        }

        $post->update([
            'title' => $request->name,
            'description' => $request->description,
            'image' => $post->image ?? $post->image,
        ]);

        return $this->sendResponse($post, 'Post updated successfully!');
    }

    public function destroy(string $id)
    {
        $post = Post::findOrFail($id);

        $imagePath = public_path('postImgs/') . $post->image;
        if (file_exists($imagePath) && $post->image) {
            unlink($imagePath);
        }

        $post->delete();
        return $this->sendResponse($post, 'Post deleted successfully!');
    }
}
