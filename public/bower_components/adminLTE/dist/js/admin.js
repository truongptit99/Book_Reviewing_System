$('.btn-delete').click(function (ev) {
    ev.preventDefault();
    var _href = $(this).attr('href');

    $('form#form-delete').attr('action', _href);
    if (confirm('Are you sure to want to delete?')) {
        $('form#form-delete').submit();
    }
});

$('#image-input').on('change', function () {
    var fileName = $(this).val();
    $(this).next('.custom-file-label').html(fileName);
});