
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Управление наградами</h1>
        <a href="{{ route('admin.awards.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Создать награду
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th style="width: 80px">Иконка</th>
                            <th>Название</th>
                            <th>Категория</th>
                            <th>Описание</th>
                            <th style="width: 150px">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($awards as $award)
                            <tr>
                                <td class="text-center">
                                    <div class="award-icon-preview">
                                        {!! $award->icon !!}
                                    </div>
                                </td>
                                <td>{{ $award->name }}</td>
                                <td>{{ $award->category }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($award->description, 100) }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.awards.edit', $award->id) }}" class="btn btn-info">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.awards.destroy', $award->id) }}" method="POST" onsubmit="return confirm('Вы уверены, что хотите удалить эту награду?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Награды отсутствуют</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.award-icon-preview {
    width: 40px;
    height: 40px;
    margin: 0 auto;
}

.award-icon-preview svg {
    width: 100%;
    height: 100%;
}

.btn-group-sm {
    display: flex;
    gap: 5px;
}
</style>

