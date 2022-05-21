<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function attachPost(Request $request){
        try {
            //request validation
            Validator::make($request->all(), [
                'post_content' => 'required|string',
            ])->validate();

            //save person post
            $page = new Post();
            $page->user_id = auth()->user()->id;
            $page->post_content = $request->post_content;
            $page->save();

            //return success message
            return response()->json(['status' => 'success', 'data' => 'Post Published Successfully']);
        } catch (ValidationException $exception) {
            return response()->json([
                'status' => 'error',
                'errors' => $exception->errors(),
            ], 422);
        }

    }
}
