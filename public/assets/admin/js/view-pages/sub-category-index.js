"use strict";

$(document).ready(function() {

    // 1. Prioridad cambio vía AJAX
    $(document).on('change', '.priority-select', function() {
        var form = $(this).closest('form');
        var url = form.attr('action');
        var formData = form.serialize();

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (typeof toastr !== 'undefined') {
                    toastr.success(response.message || 'Priority updated');
                }
            },
            error: function(err) {
                console.error(err);
            }
        });
    });

    // 2. Formulario de creación de subcategoría mediante AJAX (sin recargar página)
    $('.card-body form[action*="category"]').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var formData = new FormData(this);

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $('#loading').show();
            },
            success: function(response) {
                if (typeof toastr !== 'undefined') {
                    toastr.success(response.message || 'Sub category added successfully');
                }
                form[0].reset();
                $('#exampleFormControlSelect1').val(null).trigger('change');
                // Recargar tabla mediante AJAX sin recargar ventana
                refreshTable();
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, val) {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(val[0]);
                        }
                    });
                } else if (typeof toastr !== 'undefined') {
                    toastr.error('Error saving sub category');
                }
            },
            complete: function() {
                $('#loading').hide();
            }
        });
    });

    // 3. Formulario de edición Offcanvas mediante AJAX
    $(document).on('submit', '#data-view form', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var formData = new FormData(this);

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $('#loading').show();
            },
            success: function(response) {
                if (typeof toastr !== 'undefined') {
                    toastr.success(response.message || 'Sub category updated successfully');
                }
                // Cerrar Offcanvas
                $('.custom-offcanvas').removeClass('open');
                $('#offcanvasOverlay').removeClass('show');
                $('#content-disable').removeClass('disabled');
                // Recargar tabla mediante AJAX
                refreshTable();
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, val) {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(val[0]);
                        }
                    });
                } else if (typeof toastr !== 'undefined') {
                    toastr.error('Error updating sub category');
                }
            },
            complete: function() {
                $('#loading').hide();
            }
        });
    });

    // 4. Búsqueda en vivo (Live Search / AJAX)
    var searchTimer;
    $('#datatableSearch').on('keyup input', function() {
        clearTimeout(searchTimer);
        var query = $(this).val();
        searchTimer = setTimeout(function() {
            refreshTable(query);
        }, 400);
    });

    $('.search-form').on('submit', function(e) {
        e.preventDefault();
        var query = $('#datatableSearch').val();
        refreshTable(query);
    });

    // Función auxiliar para refrescar la tabla mediante AJAX
    function refreshTable(searchQuery) {
        var currentUrl = window.location.href;
        var urlObj = new URL(currentUrl);
        if (searchQuery !== undefined) {
            if (searchQuery) {
                urlObj.searchParams.set('search', searchQuery);
            } else {
                urlObj.searchParams.delete('search');
            }
        }

        $.ajax({
            url: urlObj.toString(),
            type: 'GET',
            dataType: 'html',
            beforeSend: function() {
                $('#loading').show();
            },
            success: function(data) {
                var newContent = $(data).find('.card.mt-2').html();
                if (newContent) {
                    $('.card.mt-2').html(newContent);
                }
                window.history.pushState(null, '', urlObj.toString());
            },
            complete: function() {
                $('#loading').hide();
            }
        });
    }

});

$('.location-reload-to-category').on('click', function() {
    const url = $(this).data('url');
    let nurl = new URL(url);
    nurl.searchParams.delete('search');
    location.href = nurl;
});

$('#reset_btn').click(function(){
    $('#exampleFormControlSelect1').val(null).trigger('change');
});

$(document).on('click', '.data-info-show', function() {
    let id = $(this).data('id');
    let url = $(this).data('url');
    $('#content-disable').addClass('disabled');
    fetch_data(id, url);
});

function fetch_data(id, url) {
    $.ajax({
        url: url,
        type: "get",
        beforeSend: function() {
            $('#data-view').empty();
            $('#loading').show();
        },
        success: function(data) {
            $("#data-view").append(data.view);
            initLangTabs();
            initSelect2Dropdowns();
        },
        complete: function() {
            $('#loading').hide();
        }
    });
}

function initLangTabs() {
    const langLinks = document.querySelectorAll(".lang_link1");
    langLinks.forEach(function(langLink) {
        langLink.addEventListener("click", function(e) {
            e.preventDefault();
            langLinks.forEach(function(link) {
                link.classList.remove("active");
            });
            this.classList.add("active");
            document.querySelectorAll(".lang_form1").forEach(function(form) {
                form.classList.add("d-none");
            });
            let form_id = this.id;
            let lang = form_id.substring(0, form_id.length - 5);
            $("#" + lang + "-form1").removeClass("d-none");
            if (lang === "default") {
                $(".default-form1").removeClass("d-none");
            }
        });
    });
}

function initSelect2Dropdowns() {
    $('.js-select2-custom1').select2({
        placeholder: 'Select tax rate',
        allowClear: true
    });

    $('.offcanvas-close, #offcanvasOverlay').on('click', function () {
        $('.custom-offcanvas').removeClass('open');
        $('#offcanvasOverlay').removeClass('show');
        $('#content-disable').removeClass('disabled');
    });
}
