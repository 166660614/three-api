<?php

namespace App\Http\Controllers\User;

use App\Model\FriendModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use App\Model\GoodsModel;
use App\Model\UserFriendModel;

class DotController extends Controller
{
    public function dot(){
        $timestamps=$_POST['timestamps'];
        $user_id=$_POST['user_id'];
        if(empty($user_id)){
            $data=[
                'errcode'=>4001,
                'msg'=>'请先登录'
            ];
            return $data;
        }
        $goods_id=$_POST['goods_id'];
        $coll_key='dot:user:'.$user_id;
        $res=Redis::zAdd($coll_key,$timestamps,$goods_id);
        if($res){
            $data=[
                'errcode'=>0,
                'msg'=>'点赞成功'
            ];
        }else{
            $data=[
                'errcode'=>5001,
                'msg'=>'您已经赞过了'
            ];
        }
        return $data;
    }
    public function give(Request $request){
        $user_id=$request->input('user_id');
        if(empty($user_id)){
            $data=[
                'errcode'=>4001,
                'errmsg'=>'请先登录'
            ];
            return $data;
        }
        $coll_key='dot:user:'.$user_id;
        $res=Redis::zRange($coll_key,0,-1,true);
        if(empty($res)){
            $data=[
                'errcode'=>4002,
                'errmsg'=>'您未给任何商品点赞'
            ];
            return $data;
        }else{
            $arr=[];
            foreach ($res as $k=>$v){
                //echo $k;
                $res1=GoodsModel::where(['goods_id'=>$k])->first();
                $res1['add_time']=$v;
                if(empty($res1)){
                    $data=[
                        'errcode'=>4002,
                        'msg'=>'商品已不存在'
                    ];
                    return $data;
                }
                $arr[]=$res1;
            }
            $data=[
                'errcode'=>0,
                'msg'=>$arr
            ];
            return $data;
        }
    }
    public function end(Request $request){
        //print_r($_POST);
        $user_id=$request->input('user_id');
       // print_r($user_id);
        $end_where=[
          'user_id'=>$user_id,
        ];
        $friend_id=UserFriendModel::where($end_where)->pluck('friend_id')->toArray();
    //  print_r($end_data);
//        foreach ($end_data as $k=>$v) {
//            $friend[] = $v['friend_id'];
//        }
//        print_r($friend);

        $data=FriendModel::whereIn('friend_id',$friend_id)->get();
       // print_r($data);
        return $data;
}
}
