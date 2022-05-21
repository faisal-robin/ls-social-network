<?php

namespace App\Http\Controllers;

use App\Models\FollowPage;
use App\Models\FollowPerson;
use App\Models\PagePost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function register(Request $request){
        try {
            //request validation
            Validator::make($request->all(), [
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'email' => 'required|email',
                'password' => 'required',
            ])->validate();

            //save person
            $user = new User;
            $user->name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->password = bcrypt($request->password); //password encryption
            $user->save();

            return response()->json(['status' => 'success', 'data' => $user]);

        } catch (ValidationException $exception) {
            return response()->json([
                'status' => 'error',
                'errors' => $exception->errors(),
            ], 422);
        }

    }

    public function login(Request $request){

        //request validation
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            //check valid user
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json('Login invalid', 503);
            }

            //generate Bearer authorization token
            $login['token'] = $user->createToken('token')->plainTextToken;
            $login['user_info'] = $user;

            //return token and user object
            return response()->json(['status' => 'success', 'data' => $login]);
        } catch (ValidationException $exception) {
            return response()->json([
                'status' => 'error',
                'errors' => $exception->errors(),
            ], 422);
        }
    }

    public function followPerson($person_id){
        try {
            //check valid user and own id
            $user_info = User::find($person_id);
            if(!$user_info){
                return response()->json(['status' => 'error', 'msg' => 'Invalid Person ID']);
            }elseif(auth()->user()->id == $person_id){
                return response()->json(['status' => 'error', 'msg' => "Can't follow yourself"]);
            }

            //save following person
            $follow_person = new FollowPerson;
            $follow_person->user_id = auth()->user()->id;
            $follow_person->following_user_id = $person_id;
            $follow_person->save();

            return response()->json(['status' => 'success', 'msg' => 'Successfully following '.$user_info->fullname]);

        }  catch (\Exception $exception) {
            return ['status' => 'error', 'data' => $exception->getMessage()];
        }
    }

    public function personFeeds(){
        try {
            $user_id = auth()->user()->id;
            //get follow person post
            $following_ids = FollowPerson::where('user_id',$user_id)->pluck('following_user_id');
            $persons_post = Post::with('user')->whereIn('user_id',$following_ids)->get();

            //get follow page post
            $page_ids = FollowPage::where('user_id',$user_id)->pluck('page_id');
            $pages_post = PagePost::with(['user','page'])->whereIn('page_id',$page_ids)->get();

            //marge person post and page post data
            $posts = [];
            foreach ($persons_post as $person_post){
                $data['person_name'] = $person_post['user']['fullname'];
                $data['page_name'] = '';
                $data['content'] = $person_post['post_content'];
                $data['date_time'] = $person_post['created_at'];
                $posts[] = $data;
            }

            foreach ($pages_post as $page_post){
                $data['person_name'] = $page_post['user']['fullname'];
                $data['page_name'] = $page_post['page']['page_name'];
                $data['content'] = $page_post['page_content'];
                $data['date_time'] = $page_post['created_at'];
                $posts[] = $data;
            }

            return response()->json(['status' => 'success', 'feeds' => $posts]);

        }  catch (\Exception $exception) {
            return ['status' => 'error', 'data' => $exception->getMessage()];
        }
    }
}
