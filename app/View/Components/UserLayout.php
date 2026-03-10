<?php

namespace App\View\Components;

use App\Services\SM2Service;
use Illuminate\View\Component;
use Illuminate\View\View;

class UserLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        $dueCardsCount = 0;

        $user = auth()->user();
        if ($user && $user->isStudyUser()) {
            $sm2 = app(SM2Service::class);
            $dueCardsCount = $sm2->getDueCardsCount($user->id);
        }

        return view('layouts.user', [
            'dueCardsCount' => $dueCardsCount,
        ]);
    }
}
