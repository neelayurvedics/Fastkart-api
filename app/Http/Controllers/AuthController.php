<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Rules\ReCaptcha;
use App\Enums\SMSMethod;
use App\Mail\ForgotPassword;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Traits\MessageTrait;
use App\Http\Traits\UtilityTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Validator;
use App\GraphQL\Exceptions\ExceptionHandler;

class AuthController extends Controller
{
    use UtilityTrait, MessageTrait;

    public function login(Request $request)
    {
        try {

            $user = $this->verifyLogin($request);
            if (!Hash::check($request->password, $user->password) || !$user->hasRole(RoleEnum::CONSUMER)) {
                throw new Exception(__('auth.invalid_credentials'), 400);
            }

            $token = $user->createToken('auth_token')->plainTextToken;
            $user->tokens()->update([
                'role_type' => $user->getRoleNames()->first()
            ]);

            return [
                'access_token' => $token,
                'permissions'  => $user->getAllPermissions(),
                'success' => true
            ];
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function login_with_numb(Request $request)
    {
        try {

            if (Helpers::isSMSLoginEnable()) {
                $user = $this->verifyLogin($request);
                if (!$user->hasRole(RoleEnum::CONSUMER)) {
                    throw new Exception(__('auth.invalid_detail'), 400);
                }

                $token = rand(11111, 99999);
                DB::table('auth_tokens')->insert([
                    'token' => $token,
                    'phone' => '+' . $request->country_code . $request->phone,
                    'created_at' => Carbon::now()
                ]);

                $message = [
                    'to' => '+' . $request->country_code . $request->phone,
                    'body' => 'Your OTP is ' . $token
                ];

                $defaultMethod = Helpers::getDefaultSMSMethod();
                $this->sendMessage($message, $defaultMethod);

                return [
                    'message' => __('auth.otp_sent'),
                    'success' => true
                ];
            }

            throw new Exception(__('auth.login_method_disabled'), 400);

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function verify_auth_token(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'country_code' => 'required',
                'phone'    => 'required',
                'token'    => 'required',
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->messages()->first(), 422);
            }

            $verify_otp = DB::table('auth_tokens')
                ->where('token', $request->token)
                ->where('phone', '+' . $request->country_code . $request->phone)
                ->where('created_at', '>', Carbon::now()->subHours(1))
                ->first();

            if (!$verify_otp) {
                throw new Exception(__('auth.invalid_token'), 400);
            }

            $user = User::where('phone', (string) $request->phone)->first();
            if (!$user->hasRole(RoleEnum::CONSUMER)) {
                throw new Exception(__('auth.invalid_detail'), 400);
            }

            $token = $user->createToken('auth_token')->plainTextToken;
            $user->tokens()->update([
                'role_type' => $user->getRoleNames()->first()
            ]);

            return [
                'access_token' => $token,
                'permissions'  => $user->getAllPermissions(),
                'success' => true
            ];

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function getAllSMSMethods()
    {
        $SMSMethods = [];
        $settings = Helpers::getSettings();
        $methods = SMSMethod::ALL_MESSAGE_METHODS;
        foreach ($methods as $method) {
            $SMSMethods[] = [
                "title" => $settings['sms_methods'][$method]['title'],
                "status" => $settings['sms_methods'][$method]['status']
            ];
        }
        return $SMSMethods;
    }

    public function verifyVendor(User $vendor)
    {
        if (Helpers::isMultiVendorEnable()) {
            if ($vendor->store?->is_approved) {
                return true;
            }

            throw new Exception(__('auth.await_store_approval'), 403);
        }

        throw new Exception(__('auth.multi_vendor_deactivated'), 403);
    }

    public function backendLogin(Request $request)
    {
        try {

            $user = $this->verifyLogin($request);
            if (!Hash::check($request->password, $user->password) || $user->hasRole(RoleEnum::CONSUMER)) {
                throw new Exception(__('auth.invalid_backend_credentials'), 400);
            }

            $token = $user->createToken('auth_token')->plainTextToken;
            if ($user->hasRole(RoleEnum::VENDOR)) {
                $this->verifyVendor($user);
            }

            $user->tokens()->update([
                'role_type' => $user->getRoleNames()->first()
            ]);

            return [
                'access_token' => $token,
                'permissions'  => $user->getAllPermissions(),
                'success' => true
            ];
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function verifyLogin(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'email'    => ['nullable', Rule::requiredIf(!$request->phone), 'email'],
                'password' => ['nullable', Rule::requiredIf(!$request->phone)],
                'recaptcha' => ['nullable', new ReCaptcha],
                'phone' => ['nullable', Rule::requiredIf(!$request->email)],
                'country_code' => ['nullable', Rule::requiredIf(!$request->email)]
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->messages()->first(), 422);
            }

            $user = User::where('email', $request->email)->first();
            if (!$request->email && $request->phone) {
                $user = User::where('phone', (string)$request->phone)->first();
            }

            if (!$user && isset($request->email)) {
                throw new Exception(__('auth.no_linked_email'), 400);
            }

            if (!$user && isset($request->phone)) {
                throw new Exception(__('auth.no_linked_number'), 400);
            }

            if (!$user->status) {
                throw new Exception(__('auth.disabled_account'), 400);
            }

            return $user;
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function register(Request $request)
    {
        DB::beginTransaction();
        try {

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,NULL,id,deleted_at,NULL',
                'password' => 'required|string|min:8|confirmed',
                'password_confirmation' => 'required',
                'country_code' => 'required',
                'phone' => 'required|min:9|unique:users,phone,NULL,id,deleted_at,NULL',
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->messages()->first(), 422);
            }

            $user = $this->createAccount($request);
            $token = $user->createToken('auth_token')->plainTextToken;
            $user->tokens()->update([
                'role_type' => $user->getRoleNames()->first()
            ]);

            DB::commit();
            return [
                'access_token' =>  $token,
                'permissions'  =>  $user->getPermissionNames(),
                'success' => true
            ];
        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    Public function backendForgotPassword(Request $request)
    {
        try {

            $validator = Validator::make($request->all(),[
                'email' => 'required|email|exists:users,email,deleted_at,NULL|string',
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->messages()->first(), 422);
            }

            $user = $this->getActiveUserByEmail($request->email);
            if ($user) {
                if (!$user->hasRole(RoleEnum::CONSUMER)) {
                    $token = rand(11111, 99999);
                    DB::table('password_resets')->insert([
                        'email' => $request->email,
                        'token' => $token,
                        'created_at' => Carbon::now()
                    ]);

                    try {

                        Mail::to($request->email)->queue(new ForgotPassword($token));

                    } catch (Exception $e) {

                        throw new Exception($e->getMessage(), $e->getCode());
                    }

                    return [
                        'message' => "We have e-mailed verification code in registered mail!",
                        'success' => true
                    ];
                }

                throw new Exception("Password reset is not available here for consumers.", 400);
            }

            throw new Exception("Provided email is not active.", 400);

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function forgotPassword(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email,deleted_at,NULL|string',
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->messages()->first(), 422);
            }

            $token = rand(11111, 99999);
            DB::table('password_resets')->insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => Carbon::now()
            ]);

            Mail::to($request->email)->send(new ForgotPassword($token));
            return [
                'message' => __('auth.email_verification_sent'),
                'success' => true
            ];

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function verifyToken(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'token' => 'required',
                'email' => 'required|email|max:255|exists:users,email,deleted_at,NULL|string',
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->messages()->first(), 422);
            }

            $user = DB::table('password_resets')
                ->where('token', $request->token)
                ->where('email', $request->email)
                ->where('created_at', '>', Carbon::now()->subHours(1))
                ->first();

            if (!$user) {
                throw new Exception(__('auth.invalid_email_token'), 400);
            }

            return [
                'message' => __('auth.token_verified'),
                'success' => true
            ];
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function updatePassword(Request $request)
    {
        DB::beginTransaction();
        try {

            $validator = Validator::make($request->all(), [
                'token' => 'required',
                'email' => 'required|email|max:255|exists:users,email,deleted_at,NULL|string',
                'password' => 'required|min:8|confirmed',
                'password_confirmation' => 'required'
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->messages()->first(), 422);
            }

            $user = DB::table('password_resets')
                ->where('token', $request->token)
                ->where('email', $request->email)
                ->where('created_at', '>', Carbon::now()->subHours(1))
                ->first();

            if (!$user) {
                throw new Exception(__('auth.invalid_email_token'), 400);
            }

            User::where('email', $request->email)
                ->update(['password' => Hash::make($request->password)]);

            DB::table('password_resets')->where('email', $request->email)->delete();
            DB::commit();

            return [
                'message' => __('auth.password_changed'),
                'success' => true
            ];

        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function logout(Request $request)
    {
        try {

            $token = PersonalAccessToken::findToken($request->bearerToken());
            if (!$token) {
                throw new Exception(__('auth.invalid_access_token'), 400);
            }

            $token->delete();
            return [
                'message' => __('auth.logged_out'),
                'success' => true
            ];
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}
