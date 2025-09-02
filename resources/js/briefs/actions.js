// Действия для работы с брифами
export function confirmDelete(brifId) {
    if (confirm("Вы действительно хотите удалить этот бриф? Это действие нельзя будет отменить.")) {
        document.getElementById('delete-form-' + brifId).submit();
    }
}

// Делаем функцию доступной глобально для HTML onclick
window.confirmDelete = confirmDelete;
