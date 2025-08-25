$(document).ready(function () {
    $('.create__brief-btn').click(function () {
        const type = $(this).attr('data-type');

        $('#createBriefType').val(type);

        $('#createBriefForm').submit();
    });
})
