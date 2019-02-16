<?php

namespace App\Api\Controllers;
use App\Account;
use Illuminate\Http\Request;
use JWTAuth;
use Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
class AuthController extends BaseController
{
    private $code=null;

    /**
     * The authentication guard that should be used.
     *
     * @var string
     */
    public function __construct()
    {
        parent::__construct();


    }
    public function test()
    {
        echo 'test';
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function authenticate(Request $request)
    {
        $payload = [
            'email' => $request->get('email'),
            'password' => $request->get('password')
        ];
        try {
            if (!$token = JWTAuth::attempt($payload)) {
                return response()->json(['error' => 'token_not_provided'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => '不能创建token'], 500);
        }
        return response()->json(compact('token'));
    }

    /**
     * @param Request $request
     */
    public function register(Request $request)
    {
        //判断是否手机注册
        $data['code']=$request->input('code','null');

        if ($data['code']!=null && is_numeric( $data['code'] ) && strlen($data['code'])==4){
            $newUser = [
                'phone' => $request->get('phone'),

            ];

            $user = Account::create($newUser);
            $token = JWTAuth::fromUser($user);
            return $token;
        }

        $credentials=$request->only(['email','name','password']);
        $validator = Validator::make($credentials, [
            'email'=>'required|regex:/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
            'name'=>'required|regex:/^[A-Za-z0-9]+$/',
            'password'=>'required|regex:/^[A-Za-z0-9]{4,}$/',
        ],[
            'name.regex'=>'名字必須為英文或者數字組成',
            'password.regex'=>'密碼必須為英文或者數字組成，至少4位',
            'email.regex'=>'邮箱格式不正确'
        ]);
        if ($validator->fails()){
            return $this->responseError($validator->errors()->first(),422);

        }



        $newUser = [
            'email' => $request->get('email'),
            'name' => $request->get('name'),
            'password' => bcrypt($request->get('password'))
        ];
        $user = Account::create($newUser);
        $token = JWTAuth::fromUser($user);
        return $token;
    }

    /****
     * 获取用户的信息
     * @return \Illuminate\Http\JsonResponse
     */
    public function AuthenticatedUser()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }
        // the token is valid and we have found the user via the sub claim
        return response()->json(compact('user'));
    }




}