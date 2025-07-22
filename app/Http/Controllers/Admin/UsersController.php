<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Http\Controllers\WebController;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use DataTables;

class UsersController extends WebController
{
    public function index()
    {
        return view('admin.user.index', [
            'title' => 'Members',
            'breadcrumb' => breadcrumb([
                'Members' => route('admin.post.index'),
            ]),
        ]);
    }

    public function listing(Request $request)
    {
        $query = User::select('users.*')
            ->where('users.type', 'user')
            ->orderBy('users.id', 'DESC');

        // Apply filters if provided
        $datatable_filter = datatable_filters();
        $offset = $datatable_filter['offset'];
        $search = $datatable_filter['search'];

        if (!empty($search)) {
            $query->where(function ($query) use ($search) {
                $query->AdminSearch($search);
            });
        }

        $return_data['recordsTotal'] = $query->count();

        // Apply more filters if provided
        if ($request->has('verified') && $request->verified === '0') {
            $query->where('posts.verified', 0);
        }

        // Paginate and get the data
        $all_data = $query->offset($offset)
            ->limit($datatable_filter['limit'])
            ->get();

        $query->count();

        if (!empty($all_data)) {
            foreach ($all_data as $value) {
                $param = [
                    'id' => $value->id,
                    'url' => [
                        'status' => route('admin.user.status_update', $value->id),
                        'edit' => route('admin.user.edit', $value->id),
                        'delete' => route('admin.user.destroy', $value->id),
                        'view' => route('admin.user.show', $value->id),
                    ],
                ];

                $statusOptions = User::getStatusOptions();
                $statusDropdown = "<select class='form-control status-dropdown' data-id='{$value->id}'>";
                foreach ($statusOptions as $statusValue => $statusLabel) {
                    $selected = $value->status == $statusValue ? 'selected' : '';
                    $statusDropdown .= "<option value='{$statusValue}' {$selected}>{$statusLabel}</option>";
                }
                $statusDropdown .= "</select>";

                $membershipOptions = User::getMembershipOptions();
                $membershipDropdown = "<select class='form-control membership-dropdown' data-id='{$value->id}'>";
                foreach ($membershipOptions as $membershipValue => $membershipLabel) {
                    $selected = $value->membership_level == $membershipValue ? 'selected' : '';
                    $membershipDropdown .= "<option value='{$membershipValue}' {$selected}>{$membershipLabel}</option>";
                }
                $membershipDropdown .= "</select>";

                $return_data['data'][] = array(
                    'user_id' => $value->id,
                    'id' => $value->id,
                    'membership_level' => $membershipDropdown,
                    'username' => $value->username ?: " - ",
                    'email' => $value->email ?: " - ",
                    'mobile' => $value->mobile ?: " - ",
                    'verified' => $value->verified ? 1 : 0,
                    'created_at' => $value->created_at ? Carbon::parse($value->created_at)->format('d-m-Y') : ' - ',
                    'status' => $statusDropdown,
                    'action' => $this->generate_actions_buttons($param),
                );
            }
        }

        return Datatables::of($return_data['data'])
            ->addIndexColumn()
            ->editColumn('username', function ($row) {
                return "<span title='$row[username]'>{$row['username']}</span>";
            })
            ->editColumn('email', function ($row) {
                return $row['email'];
            })
            ->editColumn('mobile', function ($row) {
                return $row['mobile'];
            })
            ->editColumn('created_at', function ($row) {
                return "<span title='$row[created_at]'>{$row['created_at']}</span>";
            })
            ->editColumn('status', function ($row) {
                return $row['status'];
            })
            ->editColumn('membership_level', function ($row) {
                return $row['membership_level'];
            })
            ->addColumn('verified', function ($row) {
                $checked = $row['verified'] ? 'checked' : '';
                return "<label class='switch'>
                <input type='checkbox' class='toggle-user-verified' data-id='{$row['user_id']}' {$checked}>
                <span class='slider slider-secondary round'></span>
            </label>";
            })
            ->addColumn('action', function ($row) {
                $param = [
                    'id' => $row['id'],
                    'url' => [
                        'delete' => route('admin.user.destroy', $row['user_id']),
                        'edit' => route('admin.user.edit', $row['user_id']),
                        'view' => route('admin.user.show', $row['user_id']),
                        'status' => route('admin.user.status_update', $row['user_id']),
                    ]
                ];
                return $this->generate_actions_buttons($param);
            })
            ->rawColumns(['username', 'created_at', 'status', 'membership_level', 'verified', 'action'])
            ->make(true);
    }

    public function destroy($id)
    {
        $data = User::where('id', $id)->first();
        if ($data) {
            $data->delete();
            success_session('User deleted successfully');
        } else {
            error_session('User not found');
        }
        return redirect()->route('admin.user.index');
    }

    public function status_update($id = 0)
    {
        $data = ['status' => 0, 'message' => 'User not found'];
        $find = User::find($id);
        if ($find) {
            $find->update(['status' => ($find->status == "inactive") ? "active" : "inactive"]);
            $data['status'] = 1;
            $data['message'] = 'User status updated';
        }
        return $data;
    }

