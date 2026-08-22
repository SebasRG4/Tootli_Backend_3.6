@extends('layouts.admin.app')

@section('title', 'Vista previa del repartidor')

@section('content')
    <div class="content container-fluid pb-0">
        @include('admin-views.delivery-man.partials._page_header')

        <div class="">
            @include('admin-views.delivery-man.partials._tab_menu')
        </div>
    </div>
    <!-- End Page Header -->

    <div class="content container-fluid pt-0">
        <div class="card">
            <div class="card-body pb-5">
                @if ($deliveryMan->application_status == 'approved')
                    <div
                        class="d-flex mb-xxl-4 mb-3 justify-content-between align-items-center gap-2 flex-wrap position-relative z-index-2">
                        <h4 class="card-title text-dark align-items-center flex-wrap gap-2">
                            {{ 'Detalles del repartidor' }}
                        </h4>

                        <div class="d-flex flex-wrap gap-2">
                            <a href="javascript:"
                                class="btn request-alert py-2 btn-warning text-white align-items-center d-flex"
                                data-url="{{ route('admin.users.delivery-man.reset-device', [$deliveryMan['id']]) }}"
                                data-message="{{ '¿Estás seguro de restablecer el dispositivo para este repartidor?' }}">
                                <i class="tio-android-phone mr-1"></i> {{ 'Restablecer dispositivo' }}
                            </a>
                            <a href="javascript:"
                                class="btn request-alert py-2 {{ $deliveryMan->status ? 'btn--danger' : 'btn-success' }} align-items-center d-flex"
                                data-url="{{ route('admin.users.delivery-man.status', [$deliveryMan['id'], $deliveryMan->status ? 0 : 1]) }}"
                                data-message="{{ $deliveryMan->status ? 'quieres suspender a este repartidor' : 'quieres dessuspender a este repartidor' }}">
                                {{ $deliveryMan->status ? 'suspender a este repartidor' : 'suspender a este repartidor' }}
                            </a>
                            <div class="hs-unfold">

                                <div class="dropdown">
                                    <button class="btn btn--primary dropdown_after gap-0 fs-14 dropdown-toggle"
                                        type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false">
                                        <img src="{{ asset('assets/admin/img/icons/bx_edit.png') }}" alt=""
                                            class="mr-1">
                                        {{ 'Editar' }}

                                    </button>
                                    <div class="dropdown-menu min-w-220 dropdown-menu-right text-capitalize"
                                        aria-labelledby="dropdownMenuButton">
                                        <a class="dropdown-item fs-14 font-weight-medium text-dark"
                                            href="{{ route('admin.users.delivery-man.edit', [$deliveryMan->id]) }}">{{ 'Editar información' }}</a>
                                        <a class="dropdown-item fs-14 font-weight-medium text-dark" data-toggle="modal"
                                            data-target="#work_switcher" href="javascript:">
                                            {{ 'Editar tipo de entrega' }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>
                @endif
                <div
                    class="d-flex flex-column flex-lg-nowrap flex-wrap flex-md-row align-items-center gap-3 border rounded p-3">
                    <div class="d-flex gap-3 justify-content-center position-relative w-115 rounded">
                        <img class="rounded" data-onerror-image="{{ asset('assets/admin/img/160x160/img1.jpg') }}"
                            src="{{ $deliveryMan['image_full_url'] }}" width="115" height="115"
                            alt="Delivery man image">
                        <span
                            class="suspend-badge bg-danger py-0 px-2 mb-2 fs-13 lh-1 text-white rounded position-absolute bottom-0 start-0">{{ !$deliveryMan['status'] && $deliveryMan['application_status'] == 'approved' ? 'suspendido' : '' }}</span>
                    </div>

                    <div class="flex-grow-1">
                        <div class="mb-3">
                            <h4 title="{{ $deliveryMan['f_name'] . ' ' . $deliveryMan['l_name'] }}"
                                class="d-flex justify-content-center justify-content-md-start mb-1 gap-2">
                                {{ $deliveryMan['f_name'] . ' ' . $deliveryMan['l_name'] }}
                                @if ($deliveryMan->application_status == 'approved')
                                    @if ($deliveryMan['status'])
                                        @if ($deliveryMan['active'])
                                            <label
                                                class=" mb-0 badge badge-soft-primary">{{ 'en línea' }}</label>
                                        @else
                                            <label
                                                class=" mb-0 badge badge-soft-danger">{{ 'desconectado' }}</label>
                                        @endif
                                    @else
                                        <label
                                            class=" mb-0 badge badge-danger">{{ 'suspendido' }}</label>
                                    @endif
                                @else
                                    <label
                                        class=" mb-0 badge badge-soft-{{ $deliveryMan->application_status == 'pending' ? 'info' : 'danger' }}">{{ translate('messages.' . $deliveryMan->application_status) }}</label>
                                @endif
                            </h4>
                            <div class="fs-12 text-title d-flex justify-content-center justify-content-md-start">
                                @if ($deliveryMan->application_status == 'approved')
                                    <a href="mailto:{{ $deliveryMan['email'] }}" class="text-title">
                                        {{ $deliveryMan['email'] }}</a>
                                    <span class="d-block mx-2 text-muted">|</span>
                                    <a href="tel:{{ $deliveryMan['phone'] }}" class="text-title">
                                        {{ $deliveryMan['phone'] }}</a>
                                @endif
                            </div>
                        </div>
                        <div
                            class="bg-light2 d-flex align-items-center flex-xxl-nowrap flex-wrap rider_overview-info rounded">
                            <div class="d-flex justify-content-center justify-content-md-start gap-3">
                                <div class="">
                                    <h6 class="fs-13 mb-1 font-weight-normal text-dark">
                                        {{ 'Tipo de trabajo' }} </h6>
                                    <p class="mb-0 fs-14 font-weight-bold text-dark ">
                                        {{ $deliveryMan->earning ? 'persona de libre dedicación' : 'basado en salario' }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-muted line-30"></div>
                            <div class="d-flex justify-content-center justify-content-md-start gap-3">
                                <div class="">
                                    <h6 class="fs-13 mb-1 font-weight-normal text-dark">
                                        {{ 'Tipo de vehículo' }}</h6>
                                    <p class="mb-0 fs-14 font-weight-bold text-dark ">
                                        {{ $deliveryMan?->vehicle?->type ?? 'Vehículo desconocido' }}</p>
                                </div>
                            </div>
                            <div class="text-muted line-30"></div>
                            <div class="d-flex justify-content-center justify-content-md-start gap-3">
                                <div class="">
                                    <h6 class="fs-13 mb-1 font-weight-normal text-dark">{{ 'Zona' }}
                                    </h6>
                                    <p class="mb-0 fs-14 font-weight-bold text-dark ">
                                        {{ isset($deliveryMan->zone) ? $deliveryMan->zone->name : 'zona eliminada' }}
                                    </p>
                                </div>
                            </div>

                            <div class="text-muted line-30"></div>
                            <div class="d-flex justify-content-center justify-content-md-start gap-3">
                                <div class="">
                                    <h6 class="fs-13 mb-1 font-weight-normal text-dark">
                                        {{ 'servicios' }} </h6>
                                    <div class="mb-0 fs-14 font-weight-bold text-dark ">
                                        @if($deliveryMan->can_deliver)
                                            <span class="badge badge-soft-info">{{'entrega'}}</span>
                                        @endif
                                        @if($deliveryMan->can_drive_taxi)
                                            <span class="badge badge-soft-warning">{{'Taxi'}}</span>
                                            @if($deliveryMan->taxi_is_verified)
                                                <i class="tio-checkmark-circle text-success" title="{{'verificado'}}"></i>
                                            @else
                                                <i class="tio-warning text-warning" title="{{'inconfirmado'}}"></i>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @if($deliveryMan->can_drive_taxi)
                            <div class="text-muted line-30"></div>
                            <div class="d-flex justify-content-center justify-content-md-start gap-3">
                                <div class="">
                                    <h6 class="fs-13 mb-1 font-weight-normal text-dark">{{ 'número de licencia de taxi' }}</h6>
                                    <p class="mb-0 fs-14 font-weight-bold text-dark ">
                                        {{ $deliveryMan->taxi_license_number }}
                                    </p>
                                </div>
                            </div>
                            @endif

                            {{-- Módulos habilitados --}}
                            <div class="text-muted line-30"></div>
                            <div class="d-flex justify-content-center justify-content-md-start gap-3">
                                <div class="">
                                    <h6 class="fs-13 mb-1 font-weight-normal text-dark">{{ 'Módulos habilitados' }}</h6>
                                    <div class="mb-0 font-weight-bold text-dark d-flex flex-wrap gap-1">
                                        @php $dmModules = $deliveryMan->modules; @endphp
                                        @if($dmModules && $dmModules->count() > 0)
                                            @foreach($dmModules as $mod)
                                                <span class="badge badge-soft-primary">{{ $mod->module_name }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted fs-12">{{ 'Todos los módulos (sin restricción)' }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    @if ($deliveryMan->application_status == 'approved')
                        @php $total = $deliveryMan->reviews->count(); @endphp
                        <div class="d-flex flex-column flex-sm-nowrap flex-wrap flex-sm-row gap-3 flex-grow-1 border-lg-left">
                            @if ($total > 0)
                                <div class="d-flex flex-column align-items-center justify-content-center px-4">
                                    <img class=""
                                        src="{{ asset('assets/admin/img/icons/rating-stars.png') }}" alt="">

                                    <div class="d-block">
                                        <div class="rating--review">
                                            <h3 class="title mb-0">
                                                {{ count($deliveryMan->rating) > 0 ? number_format($deliveryMan->rating[0]->average, 1) : 0 }}<span
                                                    class="out-of">/5</span></h3>
                                            <div class="info">
                                                <span>{{ 'de' }} {{ $deliveryMan->reviews->count() }}
                                                    {{ 'opiniones' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <ul
                                    class="list-unstyled list-unstyled-py-2 mb-0 rating--review-right py-3 flex-grow-1 review-color-progress">

                                    <!-- Review Ratings -->
                                    <li class="d-flex align-items-center font-size-sm">
                                        @php $five = \App\CentralLogics\Helpers::dm_rating_count($deliveryMan['id'], 5); @endphp
                                        <span class="progress-name mr-3">{{ 'excelente' }}</span>
                                        <div class="progress flex-grow-1">
                                            <div class="progress-bar" role="progressbar"
                                                style="width: {{ $total == 0 ? 0 : ($five / $total) * 100 }}%;"
                                                aria-valuenow="{{ $total == 0 ? 0 : ($five / $total) * 100 }}"
                                                aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <span class="ml-3">{{ $five }}</span>
                                    </li>
                                    <!-- End Review Ratings -->

                                    <!-- Review Ratings -->
                                    <li class="d-flex align-items-center font-size-sm">
                                        @php $four = \App\CentralLogics\Helpers::dm_rating_count($deliveryMan['id'], 4); @endphp
                                        <span class="progress-name mr-3">{{ 'bien' }}</span>
                                        <div class="progress flex-grow-1">
                                            <div class="progress-bar" role="progressbar"
                                                style="width: {{ $total == 0 ? 0 : ($four / $total) * 100 }}%;"
                                                aria-valuenow="{{ $total == 0 ? 0 : ($four / $total) * 100 }}"
                                                aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <span class="ml-3">{{ $four }}</span>
                                    </li>
                                    <!-- End Review Ratings -->

                                    <!-- Review Ratings -->
                                    <li class="d-flex align-items-center font-size-sm">
                                        @php $three = \App\CentralLogics\Helpers::dm_rating_count($deliveryMan['id'], 3); @endphp
                                        <span class="progress-name mr-3">{{ 'promedio' }}</span>
                                        <div class="progress flex-grow-1">
                                            <div class="progress-bar" role="progressbar"
                                                style="width: {{ $total == 0 ? 0 : ($three / $total) * 100 }}%;"
                                                aria-valuenow="{{ $total == 0 ? 0 : ($three / $total) * 100 }}"
                                                aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <span class="ml-3">{{ $three }}</span>
                                    </li>
                                    <!-- End Review Ratings -->

                                    <!-- Review Ratings -->
                                    <li class="d-flex align-items-center font-size-sm">
                                        @php $two = \App\CentralLogics\Helpers::dm_rating_count($deliveryMan['id'], 2); @endphp
                                        <span class="progress-name mr-3">{{ 'por debajo del promedio' }}</span>
                                        <div class="progress flex-grow-1">
                                            <div class="progress-bar" role="progressbar"
                                                style="width: {{ $total == 0 ? 0 : ($two / $total) * 100 }}%;"
                                                aria-valuenow="{{ $total == 0 ? 0 : ($two / $total) * 100 }}"
                                                aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <span class="ml-3">{{ $two }}</span>
                                    </li>
                                    <!-- End Review Ratings -->

                                    <!-- Review Ratings -->
                                    <li class="d-flex align-items-center font-size-sm">
                                        @php $one = \App\CentralLogics\Helpers::dm_rating_count($deliveryMan['id'], 1); @endphp
                                        <span class="progress-name mr-3">{{ 'pobre' }}</span>
                                        <div class="progress flex-grow-1">
                                            <div class="progress-bar" role="progressbar"
                                                style="width: {{ $total == 0 ? 0 : ($one / $total) * 100 }}%;"
                                                aria-valuenow="{{ $total == 0 ? 0 : ($one / $total) * 100 }}"
                                                aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <span class="ml-3">{{ $one }}</span>
                                    </li>
                                    <!-- End Review Ratings -->
                                </ul>
                            @else
                                <div class="d-flex flex-column align-items-center justify-content-center px-4 m-auto">
                                    <img width="75" class=""
                                        src="{{ asset('assets/admin/img/icons/no_rating.png') }}" alt="">
                                    <p class="mb-0 font-weight-normal">
                                        {{ 'aún no se ha dado ninguna reseña/calificación' }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Card de Servicios y Módulos Autorizados por Administrador --}}
                <div class="card mt-3">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h5 class="card-title mb-0 d-flex align-items-center gap-2 text-dark font-weight-bold">
                            <i class="tio-settings-outlined text-primary fs-18"></i>
                            <span>{{ 'Servicios y Módulos Autorizados por Administrador' }}</span>
                        </h5>
                        <span class="badge badge-soft-info px-3 py-1 font-weight-medium fs-12">{{ 'Configuración Admin' }}</span>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('admin.users.delivery-man.update-services', [$deliveryMan->id]) }}" method="post">
                            @csrf

                            {{-- Módulos asignados --}}
                            <div class="mb-4">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <label class="form-label font-weight-bold text-dark mb-0 fs-14">
                                        <i class="tio-category-outlined text-secondary me-1"></i>
                                        {{ 'Módulos Permitidos para este Repartidor' }}
                                    </label>
                                    <small class="text-muted">{{ 'Selecciona los servicios a los que el repartidor tendrá acceso.' }}</small>
                                </div>

                                @if(isset($modules) && $modules->count() > 0)
                                <div class="row g-3">
                                    @foreach($modules as $module)
                                        @php $isChecked = isset($selectedModuleIds) && in_array($module->id, $selectedModuleIds); @endphp
                                        <div class="col-xl-3 col-lg-4 col-sm-6">
                                            <div class="border rounded p-3 h-100 bg-white shadow-sm hover-shadow d-flex align-items-center">
                                                <label class="form-check form--check mb-0 d-flex align-items-center gap-2 cursor-pointer w-100">
                                                    <input class="form-check-input mt-0" type="checkbox"
                                                           name="modules[]"
                                                           value="{{ $module->id }}"
                                                           id="preview_module_{{ $module->id }}"
                                                           {{ $isChecked ? 'checked' : '' }}>
                                                    <span class="form-check-label font-weight-medium text-dark fs-14">
                                                        {{ $module->module_name }}
                                                        <span class="badge badge-soft-secondary badge-sm ms-1">{{ $module->module_type }}</span>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>

                            {{-- Aprobación del Servicio de Viajes (Taxi) --}}
                            <div class="border-top pt-4">
                                <div class="bg-soft-primary p-3 p-md-4 rounded-lg d-flex flex-wrap align-items-center justify-content-between gap-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar avatar-circle bg-white text-primary p-2 d-flex align-items-center justify-content-center shadow-xs" style="width: 45px; height: 45px;">
                                            <i class="tio-car fs-20"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1 text-dark font-weight-bold fs-15">{{ 'Aprobación del Servicio de Viajes (Taxi)' }}</h6>
                                            <p class="text-muted mb-0 fs-13">
                                                {{ 'Se requiere aprobación del Administrador para que el repartidor pueda operar el servicio de Viajes.' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-4 flex-wrap">
                                        <div class="form-check form-switch mb-0 d-flex align-items-center gap-2 cursor-pointer">
                                            <input type="checkbox" class="form-check-input mt-0" name="can_drive_taxi" value="1"
                                                   id="preview_can_drive_taxi" {{ $deliveryMan->can_drive_taxi ? 'checked' : '' }}>
                                            <span class="form-check-label font-weight-bold text-dark fs-14">{{ 'Habilitar Servicio de Viajes' }}</span>
                                        </div>
                                        <div class="form-check form-switch mb-0 d-flex align-items-center gap-2 cursor-pointer">
                                            <input type="checkbox" class="form-check-input mt-0" name="taxi_is_verified" value="1"
                                                   id="preview_taxi_is_verified" {{ $deliveryMan->taxi_is_verified ? 'checked' : '' }}>
                                            <span class="form-check-label font-weight-bold text-success fs-14">{{ 'Documentos Verificados' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn--primary px-4 py-2 font-weight-bold">
                                    <i class="tio-save me-1"></i> {{ 'Guardar Cambios de Servicios' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                @if ($deliveryMan->application_status == 'approved')
                    @include('admin-views.delivery-man.partials._assignment_admin_panel')
                @endif

                <div class="border rounded p-xxl-20 p-3 mt-20">
                    <div class="d-flex gap-2 align-items-center mb-20">
                        @if ($deliveryMan->application_status == 'approved')
                            <h5 class="mb-0 fs-16 fw-bold">{{ 'Documentos de identidad' }}</h5>
                        @else
                            <h5 class="mb-0 fs-16 fw-bold">{{ 'Información de registro' }}</h5>
                        @endif
                    </div>
                    <div class="row g-3">
                        @if ($deliveryMan->application_status == 'pending')
                            <div class="col-lg-4">
                                <div class="bg-light2 rounded p-3 h-100 d-flex flex-column gap-2">

                                    <div class="key-val-list-item d-flex gap-3">
                                        <div class="text-title fs-14 identity__info">
                                            {{ 'Nombre de pila' }} </div>:
                                        <div class="text-dark fs-14">{{ $deliveryMan['f_name'] }}</div>
                                    </div>
                                    <div class="key-val-list-item d-flex gap-3">
                                        <div class="text-title fs-14 identity__info">{{ 'Apellido' }}
                                        </div>:
                                        <div class="text-dark fs-14">{{ $deliveryMan['l_name'] }}</div>
                                    </div>
                                    <div class="key-val-list-item d-flex gap-3">
                                        <div class="text-title fs-14 identity__info">{{ 'correo electrónico' }}
                                        </div>:
                                        <div class="text-dark fs-14">{{ $deliveryMan['email'] }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="col-lg-4">
                            <div class="bg-light2 rounded p-3 h-100 d-flex flex-column gap-2">

                                <div class="key-val-list-item d-flex gap-3">
                                    <div class="text-title fs-14 identity__info">{{ 'Tipo de identidad' }}</div>:
                                    <div class="text-dark fs-14">{{ translate($deliveryMan->identity_type) }}</div>
                                </div>
                                <div class="key-val-list-item d-flex gap-3">
                                    <div class="text-title fs-14 identity__info">
                                        {{ 'número de identificación' }}</div>:
                                    <div class="text-dark fs-14">{{ $deliveryMan->identity_number }}</div>
                                </div>
                            </div>
                        </div>
                        @if ($deliveryMan->application_status == 'pending')
                            <div class="col-lg-4">
                                <div class="bg-light2 rounded p-3 h-100 d-flex flex-column gap-2">

                                    <div class="key-val-list-item d-flex gap-3">
                                        <div class="text-title fs-14 identity__info">{{ 'Teléfono' }}
                                        </div>:
                                        <div class="text-dark fs-14">{{ $deliveryMan->phone }}</div>
                                    </div>
                                    <div class="key-val-list-item d-flex gap-3">
                                        <div class="text-title fs-14 identity__info">{{ 'Contraseña' }}
                                        </div>:
                                        <div class="text-dark fs-14">**********</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class=" {{ $deliveryMan->application_status == 'pending' ? 'col-12' : 'col-lg-8' }} ">
                            <div class="bg-light2 rounded p-3 h-100 identity_documnet_body tabs-slide-wrap">

                                <div class="tabs-inner d-flex gap-3 identity_documnet_wrap">
                                    @foreach ($deliveryMan->identity_image_full_url as $key => $img)
                                        <button class="btn  p-0" data-toggle="modal"
                                            data-target="#image-{{ $key }}">
                                            <div class="gallary-card">
                                                <img class="rounded mx-h150 mx-w-100"
                                                    data-onerror-image="{{ asset('assets/admin/img/900x400/img1.jpg') }}"
                                                    src="{{ $img }}" width="275" height="150"
                                                    alt="">
                                            </div>
                                        </button>
                                        <div class="modal fade" id="image-{{ $key }}" tabindex="-1"
                                            role="dialog" aria-labelledby="myModlabel" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title" id="myModlabel">
                                                            {{ 'Imagen de identidad' }}</h4>
                                                        <button type="button" class="close" data-dismiss="modal"><span
                                                                aria-hidden="true">&times;</span><span
                                                                class="sr-only">{{ 'Cerca' }}</span></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <img data-onerror-image="{{ asset('assets/admin/img/900x400/img1.jpg') }}"
                                                            src="{{ $img }}" class="w-100 onerror-image">
                                                    </div>
                                                    <div class="modal-footer">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="arrow-area">
                                    <div class="button-prev align-items-center">
                                        <button type="button"
                                            class="btn btn-click-prev mr-auto border-0 btn-primary rounded-circle fs-12 p-2 d-center">
                                            <i class="tio-chevron-left fs-24"></i>
                                        </button>
                                    </div>
                                    <div class="button-next align-items-center">
                                        <button type="button"
                                            class="btn btn-click-next ml-auto border-0 btn-primary rounded-circle fs-12 p-2 d-center">
                                            <i class="tio-chevron-right fs-24"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ╔══════════════════════════════════════════╗ --}}
    {{-- ║  SECCIÓN: VERIFICACIÓN DE IDENTIDAD KYC  ║ --}}
    {{-- ╚══════════════════════════════════════════╝ --}}
    @php $kycUser = \App\Http\Controllers\Admin\DeliveryMan\DeliveryManController::findOrCreateUserForDeliveryMan($deliveryMan); @endphp
    @php $kycStatus = $kycUser?->identity_verified ?? 'none'; @endphp
    @php $kycVerificationId = $kycUser?->metamap_verification_id ?? null; @endphp
    <div class="content container-fluid pt-0">
        <div class="card">
            <div class="card-body p-xxl-20 p-3">
                <div class="d-flex align-items-center gap-2 mb-20 justify-content-between flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="mb-0 fs-16 fw-bold">Verificación KYC (Identidad)</h5>
                        @if($kycStatus === 'approved')
                            <span class="badge badge-soft-success fs-13">✔ Aprobada</span>
                        @elseif($kycStatus === 'rejected')
                            <span class="badge badge-soft-danger fs-13">✘ Rechazada</span>
                        @elseif($kycStatus === 'pending')
                            <span class="badge badge-soft-warning fs-13">⏳ Pendiente de revisión</span>
                        @else
                            <span class="badge badge-soft-secondary fs-13">— Sin verificar</span>
                        @endif
                    </div>
                    @if($kycVerificationId)
                        <a href="https://dashboard.getmati.com/verifications/{{ $kycVerificationId }}"
                           target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="tio-open-in-new mr-1"></i> Ver en MetaMap
                        </a>
                    @endif
                </div>

                @if($kycVerificationId)
                    <div class="mb-3">
                        <small class="text-muted">MetaMap Verification ID:</small>
                        <code class="ml-1 fs-12">{{ $kycVerificationId }}</code>
                    </div>
                @else
                    <p class="text-muted fs-13 mb-3">
                        <i class="tio-info-outined mr-1"></i>
                        El repartidor aún no ha completado el flujo de verificación MetaMap.
                    </p>
                @endif

                {{-- Botones de verificación manual --}}
                <div class="d-flex flex-wrap gap-2">
                    {{-- Aprobar --}}
                    <form action="{{ route('admin.users.delivery-man.kyc-status', $deliveryMan->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="identity_verified" value="approved">
                        <button type="submit"
                            class="btn {{ $kycStatus === 'approved' ? 'btn-success' : 'btn-outline-success' }} py-2"
                            {{ $kycStatus === 'approved' ? 'disabled' : '' }}>
                            <i class="tio-checkmark-circle mr-1"></i> Aprobar
                        </button>
                    </form>

                    {{-- Rechazar --}}
                    <form action="{{ route('admin.users.delivery-man.kyc-status', $deliveryMan->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="identity_verified" value="rejected">
                        <button type="submit"
                            class="btn {{ $kycStatus === 'rejected' ? 'btn--danger' : 'btn-outline-danger' }} py-2"
                            {{ $kycStatus === 'rejected' ? 'disabled' : '' }}>
                            <i class="tio-clear-circle mr-1"></i> Rechazar
                        </button>
                    </form>

                    {{-- Marcar como pendiente --}}
                    <form action="{{ route('admin.users.delivery-man.kyc-status', $deliveryMan->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="identity_verified" value="pending">
                        <button type="submit"
                            class="btn {{ $kycStatus === 'pending' ? 'btn-warning' : 'btn-outline-warning' }} py-2"
                            {{ $kycStatus === 'pending' ? 'disabled' : '' }}>
                            <i class="tio-time mr-1"></i> Marcar pendiente
                        </button>
                    </form>

                    {{-- Resetear --}}
                    <form action="{{ route('admin.users.delivery-man.kyc-status', $deliveryMan->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="identity_verified" value="none">
                        <button type="submit"
                            class="btn btn-outline-secondary py-2"
                            {{ $kycStatus === 'none' ? 'disabled' : '' }}>
                            <i class="tio-restore mr-1"></i> Resetear
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    {{-- ══════════ FIN SECCIÓN KYC ══════════ --}}



    <div class="content container-fluid pt-0">
        <div class="card">
            <div class="card-body">
                @if ($deliveryMan->application_status == 'approved')
                    <div class="row g-3 color-card-custom">
                        <div class="col-lg-3">
                            <div class="color-card h-100 align-items-center justify-content-center">
                                <div
                                    class="box d-flex flex-column text-center justify-content-center align-items-center gap-3">
                                    <div class="img-box">
                                        <img class="resturant-icon w--30"
                                            src="{{ asset('assets/admin/img/icons/color-icon-1.png') }}"
                                            alt="img">
                                    </div>
                                    <div>
                                        <h2 class="title fs-24 fw-bold mb-1">
                                            {{ count($deliveryMan['order_transaction']) }}
                                        </h2>
                                        <div class="subtitle text-title">
                                            {{ 'pedidos totales entregados' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-9">
                            <div class="row g-3 row-3">


                                <!-- Collected Cash Card Example -->
                                <div class="col-sm-6 col-xxl-4 col-xl-6 col-lg-6">
                                    <div class="color-card color-2">
                                        <div class="img-box">
                                            <img class="resturant-icon w--30"
                                                src="{{ asset('assets/admin/img/icons/color-icon-2.png') }}"
                                                alt="transactions">
                                        </div>
                                        <div>
                                            <h2 class="title fs-24 fw-bold mb-1">
                                                {{ \App\CentralLogics\Helpers::format_currency($deliveryMan->wallet ? $deliveryMan->wallet->collected_cash : 0.0) }}
                                            </h2>
                                            <div class="subtitle text-title">
                                                {{ 'efectivo en mano' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Total Earning Card Example -->
                                <div class="col-sm-6 col-xxl-4 col-xl-6 col-lg-6">
                                    <div class="color-card color-3">
                                        <div class="img-box">
                                            <img class="resturant-icon w--30"
                                                src="{{ asset('assets/admin/img/icons/color-icon-3.png') }}"
                                                alt="transactions">
                                        </div>
                                        <div>
                                            <h2 class="title fs-24 fw-bold mb-1">
                                                {{ \App\CentralLogics\Helpers::format_currency($deliveryMan->wallet ? $deliveryMan->wallet->total_earning : 0.0) }}
                                            </h2>
                                            <div class="subtitle text-title">
                                                {{ 'ganancia total' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Total Earning Card Example -->

                                <?php
                                $balance = 0;
                                if ($deliveryMan->wallet) {
                                    $balance = $deliveryMan->wallet->total_earning - ($deliveryMan->wallet->total_withdrawn + $deliveryMan->wallet->pending_withdraw + $deliveryMan->wallet->collected_cash);
                                }

                                ?>
                                @if ($deliveryMan->earning)
                                    @if ($balance > 0)
                                        <div class="col-sm-6 col-lg-4">
                                            <div class="color-card colxxl-4">
                                                <div class="img-box">
                                                    <img class="resturant-icon w--30"
                                                        src="{{ asset('assets/admin/img/icons/group.png') }}"
                                                        alt="transactions">
                                                </div>
                                                <div>
                                                    <h2 class="title fs-24 fw-bold mb-1">
                                                        {{ \App\CentralLogics\Helpers::format_currency(abs($balance)) }}
                                                    </h2>
                                                    <div class="subtitle text-title">
                                                        {{ 'Retirar saldo capaz' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @elseif($balance < 0)
                                        <div class="col-sm-6 col-xxl-4 col-xl-6 col-lg-6">
                                            <div class="color-card color-4">
                                                <div class="img-box">
                                                    <img class="resturant-icon w--30"
                                                        src="{{ asset('assets/admin/img/icons/color-icon-4.png') }}"
                                                        alt="transactions">
                                                </div>
                                                <div>
                                                    <h2 class="title fs-24 fw-bold mb-1">
                                                        {{ \App\CentralLogics\Helpers::format_currency(abs($deliveryMan->wallet->collected_cash)) }}
                                                    </h2>
                                                    <div class="subtitle text-title">
                                                        {{ 'Saldo a pagar' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="col-sm-6 col-xxl-4 col-xl-6 col-lg-6">
                                            <div class="color-card color-4">
                                                <div class="img-box">
                                                    <img class="resturant-icon w--30"
                                                        src="{{ asset('assets/admin/img/icons/group.png') }}"
                                                        alt="transactions">
                                                </div>
                                                <div>
                                                    <h2 class="title fs-24 fw-bold mb-1">
                                                        {{ \App\CentralLogics\Helpers::format_currency(0) }}
                                                    </h2>
                                                    <div class="subtitle text-title">
                                                        {{ 'Balance' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif


                                    <div class="col-sm-6 col-xxl-4 col-xl-6 col-lg-6">
                                        <div class="color-card color-5">
                                            <div class="img-box">
                                                <img class="resturant-icon w--30"
                                                    src="{{ asset('assets/admin/img/icons/color-icon-5.png') }}"
                                                    alt="transactions">
                                            </div>
                                            <div>
                                                <h2 class="title fs-24 fw-bold mb-1">
                                                    {{ \App\CentralLogics\Helpers::format_currency($deliveryMan->wallet ? $deliveryMan->wallet->total_withdrawn : 0.0) }}
                                                </h2>
                                                <div class="subtitle text-title">
                                                    {{ 'Total retirado' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-sm-6 col-xxl-4 col-xl-6 col-lg-6">
                                        <div class="color-card color-6">
                                            <div class="img-box">
                                                <img class="resturant-icon w--30"
                                                    src="{{ asset('assets/admin/img/icons/color-icon-6.png') }}"
                                                    alt="transactions">
                                            </div>
                                            <div>
                                                <h2 class="title fs-24 fw-bold mb-1">
                                                    {{ \App\CentralLogics\Helpers::format_currency($deliveryMan->wallet ? $deliveryMan->wallet->pending_withdraw : 0.0) }}
                                                </h2>
                                                <div class="subtitle text-title">
                                                    {{ 'Retiro pendiente' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xxl-4 col-xl-6 col-lg-6">
                                        <div class="color-card color-9">
                                            <div class="img-box">
                                                <img class="resturant-icon w--30"
                                                    src="{{ asset('assets/admin/img/icons/loyalty-star.png') }}"
                                                    alt="transactions">
                                            </div>
                                            <div>
                                                <h2 class="title text--039D55 fs-24 fw-bold mb-1">
                                                    {{ (int) $deliveryMan->loyalty_point }}
                                                </h2>
                                                <div class="subtitle text-title">
                                                    {{ 'Punto de fidelización' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>


    @if ($deliveryMan->application_status == 'approved')
        <div class="content container-fluid pt-0">
            <div class="card">
                <!-- Header -->
                <div class="card-header flex-sm-nowrap flex-wrap gap-2 pt-3 pb-0 border-0">
                    <h5 class="card-header-title d-flex align-items-center gap-2 text-nowrap line--limite-1">
                        {{ 'lista de revisión' }}
                        <span class="badge badge-soft-dark ml-2" id="itemCount">
                            {{ $reviews->total() }}
                        </span>
                    </h5>
                    <div class="search--button-wrapper justify-content-end">
                        <form class="search-form min--260">
                            <div class="input-group input--group">
                                <input id="datatableSearch_" type="search" name="search" class="form-control h--40px"
                                    placeholder="{{ 'buscar aquí' }}"
                                    value="{{ request()->search }}" aria-label="Search" tabindex="1">

                                <button type="submit" class="btn btn--secondary bg-modal-btn"><i
                                        class="tio-search text-muted"></i></button>
                            </div>
                        </form>
                        <!-- Unfold -->
                        <div class="hs-unfold mr-2">
                            <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle min-height-40"
                                href="javascript:;"
                                data-hs-unfold-options='{
                                    "target": "#usersExportDropdown",
                                    "type": "css-animation"
                                }'>
                                <i class="tio-download-to mr-1"></i> {{ 'exportar' }}
                            </a>

                            <div id="usersExportDropdown"
                                class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                                <span class="dropdown-header">{{ 'opciones de descarga' }}</span>
                                <a id="export-excel" class="dropdown-item"
                                    href="{{ route('admin.users.delivery-man.review-export', ['type' => 'excel', 'id' => $deliveryMan->id, request()->getQueryString()]) }}">
                                    <img class="avatar avatar-xss avatar-4by3 mr-2"
                                        src="{{ asset('assets/admin') }}/svg/components/excel.svg"
                                        alt="Image Description">
                                    {{ 'sobresalir' }}
                                </a>
                                <a id="export-csv" class="dropdown-item"
                                    href="{{ route('admin.users.delivery-man.review-export', ['type' => 'csv', 'id' => $deliveryMan->id, request()->getQueryString()]) }}">
                                    <img class="avatar avatar-xss avatar-4by3 mr-2"
                                        src="{{ asset('assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                        alt="Image Description">
                                    .{{ 'csv' }}
                                </a>
                            </div>
                        </div>
                        <!-- End Unfold -->
                    </div>
                </div>
                <!-- End Header -->

                <!-- New Table -->

                <div class="p-xxl-20 p-3">
                    <div class="card-body shadow-sm rounded p-0">
                        <div class="table-responsive datatable-custom">
                            <table id="datatable" class="table table-border table-thead-bordered table-nowrap card-table"
                                data-hs-datatables-options='{
                            "columnDefs": [{
                                "targets": [0, 3, 6],
                                "orderable": false
                            }],
                            "order": [],
                            "info": {
                            "totalQty": "#datatableWithPaginationInfoTotalQty"
                            },
                            "search": "#datatableSearch",
                            "entries": "#datatableEntries",
                            "pageLength": 25,
                            "isResponsive": false,
                            "isShowPaging": false,
                            "pagination": "datatablePagination"
                        }'>
                                <thead class="thead-light">
                                    <tr>
                                        <th class="border-0 fs-14">{{ 'SL' }}</th>
                                        <th class="border-0 fs-14">{{ 'ID de pedido' }}</th>
                                        <th class="border-0 fs-14">{{ 'Cliente' }}</th>
                                        <th class="border-0 fs-14">{{ 'Clasificación' }}</th>
                                        <th class="border-0 fs-14">{{ 'ID de revisión' }}</th>
                                        <th class="border-0 fs-14">{{ 'revisar' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($reviews as $k => $review)
                                        <tr>
                                            <td class="fs-14 text-dark">{{ $k + $reviews->firstItem() }}</td>
                                            <td>
                                                <a class="line--limit-1 fs-14 text-dark max-w--220px min-w-135px text-wrap"
                                                    href="{{ route('admin.order.all-details', ['id' => $review->order_id]) }}">{{ $review->order_id }}</a>
                                            </td>
                                            <td>
                                                @if ($review->customer)
                                                    <a class="d-flex align-items-center"
                                                        href="{{ route('admin.customer.view', [$review['user_id']]) }}">
                                                        <span
                                                            class="text-dark fs-14 line--limit-1 max-w--220px min-w-135px text-wrap">
                                                            {{ $review->customer ? $review->customer['f_name'] . ' ' . $review->customer['l_name'] : '' }}
                                                        </span>
                                                    </a>
                                                @else
                                                    {{ 'cliente no encontrado' }}
                                                @endif
                                            </td>
                                            <td>
                                                <div class="">
                                                    <div class="d-flex gap-1 align-items-center">
                                                        <span class="d-inline-block mt-half">{{ $review->rating }}</span>
                                                        <i class="tio-star text-warning"></i>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div
                                                    class="text-dark fs-14 line--limit-1 max-w--220px min-w-135px text-wrap">
                                                    {{ $review->id }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fs-14 line--limit-2 max-w-390 min-w-220 text-dark text-wrap">
                                                    {{ $review['comment'] }}
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- End Table -->
                        @if (count($reviews) !== 0)
                            <hr>
                        @endif
                        <div class="page-area">
                            {!! $reviews->links() !!}
                        </div>
                        @if (count($reviews) === 0)
                            <div class="empty--data">
                                <img src="{{ asset('assets/admin/svg/illustrations/sorry.svg') }}"
                                    alt="public">
                                <h5>
                                    {{ 'no se encontraron datos' }}
                                </h5>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    @endif

    </div>


    <div class="modal fade" id="work_switcher">
        <div class="modal-dialog modal-dialog-centered max-w-500px">
            <div class="modal-content">
                <div class="modal-header pr-3">
                    <button type="button" class="close border bg-modal-btn rounded-circle" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear text-light-gray"></span>
                    </button>
                </div>
                <div class="modal-body px-sm-4 px-3 pb-5 pt-0">
                    <div class="text-center">
                        <div>
                            <div class="text-center mb-20">
                                <img width="80"
                                    src="{{ asset('assets/admin/img/icons/deliveryman-type.png') }}"
                                    class="">
                                <h5 class="modal-title m-0"></h5>
                            </div>
                            <div class="text-center mb-4">
                                <h3 class="font-weight-normal text-dark">
                                    {{ 'Este repartidor está actualmente en' }} <br>
                                    <strong>{{ $deliveryMan->earning ? 'persona de libre dedicación' : 'basado en salario' }}</strong>
                                </h3>
                            </div>
                        </div>
                        <div class="bg-light2 rounded p-sm-4 p-3">
                            <p class="fs-14 mb-20 text-body">{{ '¿Quieres cambiar el tipo de entrega?' }}
                            </p>
                            <div class="btn--container justify-content-center p-0">
                                <a href="{{ route('admin.users.delivery-man.earning', ['id' => $deliveryMan->id, 'status' => $deliveryMan->earning ? 0 : 1]) }}"
                                    class="btn btn--primary min-w-120">
                                    {{ $deliveryMan->earning ? 'Cambiar a basado en salario' : 'Cambiar a autónomo' }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script>
        "use strict";
        $('.request-alert').on('click', function() {
            let url = $(this).data('url');
            let message = $(this).data('message');
            request_alert(url, message);
        })

        function request_alert(url, message) {
            Swal.fire({
                title: '{{ '¿está seguro?' }}',
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: '{{ 'No' }}',
                confirmButtonText: '{{ 'Sí' }}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    location.href = url;
                }
            })
        }
    </script>
@endpush
