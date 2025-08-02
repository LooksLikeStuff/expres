<?php

namespace App\Enums;

enum MessageType: string
{
    case TEXT = 'text';
    case FILE = 'file';
    case IMAGE = 'image';
}
