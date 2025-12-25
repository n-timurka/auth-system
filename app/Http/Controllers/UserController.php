<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use App\Models\User;
use App\Http\Resources\UserResource;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * List of users
     * 
     * @return Response
     */
    public function index(): Response
    {
        $users = User::withTrashed()->get();

        return Inertia::render('Users', [
            'users' => UserResource::collection($users)
        ]);
    }

    /**
     * Summary of destroy
     * 
     * @param User $user
     * @return RedirectResponse
     */
    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return back();
    }

    /**
     * Summary of restore
     * 
     * @param $id
     * @return RedirectResponse
     */
    public function restore($id): RedirectResponse
    {
        $user = User::withTrashed()->findOrFail($id);

        $user->restore();

        return back();
    }
}
