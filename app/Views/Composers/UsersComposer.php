<?php

namespace App\Views\Composers;

use App\Services\UserService;
use Illuminate\View\View;

class UsersComposer
{
    public function __construct(
        private readonly UserService $userService,
    )
    {
    }

    public function compose(View $view)
    {
        $users = $this->userService->getAll();

        return $view->with(['users' => $users]);
    }
}
