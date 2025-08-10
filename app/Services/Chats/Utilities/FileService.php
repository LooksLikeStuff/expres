<?php

namespace App\Services\Chats\Utilities;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class FileService
{
    /**
     * Сохраняет загруженный файл в указанную папку с рандомным именем
     *
     * @param UploadedFile $file
     * @param string $path
     * @return string
     */
    public function storeFileFromRequest(UploadedFile $file, string $path = ''): string
    {
        $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();

        $file->storeAs($path, $filename, 'public');

        return $path . '/' . $filename;
    }
}
