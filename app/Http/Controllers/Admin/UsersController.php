<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\WebController;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UsersController extends WebController
{
    public function index()
    {
        return view('admin.user.index', [
            'title' => 'Users',
            'breadcrumb' => breadcrumb([
                'Users' => route('admin.user.index'),
            ]),
        ]);
    }

    public function listing()
    {
        $datatable_filter = datatable_filters();
        $offset = $datatable_filter['offset'];
        $search = $datatable_filter['search'];
        $return_data = array(
            'data' => [],
            'recordsTotal' => 0,
            'recordsFiltered' => 0
        );
        $main = User::where('type', 'user');
        $return_data['recordsTotal'] = $main->count();

        if (!empty($search)) {
            $main->where(function ($query) use ($search) {
                $query->AdminSearch($search);
            });
        }
        $return_data['recordsFiltered'] = $main->count();
        $all_data = $main->orderBy($datatable_filter['sort'], $datatable_filter['order'])
            ->offset($offset)
            ->limit($datatable_filter['limit'])
            ->get();

        if (!empty($all_data)) {
            foreach ($all_data as $key => $value) {
                $param = [
                    'id' => $value->id,
                    'url' => [
                        'status' => route('admin.user.status_update', $value->id),
                        'edit' => route('admin.user.edit', $value->id),
                        'delete' => route('admin.user.destroy', $value->id),
                        'view' => route('admin.user.show', $value->id),
                    ],
                    'checked' => ($value->status == 'active') ? 'checked' : ''
                ];

                $return_data['data'][] = array(
                    'id' => $offset + $key + 1,
                    'profile_image' => get_fancy_box_html($value['profile_image']),
                    'username' => $value->username ?: " - ",
                    'email' => $value->email,
                    'status' => $this->generate_switch($param),
                    'action' => $this->generate_actions_buttons($param),
                );
            }
        }
        return $return_data;
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
            return view('admin.user.edit', [
                'title' => $title,
                'data' => $data,
                'breadcrumb' => breadcrumb([
                    'User' => route('admin.user.index'),
                    'edit' => route('admin.user.edit', $data->id)
                ]),
            ]);
        }
        error_session('user not found');

        return redirect()->route('admin.user.index');
    }

    public function update(Request $request, $id)
    {
        $data = User::find($id);

        if ($data) {
             $request->validate([
                'email' => ['required', 'email', Rule::unique('users')->ignore($id)->whereNull('deleted_at')],
                'profile_image' => ['file', 'image'],
            ]);

            $profile_image = $data->getRawOriginal('profile_image');

            if ($request->hasFile('profile_image')) {

                $path = $request->file('profile_image')->store('uploads/users', 's3');

                if ($path) {

                    Storage::disk('s3')->setVisibility($path, 'public');

                    $profile_image = $path;
                }
            }

           $userdata = [
                'email' => $request->email,
                'profile_image' => $profile_image,
                'name' =>  $request->name,
                'username' =>  $request->username,
                'bio' =>  $request->bio,
                'website' =>  $request->website,
           ];

            $data->update($userdata);
            success_session('user updated successfully');
        } else {
            error_session('user not found');
        }

        return redirect()->route('admin.user.index');
    }

}
