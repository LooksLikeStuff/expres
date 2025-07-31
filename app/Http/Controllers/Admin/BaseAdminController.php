<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class BaseAdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('status:admin');
    }

    /**
     * Get authenticated admin user.
     *
     * @return \App\Models\User
     */
    protected function getAdminUser()
    {
        return Auth::user();
    }
}
