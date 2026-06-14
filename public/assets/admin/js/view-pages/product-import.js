"use strict";
let count = 0;
let countRow = 0;
let element = 0;
function show_min_max(data) {
    $('#min_max1_' + data).removeAttr("readonly");
    $('#min_max2_' + data).removeAttr("readonly");
    $('#min_max1_' + data).attr("required", "true");
    $('#min_max2_' + data).attr("required", "true");
    $('#min_max1_' + data).attr("min", "1");
    $('#min_max2_' + data).attr("min", "2");
}

function hide_min_max(data) {
    $('#min_max1_' + data).val(null).trigger('change');
    $('#min_max2_' + data).val(null).trigger('change');
    $('#min_max1_' + data).attr("readonly", "true");
    $('#min_max2_' + data).attr("readonly", "true");
    $('#min_max1_' + data).removeAttr("required");
    $('#min_max2_' + data).removeAttr("required");
    $('#min_max1_' + data).removeAttr("min");
    $('#min_max2_' + data).removeAttr("min");
}

$(document).on('change', '.show_min_max', function () {
    let data = $(this).data('count');
    show_min_max(data);
});

$(document).on('change', '.hide_min_max', function () {
    let data = $(this).data('count');
    hide_min_max(data);
});

$(document).on('click', '.add_new_row_button', function () {
    let data = $(this).data('count');
    add_new_row_button(data);
});

$(document).on('keyup', '.new_option_name', function () {
    let data = $(this).data('count');
    let value = $(this).val();
    new_option_name(value, data);
});

$('#reset_btn').click(function(){
    $('#bulk__import').val(null);
})







function new_option_name(value, data) {
    $("#new_option_name_" + data).empty();
    $("#new_option_name_" + data).text(value)
    console.log(value);
}

function removeOption(e) {
    element = $(e);
    element.parents('.view_new_option').remove();
}

$(document).on('click', '.delete_input_button', function () {
    let e = $(this);
    removeOption(e);
});

function deleteRow(e) {
    element = $(e);
    element.parents('.add_new_view_row_class').remove();
}

$(document).on('click', '.deleteRow', function () {
    let e = $(this);
    deleteRow(e);
});
