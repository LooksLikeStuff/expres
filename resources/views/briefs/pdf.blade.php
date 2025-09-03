<!DOCTYPE html>
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $brief->isCommon() ? 'Общий' : 'Коммерческий' }} бриф #{{ $brief->id }}</title>
    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            src: url('{{ storage_path('fonts/DejaVuSans.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            line-height: 1.6;
            color: #333;
        }
        
        h1 {
            color: #2c3e50;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        h2 {
            color: #3498db;
            margin-top: 30px;
        }
        
        h3 {
            margin-top: 30px;
            font-size: 18px;
            color: #3498db;
        }
        
        h4 {
            margin-top: 20px;
            font-size: 16px;
            color: #34495e;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        table, th, td {
            border: 1px solid #ddd;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
        }
        
        th {
            background-color: #f2f2f2;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .logo img {
            max-width: 150px;
            height: auto;
        }
        
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
        
        ul {
            margin: 10px 0;
            padding-left: 20px;
        }
    </style>
</head>
<body>
    {{-- Логотип --}}
    @include('briefs.components.pdf.logo')
    
    {{-- Заголовок --}}
    @include('briefs.components.pdf.header', ['brief' => $brief])
    
    {{-- Основная информация --}}
    @include('briefs.components.pdf.basic-info', ['brief' => $brief, 'user' => $user])
    
    {{-- Контент в зависимости от типа брифа --}}
    @if($brief->isCommon())
        @include('briefs.components.pdf.common-content', [
            'brief' => $brief,
            'rooms' => $rooms ?? collect(),
            'questions' => $questions ?? [],
            'roomAnswers' => $roomAnswers ?? collect(),
            'pageTitles' => $pageTitles ?? []
        ])
    @elseif($brief->isCommercial())
        @include('briefs.components.pdf.commercial-content', [
            'brief' => $brief,
            'zones' => $zones ?? [],
            'questions' => $questions ?? [],
            'zoneAnswers' => $zoneAnswers ?? collect()
        ])
    @endif
    
    {{-- Документы --}}
    @include('briefs.components.pdf.documents', ['brief' => $brief])
    
    {{-- Подвал с датами --}}
    @include('briefs.components.pdf.footer', ['brief' => $brief])
</body>
</html>
