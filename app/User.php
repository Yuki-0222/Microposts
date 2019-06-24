<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];
    
    public function microposts()
    {
        return $this->hasMany(Micropost::class);
    }

    
    
    // *** 追加 ***
    public function favorites()
    {
        return $this->belongsToMany(Micropost::class, 'favorites', 'user_id', 'micropost_id')->withTimestamps(); // 関連のModel名, 中間テーブル名, 自身, 関連先 
    }
    
    public function favorite($micropostId)
    {
        // 既にお気に入りしているかの確認
        $exist = $this->is_favorite($micropostId);
        
        if ($exist) {
            // 既にお気に入りなら何もしない
            return false;
        } else {
            // お気に入りしていなければ、お気に入りする
            $this->favorites()->attach($micropostId);
            return true;
        }
    }
    
    public function unfavorite($micropostId)
    {
        // 既にお気に入りしているかの確認
        $exist = $this->is_favorite($micropostId);
        
        if ($exist) {
            // 既にお気に入りならお気に入りを外す
            $this->favorites()->detach($micropostId);
            return true;
        } else {
            // お気に入りしていなければ、何もしない
            return false;
        }
    }
    
    public function is_favorite($micropostId)
    {
        return $this->favorites()->where('micropost_id', $micropostId)->exists();
    }
    // ************

    
    public function followings()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'user_id', 'follow_id')->withTimestamps(); // 第一引数：Model、第二引数：中間テーブル、第三引数：自身、第四引数：関連先
    }
    
    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'follow_id', 'user_id')->withTimestamps();
    }
    
    public function follow($userId)
    {
        // 既にフォローしているかの確認
        $exist = $this->is_following($userId);
        // 相手が自分自身ではないかの確認
        $its_me = $this->id == $userId;
        
        if ($exist || $its_me) {
            // 既にフォローしていれば何もしない
            return false;
        } else {
            // 未フォローであればフォローする
            $this->followings()->attach($userId);
            return true;
        }
    }
    
    public function unfollow($userId)
    {
        // 既にフォローしているかの確認
        $exist = $this->is_following($userId);
        // 相手が自分自身ではないかの確認
        $its_me = $this->id == $userId;
        
        if ($exist && !$its_me) {
            // 既にフォローしていればフォローを外す
            $this->followings()->detach($userId);
            return true;
        } else {
            // 未フォローであれば何もしない
            return false;
        }
    }
    
    public function is_following($userId)
    {
        return $this->followings()->where('follow_id', $userId)->exists();
    }
    
    public function feed_microposts()
    {
        $follow_user_ids = $this->followings()->pluck('users.id')->toArray(); // UserがフォローしているUserのidの配列を取得、users_idのみを取得して配列にしている
        $follow_user_ids[] = $this->id; // 自分のidも配列に追加
        return Micropost::whereIn('user_id', $follow_user_ids); // user_idで、$follow_user_idsの中にあるuser_idをすべて取得してreturn
    }
}

