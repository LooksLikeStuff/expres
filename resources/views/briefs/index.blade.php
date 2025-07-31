@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h1>Список брифов</h1>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(count($briefs) > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Название</th>
                                        <th>Описание</th>
                                        <th>Статус</th>
                                        <th>Дата создания</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($briefs as $brief)
                                        <tr>
                                            <td>{{ $brief->id }}</td>
                                            <td>{{ $brief->title }}</td>
                                            <td>{{ \Illuminate\Support\Str::limit($brief->description, 50) }}</td>
                                            <td>
                                                @if($brief->status == 'Активный')
                                                    <span class="badge bg-success">Активный</span>
                                                @elseif($brief->status == 'Завершенный')
                                                    <span class="badge bg-secondary">Завершенный</span>
                                                @else
                                                    <span class="badge bg-info">{{ $brief->status }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $brief->created_at->format('d.m.Y H:i') }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    @if(isset($brief->id))
                                                        <a href="{{ route('brief.show', $brief->id) }}" class="btn btn-sm btn-info">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('brief.edit', $brief->id) }}" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p>Брифы не найдены</p>
                            <a href="{{ route('brief.create') }}" class="btn btn-primary">Создать бриф</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
