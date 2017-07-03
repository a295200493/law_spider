<?php

namespace App\Http\Controllers\Api\V1;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Jobs\SendRegisterEmail;
use App\Transformers\UserTransformer;
use App\Repositories\Contracts\UserRepository;

class UserController extends BaseController
{
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }
    public function index()
    {
        $users = $this->userRepository->paginate();

        return $this->response->paginator($users, new UserTransformer());
    }
    public function editPassword(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'old_password' => 'required',
            'password' => 'required|confirmed|different:old_password',
            'password_confirmation' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator);
        }

        $user = $this->user();

        $auth = \Auth::once([
            'email' => $user->email,
            'password' => $request->get('old_password'),
        ]);

        if (! $auth) {
            return $this->response->errorUnauthorized();
        }

        $password = app('hash')->make($request->get('password'));
        $this->userRepository->update($user->id, ['password' => $password]);

        return $this->response->noContent();
    }
    public function show($id)
    {
        $user = $this->userRepository->find($id);

        if (! $user) {
            return $this->response->errorNotFound();
        }

        return $this->response->item($user, new UserTransformer());
    }
    public function userShow()
    {
        return $this->response->item($this->user(), new UserTransformer());
    }
    public function patch(Request $request)
    {
        $validator = \Validator::make($request->input(), [
            'name' => 'string|max:50',
            'avatar' => 'url',
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator);
        }

        $user = $this->user();
        $attributes = array_filter($request->only('name', 'avatar'));

        if ($attributes) {
            $user = $this->userRepository->update($user->id, $attributes);
        }

        return $this->response->item($user, new UserTransformer());
    }
    public function store(Request $request)
    {
        $validator = \Validator::make($request->input(), [
            'email' => 'required|email|unique:users',
            'name' => 'required|string',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator);
        }

        $email = $request->get('email');
        $password = $request->get('password');

        $attributes = [
            'email' => $email,
            'name' => $request->get('name'),
            'password' => app('hash')->make($password),
        ];
        $user = $this->userRepository->create($attributes);

        dispatch(new SendRegisterEmail($user));

        // 201 with location
        $location = dingo_route('v1', 'users.show', $user->id);

        $result = [
            'token' => \Auth::fromUser($user),
            'expired_at' => Carbon::now()->addMinutes(config('jwt.ttl'))->toDateTimeString(),
            'refresh_expired_at' => Carbon::now()->addMinutes(config('jwt.refresh_ttl'))->toDateTimeString(),
        ];

        return $this->response->item($user, new UserTransformer())
            ->header('Location', $location)
            ->setMeta($result)
            ->setStatusCode(201);
    }
}
