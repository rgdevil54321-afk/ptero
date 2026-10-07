$(document).ready(function() {
    $('.form-autocomplete-stop').on('click', function () {
        $(this).removeAttr('readonly').blur().focus();
    });
});
