<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MicropostsController extends Controller
{
    public function index()
    {
        $data = [];
        if (\Auth::check()) { // Auth::check() はログインしているか確認する関数
            $user = \Auth::user(); // Auth::user() はログイン中のユーザーを取得する関数
            $microposts = $user->feed_microposts()->orderBy('created_at', 'desc')->paginate(10);
            
            $data = [
                'user' => $user,
                'microposts' => $microposts,
                ];
        }
        
        return view('welcome', $data);
    }
    
    public function store(Request $request)
    {
        $this->validate($request, [
            'content' => 'required|max:191',
        ]);
        
        $request->user()->microposts()->create([
            'content' => $request->content,
        ]);
        
        return back();
    }
    
    public function destroy($id)
    {
        $micropost = \App\Micropost::find($id);
        
        if (\Auth::id() === $micropost->user_id) {
            $micropost->delete();
        }
        
        return back();
    }
}
