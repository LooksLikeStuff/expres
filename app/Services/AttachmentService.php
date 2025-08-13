<?php

namespace App\Services;

use App\Models\Chats\Message;
use Illuminate\Support\Facades\Storage;

class AttachmentService
{
    public function saveMessageAttachments(Message $message, array $attachments)
    {
        foreach ($attachments as $attachment) {
           $path = Storage::disk('yandex')->putFile('chat-attachments', $attachment);

            $message->attachments()->create([
                'path' => $path,
                'original_name' => $attachment->getClientOriginalName(),
                'filesize' => $attachment->getSize(),
                'mime_type' => $attachment->getMimeType(),
            ]);
        }
    }
}
