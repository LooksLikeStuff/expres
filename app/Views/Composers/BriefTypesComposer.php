<?php

namespace App\Views\Composers;

use App\Enums\Briefs\BriefType;
use Illuminate\View\View;

class BriefTypesComposer
{
    public function compose(View $view)
    {
        $types = BriefType::cases();

        return $view->with('briefTypes', $types);
    }
}
