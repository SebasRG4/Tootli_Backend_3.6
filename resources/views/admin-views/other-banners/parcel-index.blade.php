@extends('layouts.admin.app')

@section('title','bandera')

@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <h1 class="page-header-title">
            <span class="page-header-icon">
                <img src="{{asset('assets/admin/img/3rd-party.png')}}" class="w--26" alt="">
            </span>
            <span>
                {{'Configuración de otro contenido promocional'}}
            </span>
        </h1>
    </div>
    <div class="mb-20 mt-2">
        <div class="js-nav-scroller hs-nav-scroller-horizontal">
            @include('admin-views.other-banners.partial.parcel-links')
        </div>
    </div>
    <div class="tab-content">
        <div class="tab-pane fade show active">
            <div class="row g-3">
                <div class="col-lg-12 mb-3 mb-lg-2">
                    <div class="card h-100">
                        <form action="{{ route('admin.promotional-banner.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="text" name="key" value="promotional_banner"  hidden>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-12 d-flex justify-content-between">
                                            <span class="d-flex g-1">
                                                <img src="{{asset('assets/admin/img/other-banner.png')}}" class="h-85" alt="">
                                                <h3 class="form-label d-block mb-2">
                                                    {{'pancartas promocionales'}}
                                                </h3>
                                            </span>
                                        </div>
                                        <div class="col-12">
                                            <label class="__upload-img aspect-4-1 m-auto d-block">
                                                <div class="img">
                                                    <img class="onerror-image"    src="{{asset('assets/admin/img/upload-placeholder.png')}}" data-onerror-image="{{asset('assets/admin/img/upload-placeholder.png')}}" alt="">




                                                </div>
                                                    <input type="file" name="image"  hidden>
                                            </label>
                                            <div class="text-center mt-5">
                                                <h3 class="form-label d-block mt-2">
                                                {{'Proporción de imagen de banner 4:1'}}
                                            </h3>
                                            <p>{{'formato de imagen: jpg, png, jpeg | tamaño máximo: 2 MB'}}</p>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="btn--container justify-content-end mt-20">
                                        <button type="submit" class="btn btn--primary mb-2">{{'Entregar'}}</button>
                                    </div>
                                </div>
                            </form>
                            @php($banners=\App\Models\ModuleWiseBanner::where('module_id',Config::get('module.current_module_id'))->where('key','promotional_banner')->get())
                            {{-- <div class="card"> --}}
                                <div class="card-header py-2">
                                    <div class="search--button-wrapper">
                                        <h5 class="card-title">{{'Lista de banners promocionales'}}
                                        </h5>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <!-- Table -->
                                    <div class="table-responsive datatable-custom">
                                        <table id="columnSearchDatatable"
                                                class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                                                data-hs-datatables-options='{
                                                    "order": [],
                                                    "orderCellsTop": true,
                                                    "paging":false

                                                }'>
                                            <thead class="thead-light">
                                            <tr>
                                                <th class="border-0">{{'SL'}}</th>
                                                <th class="border-0">{{'Imagen'}}</th>
                                                <th class="border-0">{{'Estado'}}</th>
                                                <th class="text-center border-0">{{'acción'}}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($banners as $key=>$banner)
                                                <tr>
                                                    <td>{{ $key+1 }}</td>
                                                    <td>
                                                        <img src="{{ $banner->value_full_url ?? asset('assets/admin/img/upload-3.png') }}" data-toggle="modal"
                                                             data-target="#imagemodal{{ $key }}"  data-onerror-image="{{asset('assets/admin/img/upload-3.png')}}" class="__size-105 onerror-image" alt="">
                                                        <div class="modal fade" id="imagemodal{{ $key }}" tabindex="-1"
                                                             role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h4 class="modal-title" id="myModalLabel">
                                                                            {{ 'bandera' }}</h4>
                                                                        <button type="button" class="close"
                                                                                data-dismiss="modal"><span
                                                                                aria-hidden="true">&times;</span><span
                                                                                class="sr-only">{{ 'Cancelar' }}</span></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <img src="{{ $banner->value_full_url ?? asset('assets/admin/img/upload-3.png') }}"
                                                                             class="initial--22 w-100">
                                                                    </div>
{{--                                                                    <div class="modal-footer">--}}
{{--                                                                        <a class="btn btn-primary"--}}
{{--                                                                           href="{{ route('admin.file-manager.download', base64_encode('public/promotional_banner/' . $banner->value ?? '')) }}"><i--}}
{{--                                                                                class="tio-download"></i>--}}
{{--                                                                            {{ 'descargar' }}--}}
{{--                                                                        </a>--}}
{{--                                                                    </div>--}}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <label class="toggle-switch toggle-switch-sm">
                                                            <input type="checkbox" class="toggle-switch-input dynamic-checkbox"
                                                                   data-id="status-{{$banner->id}}"
                                                                   data-type="status"
                                                                   data-image-on="{{asset('assets/admin/img/modal')}}/promotional-on.png"
                                                                   data-image-off="{{asset('assets/admin/img/modal')}}/promotional-off.png"
                                                                   data-title-on="{{'Al activar la sección de banner promocional ONN'}}"
                                                                   data-title-off="{{'Desactivando la sección de banners promocionales'}}"
                                                                   data-text-on="<p>{{'Se habilitará el banner promocional. Podrás ver la actividad promocional.'}}</p>"
                                                                   data-text-off="<p>{{'El banner promocional estará deshabilitado. No podrás ver la actividad promocional.'}}</p>"
                                                                   id="status-{{$banner->id}}" {{$banner->status?'checked':''}}>
                                                            <span class="toggle-switch-label">
                                                                <span class="toggle-switch-indicator"></span>
                                                            </span>
                                                        </label>
                                                        <form action="{{route('admin.promotional-banner.update-status',[$banner->id,$banner->status?0:1])}}" method="get" id="status-{{$banner->id}}_form">
                                                        </form>
                                                    </td>

                                                    <td>
                                                        <div class="btn--container justify-content-center">
                                                            <a class="btn action-btn btn--primary btn-outline-primary" href="{{route('admin.promotional-banner.edit',[$banner['id']])}}">
                                                                <i class="tio-edit"></i>
                                                            </a>
                                                            <a class="btn action-btn btn--danger btn-outline-danger form-alert" href="javascript:"
                                                               data-id="banner-{{$banner['id']}}" data-message="{{ '¿Quieres eliminar este banner?' }}"
                                                             title="{{'eliminar banner'}}"><i class="tio-delete-outlined"></i>
                                                            </a>
                                                            <form action="{{route('admin.promotional-banner.delete',[$banner['id']])}}" method="post" id="banner-{{$banner['id']}}">
                                                                @csrf @method('delete')
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>

                                    </div>
                                    <!-- End Table -->
                                </div>
                                @if(count($banners) === 0)
                                <div class="empty--data">
                                    <img src="{{asset('assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                                    <h5>
                                        {{'no se encontraron datos'}}
                                    </h5>
                                </div>
                                @endif
                            {{-- </div> --}}
                        </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('script_2')
    <script src="{{asset('assets/admin')}}/js/view-pages/other-banners.js"></script>
        <script>
            $('#reset_btn').click(function(){
                $('#viewer').attr('src','{{asset('assets/admin/img/upload-placeholder.png')}}');
            })
        </script>
@endpush

