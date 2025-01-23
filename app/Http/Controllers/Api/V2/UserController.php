<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\InterestedList;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use DateTime;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:4',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid email or password'], 401);
        }

        $token = $user->createToken('YourAppName')->accessToken;

        $user->api_token = $token;
        $user->save();

        return response()->json([
            'token' => $token,
            'user_id' => $user->id,
        ]);
    }

    public function getProfile(Request $request)
    {
        $user = $request->user();

        return response()->json($user);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:4|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = $request->password;
        $user->save();

        return response()->json(['message' => 'User registered successfully'], 201);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $user->token()->revoke();

            return response()->json(['message' => 'Successfully logged out'], 200);
        }

        return response()->json(['error' => 'User not authenticated'], 401);
    }

    public function getUsers()
    {
        $users = User::all();

        if ($users->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No users found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $users
        ]);
    }

    public function getUser($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User with ID ' . $id . ' not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $user
        ]);
    }

    public function updateUser(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . $id,
                'username' => 'nullable|string|max:255',
                'profile_image' => 'nullable|string|max:255',
                'bio' => 'nullable|string',
                'website' => 'nullable|string|max:255',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User with ID ' . $id . ' not found'
            ], 404);
        }

        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];
        $user->username = $validatedData['username'] ?? $user->username;
        $user->profile_image = $validatedData['profile_image'] ?? $user->profile_image;
        $user->bio = $validatedData['bio'] ?? $user->bio;
        $user->website = $validatedData['website'] ?? $user->website;
        $user->save();

        return response()->json([
            'status' => 'success',
            'data' => $user
        ]);
    }

    public function addUserById(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|string|max:255|unique:users,username',
            'profile_image' => 'nullable|string|max:255',
            'password' => 'required|min:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'username' => $request->input('username'),
            'password' => $request->input('password'),
            'profile_image' => $request->input('profile_image'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'data' => $user,
        ], 201);
    }

    public function search(Request $request)
    {
        $query = User::query();

        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        if ($request->has('email')) {
            $query->where('email', 'like', '%' . $request->input('email') . '%');
        }

        if ($request->has('username')) {
            $query->where('username', 'like', '%' . $request->input('username') . '%');
        }

        $sortBy = $request->input('sort_by', 'name');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $users = $query->paginate(10);

        return response()->json($users);
    }

    public function searchProfile(Request $request)
    {
        $query = User::with([
            'comments:id,user_id,comment_text',
            'posts:id,user_id,title',
            'posts' => function ($query) {
                $query->withCount(['comments as comments_count', 'postLikes as likes_count']);
            },
            'userInterested:id,user_id,title',
            'socialAccounts:id,user_id,provider_id,provider'
        ])->where('username', $request->username);

        $sortBy = $request->input('sort_by', 'name');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $users = $query->paginate(10);

        return response()->json($users);
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function interestedList(Request $request)
    {
        $user = $request->user();

        $dobObject = new DateTime($user->date_of_birth);
        $nowObject = new DateTime();

        $diff = $dobObject->diff($nowObject);

        $cuisines = InterestedList::where('type', 1)->get();

        if ($diff->y > 18) {
            $food_and_drink = InterestedList::where('type', 2)
                ->whereIn('category_id', [1, 3])
                ->get();
        } else {
            $food_and_drink = InterestedList::where('type', 2)
                ->whereIn('category_id', [2, 3])
                ->get();
        }

        $diet = InterestedList::where('type', 3)->get();

        return response()->json([
            'status' => 200,
            'message' => __('api.suc_interestedlist'),
            'data' => [
                'cuisines' => $cuisines,
                'food_and_drink' => $food_and_drink,
                'diet' => $diet
            ]
        ]);
    }

}
