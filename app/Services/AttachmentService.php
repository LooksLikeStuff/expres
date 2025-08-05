<?php

namespace App\Services;

use App\Models\Chats\Message;

class AttachmentService
{
    public function saveMessageAttachments(Message $message, array $attachments)
    {
        foreach ($attachments as $attachment) {
            $path = $attachment->store('attachments', 'public');

            $message->attachments()->create([
                'path' => $path,
                'original_name' => $attachment->getClientOriginalName(),
                'filesize' => $attachment->getSize(),
                'mime_type' => $attachment->getMimeType(),
            ]);
        }
    }
}
