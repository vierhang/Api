<?php

namespace App\Api\Controllers;

use App\Http\Controllers\Controller;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    use Helpers;
    protected $response_data = [
        'success'=> true,
        'error_code' => 0,
        'data'   => [],
        'error_msg' => ''
    ];
    /****
     * BaseController constructor.
     */
    public function __construct()
    {

    }

    public function getuserinfo($id){
        return (new \App\Account())->where('id',$id)->first();
    }

    public function responseError($message,$code = 1,$status_code = 400){
        $this->response_data['success'] = false;
        $this->response_data['error_msg'] = $message;
        $this->response_data['error_code'] = $code;
        if($code == 422){
            $status_code = 422;
        }
        return response()->json($this->response_data,$status_code);
    }
}