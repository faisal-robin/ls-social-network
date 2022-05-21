<?php

namespace App\Http\Controllers;

use App\Models\FollowPage;
use App\Models\page;
use App\Models\PagePost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PageController extends Controller
{
    public function create(Request $request){
        try {
            //request validation
             Validator::make($request->all(), [
                'page_name' => 'required|string',
            ])->validate();

            //save page
            $page = new Page;
            $page->user_id = auth()->user()->id;
            $page->page_name = $request->page_name;
            $page->save();

            //return success message
            return response()->json(['status' => 'success', 'data' => 'Page Save Successfully']);

        } catch (ValidationException $exception) {
            return response()->json([
                'status' => 'error',
                'errors' => $exception->errors(),
            ], 422);
        }
    }

    public function attachPost(Request $request,$id){
        try {
            //check user valid page
            $user_id = auth()->user()->id;
            $user_page_exist = page::where('user_id',$user_id)->where('id',$id)->count();
            if(!$user_page_exist){
                return response()->json(['status' => 'error', 'data' => 'Invalid Page ID']);
            }

            //request validation
            Validator::make($request->all(), [
                'page_content' => 'required|string',
            ])->validate();

            //save person page post
            $page = new PagePost();
            $page->user_id = $user_id;
            $page->page_id = $id;
            $page->page_content = $request->page_content;
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

    public function followPage($page_id){
        try {
            //check valid page and own page
            $page_info = Page::find($page_id);

            if(!$page_info){
                return response()->json(['status' => 'error', 'msg' => 'Invalid Page ID']);
            }elseif(auth()->user()->id == $page_info->user_id){
                return response()->json(['status' => 'error', 'msg' => "can't follow your page"]);
            }

            //save following page
            $follow_page = new FollowPage;
            $follow_page->user_id = auth()->user()->id;
            $follow_page->page_id = $page_id;
            $follow_page->save();

            return response()->json(['status' => 'success', 'msg' => 'Successfully following '.$page_info->page_name.' page']);

        }  catch (\Exception $exception) {
            return ['status' => 'error', 'data' => $exception->getMessage()];
        }

    }
}
