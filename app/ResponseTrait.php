<?php

namespace App;

trait ResponseTrait
{


    public function getData($msg,$key,$data)
    {
        return response()->json([
            'status' => 200,
            'message' => $msg,
            $key => $data,
        ],200);
    }

    public function getError($status,$msg){
        return response()->json([
            'status'=>$status,
            'message'=>$msg,
        ],$status);
    }

    public function getSuccess($msg){
        return response()->json([
            'status'=>200,
            'message'=>$msg,
        ],200);
    }


}
