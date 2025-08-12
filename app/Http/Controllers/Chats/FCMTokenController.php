<?php

namespace App\Http\Controllers\Chats;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chats\FCMTokenCreateRequest;
use App\Models\UserFCMToken;

class FCMTokenController extends Controller
{
    public function store(FCMTokenCreateRequest $request)
    {
        UserFCMToken::updateOrCreate(
            ['user_id' => auth()->id(), 'token' => $request->validated('token')],
            ['updated_at' => now()]
        );

        return response()->json(['status' => 'OK']);
    }
}
