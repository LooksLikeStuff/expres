<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

class SettingsController extends BaseAdminController
{
    /**
     * Отобразить страницу настроек
     */
    public function index()
    {
        $title_site = "Настройки системы | Личный кабинет Экспресс-дизайн";
        $user = $this->getAdminUser();
        
        // Здесь код для получения настроек из базы данных или конфигурации
        
        return view('admin.settings', compact('title_site', 'user'));
    }
    
    /**
     * Обновить настройки системы
     */
    public function update(Request $request)
    {
        // Валидация входящих данных
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'app_url' => 'required|url',
            'mail_host' => 'required|string|max:255',
            'mail_port' => 'required|integer',
            'mail_username' => 'required|string|max:255',
            'mail_password' => 'required|string',
            'mail_encryption' => 'required|string|in:tls,ssl,null',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string|max:255',
            // Другие настройки
        ]);
        
        // Обновление настроек
        // Здесь код для обновления настроек в базе данных или в конфигурации
        
        return redirect()->route('admin.settings.index')
            ->with('success', 'Настройки успешно обновлены.');
    }
}
