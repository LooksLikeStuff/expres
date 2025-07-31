<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Award;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AwardController extends Controller
{
    /**
     * Отображает список всех наград
     */
    public function index()
    {
        $awards = Award::orderBy('category')->orderBy('name')->get();
        return view('admin.awards.index', compact('awards'));
    }

    /**
     * Отображает форму создания новой награды
     */
    public function create()
    {
        return view('admin.awards.create');
    }

    /**
     * Сохраняет новую награду
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'required|string', // SVG код или путь
            'category' => 'nullable|string|max:100',
        ]);

        $award = Award::create($request->all());

        return redirect()->route('admin.awards.index')
            ->with('success', 'Награда успешно создана.');
    }

    /**
     * Отображает форму редактирования награды
     */
    public function edit(Award $award)
    {
        return view('admin.awards.edit', compact('award'));
    }

    /**
     * Обновляет награду
     */
    public function update(Request $request, Award $award)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'required|string',
            'category' => 'nullable|string|max:100',
        ]);

        $award->update($request->all());

        return redirect()->route('admin.awards.index')
            ->with('success', 'Награда успешно обновлена.');
    }

    /**
     * Удаляет награду
     */
    public function destroy(Award $award)
    {
        $award->delete();

        return redirect()->route('admin.awards.index')
            ->with('success', 'Награда успешно удалена.');
    }

    /**
     * Отображает форму выдачи награды пользователю
     */
    public function showAwardForm(User $user)
    {
        // Проверяем, что пользователь не имеет статус 'user'
        if ($user->status === 'user') {
            return redirect()->back()
                ->with('error', 'Нельзя выдавать награды пользователям со статусом "user".');
        }

        $awards = Award::orderBy('category')->orderBy('name')->get();
        return view('admin.awards.award-user', compact('user', 'awards'));
    }

    /**
     * Выдает награду пользователю
     */
    public function awardUser(Request $request, User $user)
    {
        $request->validate([
            'award_id' => 'required|exists:awards,id',
            'comment' => 'nullable|string|max:500',
        ]);

        // Проверяем, что пользователь не имеет статус 'user'
        if ($user->status === 'user') {
            return redirect()->back()
                ->with('error', 'Нельзя выдавать награды пользователям со статусом "user".');
        }

        try {
            $user->awards()->attach($request->award_id, [
                'awarded_by' => Auth::id(),
                'awarded_at' => now(),
                'comment' => $request->comment,
            ]);

            return redirect()->route('profile.view', $user->id)
                ->with('success', 'Награда успешно выдана пользователю.');
                
        } catch (\Exception $e) {
            Log::error('Ошибка при выдаче награды: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'award_id' => $request->award_id,
                'admin_id' => Auth::id(),
            ]);
            
            return redirect()->back()
                ->with('error', 'Произошла ошибка при выдаче награды.');
        }
    }

    /**
     * Отзывает награду у пользователя
     */
    public function revokeAward(User $user, Award $award)
    {
        try {
            $user->awards()->detach($award->id);
            
            return redirect()->back()
                ->with('success', 'Награда успешно отозвана у пользователя.');
                
        } catch (\Exception $e) {
            Log::error('Ошибка при отзыве награды: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'award_id' => $award->id,
                'admin_id' => Auth::id(),
            ]);
            
            return redirect()->back()
                ->with('error', 'Произошла ошибка при отзыве награды.');
        }
    }
}
