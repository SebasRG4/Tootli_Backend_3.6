@extends('layouts.admin.app')

@section('title',Request::is('admin/advertisement/copy-advertisement/*') ? 'Nuevo anuncio'  : 'Publicidad Editar')


@section('advertisement')
active
@endsection
@if (isset($request_page_type))
@section('advertisement_request')
@else
@section('advertisement_list')
@endif
active
@endsection


@push('css_or_js')
    <link rel="stylesheet" type="text/css" href="{{asset('assets/admin/css/daterangepicker.css')}}"/>
@endpush

@section('content')
<div class="content container-fluid">


    <!-- Advertisement -->
    <h1 class="page-header-title mb-3">
        {{ Request::is('admin/advertisement/copy-advertisement/*') ? 'Nuevo anuncio'  : 'Publicidad Editar' }}
    </h1>


    <div class="card mb-20">
        <div class="card-body p-30">
            <form id="create-add-form"  method="post" enctype="multipart/form-data" >
                @csrf
                @if (Request::is('admin/advertisement/copy-advertisement/*'))
                @method("POST")

                @else

                @method("PUT")
                @endif
                <div class="row g-4">
                    <div class="col-lg-6">
                        @isset($request_page_type)
                        <input type="hidden" name="request_page_type" value="true"  >
                        @endisset
                        @if ($language)
                    <div class="js-nav-scroller hs-nav-scroller-horizontal">
                        <ul class="nav nav-tabs mb-3 border-0">
                            <li class="nav-item">
                                <a class="nav-link lang_link active"
                                href="#"
                                id="default-link">{{'por defecto'}}</a>
                            </li>
                            @foreach ($language as $lang)
                                <li class="nav-item">
                                    <a class="nav-link lang_link"
                                        href="#"
                                        id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>

                                <div class="lang_form" id="default-form">
                                    <div class="mb-20">
                                        <label class="form-label">{{ 'Título del anuncio' }}   ({{ 'Por defecto' }})</label>
                                        <input type="text" class="form-control" id="title" name="title[]"
                                            value="{{  $advertisement?->getRawOriginal('title') }}" placeholder="{{ 'Oferta exclusiva' }}" maxlength="255"
                                            data-preview-text="preview-title">
                                    </div>
                                    <div class="form-floating mb-20">
                                        <label class="form-label">{{ 'Breve descripción' }}  ({{ 'Por defecto' }})</label>
                                        <textarea class="form-control resize-none" id="description"
                                            placeholder="{{ 'Obtener descuento' }}" name="description[]"
                                            data-preview-text="preview-description">{{$advertisement?->getRawOriginal('description') }}</textarea>
                                    </div>
                                <input type="hidden" name="lang[]" value="default">
                                </div>




                                @foreach ($language as $lang)
                                <?php
                                if(count($advertisement['translations'])){
                                    $translate = [];
                                    foreach($advertisement['translations'] as $t)
                                    {
                                        if($t->locale == $lang && $t->key=="title"){
                                            $translate[$lang]['title'] = $t->value;
                                        }
                                        if($t->locale == $lang && $t->key=="description"){
                                            $translate[$lang]['description'] = $t->value;
                                        }
                                    }
                                }
                            ?>


                    <div class="d-none lang_form" id="{{ $lang }}-form">
                        <div class="mb-20">
                            <label class="form-label">{{ 'Título del anuncio' }}    ({{ strtoupper($lang) }})</label>
                            <input type="text" class="form-control" id="title" name="title[]"
                            value="{{$translate[$lang]['title']??''}}"  placeholder="{{ 'Oferta exclusiva' }}" maxlength="255"
                                data-preview-text="preview-title">
                        </div>
                        <div class="form-floating mb-20">
                            <label class="form-label">{{ 'Breve descripción' }}   ({{ strtoupper($lang) }})</label>
                            <textarea class="form-control resize-none" id="description"
                                placeholder="{{ 'Obtener descuento' }}" name="description[]"
                                data-preview-text="preview-description">{{$translate[$lang]['description']??'' }}</textarea>
                        </div>
                        <input type="hidden" name="lang[]" value="{{ $lang }}">
                    </div>

                    @endforeach

                                @else

                                <div class="mb-20">
                                    <label class="form-label">{{ 'Título del anuncio' }}</label>
                                    <input type="text" class="form-control" id="title" name="title[]"
                                        value="{{  $advertisement?->getRawOriginal('title') }}" placeholder="{{ 'Oferta exclusiva' }}" maxlength="255"
                                        data-preview-text="preview-title">
                                </div>
                                <div class="form-floating mb-20">
                                    <label class="form-label">{{ 'Breve descripción' }}</label>
                                    <textarea class="form-control resize-none" id="description"
                                        placeholder="{{ 'Obtener descuento' }}" name="description[]"
                                        data-preview-text="preview-description">{{$advertisement?->getRawOriginal('description') }}</textarea>
                                </div>
                                @endif










                            <label class="input-label" for="store_id">{{ 'seleccionar tienda' }} <span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ 'Si desea crear un anuncio en vídeo, puede omitir este campo.' }}"><img src="{{ asset('assets/admin/img/info-circle.svg') }}" alt=""></span></label>
                        <div class="mb-20">
                            <select name="store_id" id="store_id"  data-placeholder="{{ 'seleccionar tienda' }}"
                            class="js-data-example-ajax form-control">
                            @if (isset($advertisement->store))
                            <option value="{{ $advertisement->store_id }}" selected="selected">
                                {{ $advertisement->store->name }}</option>
                            @endif
                            </select>
                        </div>

                        <label class="form-label">{{ 'Seleccionar prioridad' }}</label>
                        <div class="mb-20">
                            <select class="form-control w-100 js-select2-custom" name="priority">
                                <option value="{{ $advertisement?->priority == null ||  $advertisement?->priority == 0 ?  '' : $advertisement?->priority }}">{{ $advertisement?->priority == null ||  $advertisement?->priority == 0 ?  'N / A' : $advertisement?->priority }} </option>
                                @for ($i = 1; $i <= $total_adds; $i++)
                                @if ($advertisement?->priority != $i )
                                <option value="{{ $i }}">{{ $i }}</option>
                                @endif
                                @endfor
                                @if ( $advertisement?->priority !== null)
                                    <option value="">{{  'N / A' }} </option>
                                @endif

                            </select>
                        </div>
                        <div class="mb-20">
                            <label class="form-label">{{ 'Tipo de anuncio' }}</label>
                            <select class="js-select form-control w-100 promotion_type" name="advertisement_type">
                                <option value="video_promotion" {{ $advertisement?->add_type == 'video_promotion' ? 'selected' : '' }}>{{ 'Vídeo de promoción' }}</option>
                                <option value="store_promotion" {{ $advertisement?->add_type == 'store_promotion' ? 'selected' : '' }} >{{ 'promoción de la tienda' }}</option>
                            </select>
                        </div>
                        <div class="mb-20">
                            <label class="form-label">{{ 'Validez' }}</label>
                            <div class="position-relative">
                                <i class="tio-calendar-month icon-absolute-on-right"></i>
                                <input type="text" class="form-control h-45 position-relative bg-transparent" name="dates" value="{{ Carbon\Carbon::parse($advertisement?->start_date)->format('m/d/Y')  . ' - '.  Carbon\Carbon::parse($advertisement?->end_date)->format('m/d/Y')  }}" placeholder="dd/mm/yyyy - dd/mm/yyyy">
                            </div>
                        </div>

                        <div class="promotion-typewise-upload-box" id="video-upload-box">
                            <label class="form-label">{{ 'Cargar archivos relacionados' }}</label>
                            <div class="border rounded p-3">
                                <div class="d-flex flex-column align-items-center gap-3">
                                    <p class="title-color mb-0 ">{{ 'Sube tu vídeo' }}

                                        ({{ '16:9' }})</p>

                                    <div class="upload-file">
                                        <input type="file" class="video_attachment" name="video_attachment"
                                            accept="video/mp4, video/webm, video/mkv">
                                        <div class="upload-file__img upload-file__img_banner upload-file__video-not-playable h-140">
                                        </div>
                                        <button class="remove-file-button" type="button">
                                            <i class="tio-clear"></i>
                                        </button>
                                    </div>

                                    <p class="opacity-75 max-w220 mx-auto text-center fs-12">
                                        {{ 'Máximo 5MB' }}
                                        <br>
                                        {{ 'Soporta: MP4, WEBM, MKV' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="promotion-typewise-upload-box" id="profile-upload-box">
                            <h5 class="mb-3">{{ 'Mostrar reseña' }} &amp; {{ 'Calificaciones' }}</h5>
                            <div class="card bg--secondary shadow-none">
                                <div class="card-body p-3">
                                    <div class="w-100 d-flex flex-wrap gap-3">
                                        <label class="form-check form--check-2 me-3">
                                            <input type="checkbox"  id="is_review_checked" value='1' class="form-check-input" name="review" {{ $advertisement?->is_review_active  == 1 ?  ' checked' :" " }} >
                                            <span class="form-check-label">{{ 'Revisar' }}</span>
                                        </label>
                                        <label class="form-check form--check-2">
                                            <input type="checkbox"  id="is_rating_checked" class="form-check-input"  value="1" name="rating"  {{ $advertisement?->is_rating_active  == 1 ?  'checked' :" " }} >
                                            <span class="form-check-label">{{ 'Clasificación' }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <br>
                            <label class="form-label">{{ 'Cargar archivos relacionados' }}</label>
                            <div class="d-flex flex-wrap flex-sm-nowrap justify-content-center gap-3 border rounded p-3">
                                <div class="d-flex flex-column align-items-center gap-3 flex-shrink-0">
                                    <p class="title-color mb-0">{{ 'Imagen de perfil' }} <span class="text-danger">({{ 'Relación - 1:1' }})</span></p>

                                    <div class="upload-file">
                                        <input type="file" class="cover_attachment js-upload-input"
                                            data-target="profile-prev-image" name="profile_image"
                                            accept=".webp, .png,.jpg,.jpeg,.gif, |image/*">
                                        <div class="upload-file__img">
                                            <img src="{{ $advertisement?->profile_image_full_url }}" data-src="{{asset('assets/admin/img/media/upload-file.png')}}" alt="" >
                                        </div>
                                        <button class="remove-file-button" type="button">
                                            <i class="tio-clear"></i>
                                        </button>
                                    </div>

                                    <p class="opacity-75 max-w220 mx-auto text-center fs-12">
                                        {{ 'Compatible con: PNG, JPG, JPEG, WEBP' }}
                                        <br>
                                        {{ 'Máximo 2 MB' }}
                                    </p>
                                </div>
                                <div class="d-flex flex-column align-items-center gap-3">
                                    <p class="title-color mb-0">{{ 'Subir portada' }} <span class="text-danger">({{ 'Relación - 2:1' }})</span></p>
                                    <div class="upload-file">
                                        <input type="file" class="cover_attachment js-upload-input"
                                            data-target="main-image" name="cover_image"
                                            accept=".webp, .png,.jpg,.jpeg,.gif, |image/*">
                                        <div class="upload-file__img upload-file__img_banner aspect-2-1">
                                            <img src="{{ $advertisement?->cover_image_full_url }}" data-src="{{asset('assets/admin/img/media/banner-upload-file.png')}}" alt="" >
                                        </div>
                                        <button class="remove-file-button" type="button">
                                            <i class="tio-clear"></i>
                                        </button>
                                    </div>

                                    <p class="opacity-75 max-w220 mx-auto text-center fs-12">
                                        {{ 'Compatible con: PNG, JPG, JPEG, WEBP' }}
                                        <br>
                                        {{ 'Máximo 2 MB' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="position-sticky top-80px text-8797AB">
                            <div class="bg-light p-3 p-sm-4 rounded">
                                <label class="form-label">{{ 'Vista previa del anuncio' }}</label>
                                <div id="video-preview-box" class="video-preview-box">
                                    <div class="bg--secondary rounded">
                                        <div class="video h-200">
                                            <video src="{{$advertisement?->video_attachment ? $advertisement?->video_attachment_full_url :'' }}" controls>
                                                {{ 'Su navegador no soporta la etiqueta de video.' }}
                                            </video>
                                        </div>
                                        <div
                                            class="prev-video-box rounded bg-white px-3 py-4 position-relative gap-4 mt-n2">
                                            <div class="profile-img">
                                            </div>
                                            <div
                                                class="d-flex align-items-center justify-content-between gap-2">
                                                <div class="d-flex flex-column gap-2 flex-grow-1">
                                                    <div class="preview-title w-100">






                                                        <h5  class="set-def-title main-text pe-4"> {{ $advertisement?->getRawOriginal('title') }}</h5>
                                                        {{-- <div class="placeholder-text bg--secondary p-2 w-50"></div> --}}
                                                    </div>
                                                    <div class="preview-description w-100">
                                                        <div   class="set-def-description main-text line-limit-2">{{ $advertisement?->getRawOriginal('description') }}
                                                        </div>
                                                        {{-- <div class="placeholder-text bg--secondary p-2 w-75"></div> --}}
                                                    </div>
                                                    <div class="preview-description w-100">
                                                        {{-- <div class="placeholder-text bg--secondary p-2 w-65"></div> --}}
                                                    </div>
                                                </div>
                                                <a class="btn btn--primary py-2 px-3 cursor-auto">
                                                    <span class="tio-arrow-forward"></span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="profile-preview-box" class="profile-preview-box">
                                    <div class="bg--secondary rounded">
                                        <!-- Existing Profile Banner Image -->
                                        <div class="main-image rounded min-h-200" style="background: url('{{ $advertisement?->cover_image_full_url }}') center center / cover no-repeat">
                                        </div>
                                        <div class="rounded bg-white px-3 py-4 position-relative mt-n2">
                                            <div class="preview-title preview-description">
                                                <div class="wishlist-btn bg--secondary placeholder-text"></div>
                                                <div class="static-text wishlist-btn-2" style="display: block;">
                                                    <div
                                                        class="h-100 w-100 d-flex align-items-center justify-content-center">
                                                        <i class="tio-heart-outlined"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div
                                                class="d-flex align-items-center justify-content-between gap-2">
                                                <!-- Existing Profile Image -->
                                                <div class="profile-prev-image bg--secondary me-xl-3" style="background: url('{{ $advertisement?->profile_image_full_url }}') center center / cover no-repeat">
                                                </div>
                                                <div class="review-rating-demo">
                                                    <div class="rating-text static-text">
                                                        <div class="rating-number d-flex align-items-center">
                                                            <i class="tio-star"></i><span  id="rating_data" >{{ '4.7' }}</span>
                                                        </div>
                                                    </div>
                                                    <span id="review_data" class="review--text static-text">({{ '25+' }})</span>
                                                </div>
                                                <div class="w-0 d-flex flex-column gap-2 flex-grow-1">
                                                    <div class="d-flex justify-content-between">
                                                        <div class="preview-title w-100">
                                                            <h5 class="set-def-title main-text pe-4">{{ $advertisement?->getRawOriginal('title') }}</h5>
                                                            {{-- <div  class="placeholder-text bg--secondary p-2 w-50"></div> --}}
                                                        </div>
                                                    </div>
                                                    <div class="preview-description w-100">
                                                        <div class="set-def-description main-text line-limit-2">{{ $advertisement?->getRawOriginal('description') }}
                                                        </div>
                                                        {{-- <div class="placeholder-text bg--secondary p-2 w-75">{{ $advertisement?->getRawOriginal('description') }}</div> --}}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <br>
                            <br>
                            </div>
                            </div>
                            </div>
                                <div class="btn--container justify-content-end">
                                    <button type="reset" id="reset_btn" class="btn btn--reset">{{ 'Reiniciar' }}</button>
                                    <button type="submit" class="btn btn--primary">{{ 'Entregar' }}</button>
                                </div>
            </form>
        </div>
    </div>
    <!-- Advertisement -->

</div>
@endsection

@push('script_2')

    <script type="text/javascript" src="{{asset('assets/admin/js/moment.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('assets/admin/js/daterangepicker.min.js')}}"></script>



    <script>
        $(function() {

            $('input[name="dates"]').daterangepicker({
                startDate: moment('{{ $advertisement?->start_date }}').startOf('hour'),
                endDate: moment('{{ $advertisement?->end_date }}').startOf('hour'),
                minDate: new Date(),
                autoUpdateInput: false,

            });
            $('.js-select').each(function () {
                let select2 = $.HSCore.components.HSSelect2.init($(this));
            });

            $('input[name="dates"]').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('M/D/Y') + ' - ' + picker.endDate.format('M/D/Y'));
            });

        });

    </script>


    <!-- Video Upload Handlr -->
    <script>
        $(".video_attachment").on("change", function (event) {
            const videoEl = $(".video > video")
            const prevVideoBox = $('.prev-video-box')
            let file = event.target.files[0];
            let blobURL = URL.createObjectURL(file);
            const prevImage = $(this).closest('.upload-file').find('.upload-file__img').find('img').attr('src');
            videoEl.css('display', 'block');
            videoEl.attr('src', blobURL);
            videoEl.siblings('.play-icon').hide();
            $(this).closest('.upload-file').find('.upload-file__img').html('<video src="' + blobURL + '" controls></video>');
            $(this).closest('.upload-file').find('.remove-file-button').show()
            $(this).closest('.upload-file').find('.remove-file-button').on('click', function () {
                $(this).hide()
                videoEl.siblings('.play-icon').show();
                $(this).closest('.upload-file').find('.upload-file__img').find('img').attr('src', prevImage);
                $(this).closest('.upload-file').find('.video_attachment').val('');
                $(this).closest('.upload-file').find('.video > video').css('display', 'none');
                videoEl.css('display', 'none');
                videoEl.attr('src', '');
            })
        })

        $(window).on('load', function () {
            handleUploadBox();

            const videoEl = $(".video > video")
            let blobURL = "";
            // prev video attachment file
            blobURL = "{{ $advertisement?->video_attachment ?  $advertisement?->video_attachment_full_url : '' }}";

            videoEl.css('display', 'block');
            videoEl.attr('src', blobURL);
            $(".video_attachment").closest('.upload-file').find('.upload-file__img').html('<video src="' + blobURL + '" controls></video>');
            $(".video_attachment").closest('.upload-file').find('.remove-file-button').show()
            $(".video_attachment").closest('.upload-file').find('.remove-file-button').on('click', function () {
                $(this).hide()
                $(this).closest('.upload-file').find('.upload-file__img').html('<img src="{{asset('assets/admin/img/media/video-banner.png')}}" alt="">');
                $(this).closest('.upload-file').find('.video_attachment').val('');
                $(this).closest('.upload-file').find('.video > video').css('display', 'none');
                videoEl.css('display', 'none');
                videoEl.attr('src', '');
            })
        })
    </script>

    <!-- Select Toggler Scripts -->
    <script>
        const handleUploadBox = () => {
            const value = $('.promotion_type').val();
            if (value == 'video_promotion') {
                $('#video-upload-box, #video-preview-box').show();
                $('#profile-upload-box, #profile-preview-box').hide();
            } else {
                $('#video-upload-box, #video-preview-box').hide();
                $('#profile-upload-box, #profile-preview-box').show();
            }
        }
        $(window).on('load', function () {
            handleUploadBox()
        })

        $('.promotion_type').on('change', function () {
            handleUploadBox();

            @if( $advertisement?->add_type == 'store_promotion')
                $('.remove-file-button').click()
            @endif

        })



    </script>

    <!-- Profile Promotion Image Upload Handlr -->
    <script>
        $(".js-upload-input").on("change", function (event) {
            let file = event.target.files[0];
            const target = $(this).data('target');
            let blobURL = URL.createObjectURL(file);
            const prevImage = $(this).closest('.upload-file').find('.upload-file__img').find('img').attr('src');
            $(this).closest('.upload-file').find('.upload-file__img').html('<img src="' + blobURL + '" alt="">');
            $(this).closest('.upload-file').find('.remove-file-button').show()
            $('#profile-preview-box').find('.' + target).css('background', 'url(' + blobURL + ') no-repeat center center / cover');
            $(this).closest('.upload-file').find('.remove-file-button').on('click', function () {
                $('#profile-preview-box').find('.' + target).css('background', 'rgba(117, 133, 144, 0.1)');
                $(this).hide();
                $(this).closest('.upload-file').find('.upload-file__img').find('img').attr('src', prevImage);
                file ? $(this).closest('.upload-file').find('.js-upload-input').val(file) : ''
            })
        })
    </script>

    <!-- Title and Description Change Handlr -->
    <script>
        $('[data-preview-text]').on('input', function (event) {
            const target = $(this).data('preview-text');
            if (event.target.value) {
                $('.' + target).each(function () {
                    $(this).find('.main-text').text(event.target.value)
                    $(this).find('.placeholder-text').hide()
                    $(this).find('.static-text').show()
                })
            } else {
                $('.' + target).each(function () {
                    $(this).find('.main-text').text('')
                    $(this).find('.placeholder-text').show()
                    $(this).find('.static-text').hide()
                })
            }
        })
        // const resetTextHandlr = () => {
        //     $('[data-preview-text]').each(function () {
        //         const target = $(this).data('preview-text');
        //         const value = $(this).val()
        //         if (value) {
        //             $('.' + target).each(function () {
        //                 $(this).find('.main-text').text(value)
        //                 $(this).find('.placeholder-text').hide()
        //                 $(this).find('.static-text').show()
        //             })
        //         }
        //     })
        // }
        // $(window).on('load', function () {
        //     resetTextHandlr()
        // })

        $('#create-add-form').on('reset', function () {
            window.location.reload()
        })
    </script>

    <!-- Review and Rating Handlr -->
    <script>
        $('[name="review"]').on('change', function () {
            if ($(this).is(':checked')) {
                $('.review-placeholder').hide()
                $('.review--text').show()
                $('.review-rating-demo').css('opacity', '1')
            } else {
                $('.review-placeholder').show()
                $('.review--text').hide()
                if(!$('[name="rating"]').is(':checked')){
                    $('.review-rating-demo').css('opacity', '0')
                }
            }
        })
        $('[name="rating"]').on('change', function () {
            if ($(this).is(':checked')) {
                $('.rating-text').show()
                $('.review-rating-demo').css('opacity', '1')
            } else {
                $('.rating-text').hide()
                if(!$('[name="review"]').is(':checked')){
                    $('.review-rating-demo').css('opacity', '0')
                }
            }
        })


        $(window).on('load', function () {
            check_review_and_rating($('.js-data-example-ajax').val());
            $('[name="review"]').each(function () {
                if ($(this).is(':checked')) {
                    $('.review--text').show()
                } else {
                    $('.review--text').hide()
                    if(!$('[name="rating"]').is(':checked')){
                        $('.review-rating-demo').css('opacity', '0')
                    }
                }
            })
            $('[name="rating"]').each(function () {
                if ($(this).is(':checked')) {
                    $('.rating-text').show()
                } else {
                    $('.rating-text').hide()
                    if(!$('[name="review"]').is(':checked')){
                        $('.review-rating-demo').css('opacity', '0')
                    }
                }
            })
        })
        $('[data-src]').each(function (){
            $(this).on('error', function (){
                $(this).attr('src', $(this).data('src'))
            })
        })
    </script>


<script>
            $(document).on('ready', function() {
                    $('.js-data-example-ajax').select2({
                        ajax: {
                            url: '{{ url('/') }}/admin/store/get-stores',
                            data: function(params) {
                                return {
                                    q: params.term, // search term
                                    page: params.page,
                                    module_id:{{ config('module')['current_module_id'] }}
                                };
                            },
                            processResults: function(data) {
                                return {
                                    results: data
                                };
                            },
                            __port: function(params, success, failure) {
                                let $request = $.ajax(params);

                                $request.then(success);
                                $request.fail(failure);

                                return $request;
                            }
                        },
                        allowClear: true,
                         placeholder: "{{ 'seleccionar tienda' }}"
                    });


                    $('#create-add-form').on('submit', function (event) {
                        event.preventDefault();
                        let formData = new FormData(this);
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });
                        $.post({
                            url: `{{ Request::is('admin/advertisement/copy-advertisement/*') ? route('admin.advertisement.copyAddPost',$advertisement?->id) : route('admin.advertisement.update',$advertisement?->id) }}`,
                            data: $('#create-add-form').serialize(),
                            data: formData,
                            cache: false,
                            contentType: false,
                            processData: false,
                            beforeSend: function () {
                                $('#loading').show();
                            },
                            success: function (data) {
                                $('#loading').hide();
                                if (data.errors) {
                                    for (let i = 0; i < data.errors.length; i++) {
                                        toastr.error(data.errors[i].message, {
                                            CloseButton: true,
                                            ProgressBar: true
                                        });
                                    }
                                }
                                    else if(data.file_required){
                                        toastr.error(data.message, {
                                            CloseButton: true,
                                            ProgressBar: true
                                        });
                                        $('#loading').hide();
                                    }
                                else {
                                    toastr.success(data.message, {
                                        CloseButton: true,
                                        ProgressBar: true
                                    });
                                    setTimeout(function () {
                                        location.href = '{{route('admin.advertisement.index')}}';
                                    }, 2000);
                                }
                            }
                        });
                    });
            });



            $(document).on('change', '.js-data-example-ajax', function () {
                var store_id= $(this).val();
                check_review_and_rating(store_id)
            });
            $(document).on('change', '#is_review_checked', function () {
                if($(this).is(':checked') == true){
                    var store_id= $('.js-data-example-ajax').val();
                    if(store_id){
                        check_review_and_rating(store_id)
                    }
                }

            });
            $(document).on('change', '#is_rating_checked', function () {
                if($(this).is(':checked') == true){
                    var store_id= $('.js-data-example-ajax').val();
                    if(store_id){
                        check_review_and_rating(store_id)
                    }
                }
            });




            function check_review_and_rating(store_id){
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: "{{route('admin.store.get-store-ratings')}}",
                    method: 'get',
                    data: {
                        store_id: store_id,
                    },
                    beforeSend: function () {

                    },
                    success: function (response) {
                        $('#rating_data').html(response.rating);
                        $('#review_data').html( ' (' + response.review +  '+)' ) ;

                    },
                    complete: function () {
                    },
                });
            }
</script>
@endpush