    public function show($id)
    {
        $data = User::where(['type' => 'user', 'id' => $id])->first();
        if ($data) {
            return view('admin.user.view', [
                'title' => 'View user',
                'data' => $data,
                'breadcrumb' => breadcrumb([
                    'user' => route('admin.user.index'),
                    'view' => route('admin.user.show', $id)
                ]),
            ]);
        }
        error_session('user not found');
        return redirect()->route('admin.user.index');
    }

    public function edit($id)
    {
        $data = User::find($id);
        if ($data) {
            $title = "Update user";
            $statusOptions = User::getStatusOptions();
            $membershipOptions = User::getMembershipOptions();
            return view('admin.user.edit', [
                'title' => $title,
                'data' => $data,
                'statusOptions' => $statusOptions,
                'membershipOptions' => $membershipOptions,
                'breadcrumb' => breadcrumb([
                    'User' => route('admin.user.index'),
                    'edit' => route('admin.user.edit', $data->id)
                ]),
            ]);
        }
        error_session('user not found');

        return redirect()->route('admin.user.index');
    }

    public function create()
    {
        $title = "Create user";
        $statusOptions = User::getStatusOptions();
        $membershipOptions = User::getMembershipOptions();
        return view('admin.user.create', [
            'title' => $title,
            'statusOptions' => $statusOptions,
            'membershipOptions' => $membershipOptions,
            'breadcrumb' => breadcrumb([
                'User' => route('admin.user.index'),
                'create' => route('admin.user.create')
            ]),
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = User::find($id);

        if ($data) {
            $request->validate([
                'email' => ['required', 'email', Rule::unique('users')->ignore($id)->whereNull('deleted_at')],
                'profile_image' => ['file', 'image'],
                'membership_level' => ['required', Rule::in(array_keys(User::getMembershipOptions()))],
                'status' => ['required', Rule::in(array_keys(User::getStatusOptions()))],
            ]);

            $profile_image = $data->getRawOriginal('profile_image');
            if ($request->hasFile('profile_image')) {
                $path = $request->file('profile_image')->store('uploads/users', 's3');
                if ($path) {
                    //Storage::disk('s3')->setVisibility($path, 'public');
                    $profile_image = $path;
                }
            }

            $userdata = [
                'email' => $request->email,
                'profile_image' => $profile_image,
                'name' => $request->name,
                'username' => $request->username,
                'bio' => $request->bio,
                'website' => $request->website,
                'verified' => $request->has('verified') ? 1 : 0,
                'membership_level' => $request->membership_level,
                'status' => $request->status,
            ];

            $data->update($userdata);
            success_session('user updated successfully');
        } else {
            error_session('user not found');
        }

        return redirect()->route('admin.user.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', Rule::unique('users')->whereNull('deleted_at')],
            'profile_image' => ['file', 'image'],
            'membership_level' => ['required', Rule::in(array_keys(User::getMembershipOptions()))],
            'status' => ['required', Rule::in(array_keys(User::getStatusOptions()))],
            'password' => ['required', 'string', 'min:8'], // Add validation rule for password
        ]);

        $profile_image = null;
        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('uploads/users', 's3');
            if ($path) {
                //Storage::disk('s3')->setVisibility($path, 'public');
                $profile_image = $path;
            }
        }

        $user = new User();
        $user->email = $request->email;
        $user->profile_image = $profile_image;
        $user->name = $request->name;
        $user->username = $request->username;
        $user->bio = $request->bio;
        $user->website = $request->website;
        $user->verified = $request->has('verified') ? 1 : 0;
        $user->membership_level = $request->membership_level;
        $user->status = $request->status;
        $user->type = 'user';
        $user->password = $request->password;

        $user->save();

        success_session('user created successfully');

        return redirect()->route('admin.user.index');
    }

    public function updateUserVerified(Request $request)
    {
        $user = User::find($request->id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found!']);
        }

        $user->verified = !$user->verified;

        $user->save();

        return response()->json(['success' => true, 'message' => 'Verified updated!', 'verified' => $user->verified]);
    }

    public function updateUserStatus(Request $request)
    {
        $user = User::find($request->id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found!']);
        }

        $userdata = ['status' => $request->status];
        $user->update($userdata);

        return response()->json(['success' => true, 'message' => 'Status updated!', 'status' => $user->status]);
    }

    public function updateUserMembershipLevel(Request $request)
    {
        $user = User::find($request->id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found!']);
        }

        $userdata = ['membership_level' => $request->membership_level];
        $user->update($userdata);

        return response()->json(['success' => true, 'message' => 'Membership level updated!', 'membership_level' => $user->membership_level]);
    }

    public function duplicate(Request $request)
    {
        $originalUser = User::find($request->id);
        $newUser = $originalUser->replicate();
        $newUser->save();

        return response()->json([
            'message' => 'New user created successfully with ID ' . $newUser->id,
            'redirect_url' => route('admin.user.index')
        ]);
    }

}
