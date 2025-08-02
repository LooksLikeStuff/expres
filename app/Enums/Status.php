<?php

namespace App\Enums;

enum Status: string
{
//admin - полный доступ ко всем чатам
//coordinator - доступ к чатам в рамках проектов
//partner - доступ к назначенным чатам
//architect, designer, visualizer - участие в проектных чатах
//client - о

    case ADMIN = 'admin';
    case COORDINATOR = 'coordinator';
    case PARTNER = 'partner';
    case ARCHITECT = 'architect';
    case DESIGNER = 'designer';
    case VISUALIZER = 'visualizer';
    case CLIENT = 'client';
    case USER = 'user';
}
