<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Redirect users based on their status.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        $user = Auth::user();

        switch ($user->status) {
            case 'user':
            case 'client':
                // Клиенты перенаправляются на страницу со своими сделками
                return redirect()->route('brifs.index');
            case 'coordinator':
            case 'architect':
            case 'designer':
            case 'visualizer':
                return redirect()->route('deal.cardinator');
            case 'partner':
                // Партнеры имеют доступ к estimate, но и должны видеть сделки
                return redirect()->route('deal.cardinator');
            case 'admin':
                return redirect()->route('admin');
            case 'support':
                return redirect()->route('chats.index');
            default:
                // Optionally, handle unexpected statuses
                Auth::logout();
                return redirect()->route('home')->withErrors(['status' => 'Invalid user status.']);
        }
    }
}
