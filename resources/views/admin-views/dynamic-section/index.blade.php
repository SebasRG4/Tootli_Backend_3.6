@extends('layouts.admin.app')

@section('title', translate('messages.dynamic_sections'))

@push('css_or_js')
    <link href="{{asset('assets/admin/css/select2.min.css')}}" rel="stylesheet" />
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/banner.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{translate('messages.dynamic_sections')}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->

        <!-- Create Form -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title">
                    <span class="card-header-icon">
                        <i class="tio-add-circle"></i>
                    </span>
                    <span>{{translate('messages.add_new_section')}}</span>
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.dynamic-section.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="title">{{translate('messages.title')}} <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title"
                                    placeholder="{{translate('messages.enter_title')}}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="subtitle">{{translate('messages.subtitle')}}</label>
                                <input type="text" class="form-control" id="subtitle" name="subtitle"
                                    placeholder="{{translate('messages.enter_subtitle')}}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{translate('messages.background_image')}}</label>
                                <label class="__upload-img aspect-4-1 m-auto d-block">
                                    <div class="img">
                                        <img class="onerror-image" id="viewer"
                                            src="{{asset('assets/admin/img/upload-placeholder.png')}}"
                                            data-onerror-image="{{asset('assets/admin/img/upload-placeholder.png')}}"
                                            alt="">
                                    </div>
                                    <input type="file" name="background_image" accept="image/*" hidden
                                        onchange="document.getElementById('viewer').src = window.URL.createObjectURL(this.files[0])">
                                </label>
                                <p class="text-center mt-2">{{translate('messages.image_ratio_4:1')}}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{translate('messages.select_items')}}</label>
                                <select name="items[]" class="form-control select2-items" multiple
                                    data-placeholder="{{translate('messages.select_items')}}">
                                    @foreach(\App\Models\Item::where('module_id', Config::get('module.current_module_id'))->where('status', 1)->select('id', 'name')->get() as $item)
                                        <option value="{{$item->id}}">{{$item->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="toggle-switch toggle-switch-sm">
                                    <input type="checkbox" class="toggle-switch-input" name="status" checked>
                                    <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                    <span class="ml-2">{{translate('messages.status')}}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end">
                        <button type="reset" class="btn btn--reset">{{translate('messages.reset')}}</button>
                        <button type="submit" class="btn btn--primary">{{translate('messages.submit')}}</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sections List -->
        <div class="card">
            <div class="card-header border-0 py-2">
                <h5 class="card-title">
                    {{translate('messages.sections_list')}} <span
                        class="badge badge-soft-dark ml-2">{{$sections->total()}}</span>
                </h5>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-borderless table-thead-bordered table-align-middle">
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0">{{translate('messages.sl')}}</th>
                                <th class="border-0">{{translate('messages.title')}}</th>
                                <th class="border-0">{{translate('messages.subtitle')}}</th>
                                <th class="border-0">{{translate('messages.items_count')}}</th>
                                <th class="border-0">{{translate('messages.status')}}</th>
                                <th class="border-0 text-center">{{translate('messages.action')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sections as $key => $section)
                                <tr>
                                    <td>{{$sections->firstItem() + $key}}</td>
                                    <td>
                                        <span class="font-weight-semibold">{{$section->title}}</span>
                                    </td>
                                    <td>{{$section->subtitle ?? '-'}}</td>
                                    <td>
                                        <span class="badge badge-soft-info">{{$section->items_count}}
                                            {{translate('messages.items')}}</span>
                                    </td>
                                    <td>
                                        <label class="toggle-switch toggle-switch-sm">
                                            <input type="checkbox" class="toggle-switch-input change-status"
                                                data-url="{{route('admin.dynamic-section.status', $section->id)}}"
                                                {{$section->status ? 'checked' : ''}}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </td>
                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a class="btn action-btn btn--primary btn-outline-primary"
                                                href="{{route('admin.dynamic-section.edit', $section->id)}}"
                                                title="{{translate('messages.edit')}}">
                                                <i class="tio-edit"></i>
                                            </a>
                                            <a class="btn action-btn btn--danger btn-outline-danger delete-btn"
                                                href="javascript:"
                                                data-url="{{route('admin.dynamic-section.delete', $section->id)}}"
                                                title="{{translate('messages.delete')}}">
                                                <i class="tio-delete-outlined"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <img src="{{asset('assets/admin/img/empty-table.png')}}" alt="" class="mb-3"
                                            style="width: 100px;">
                                        <p class="text-muted">{{translate('messages.no_data_found')}}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="page-area mt-3">
                    {!! $sections->links() !!}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script src="{{asset('assets/admin/js/select2.min.js')}}"></script>
    <script>
        $(document).ready(function () {
            $('.select2-items').select2({
                placeholder: "{{translate('messages.select_items')}}",
                allowClear: true
            });

            // Status change
            $('.change-status').on('change', function () {
                let url = $(this).data('url');
                $.get(url, function (response) {
                    toastr.success("{{translate('messages.status_updated')}}");
                });
            });

            // Delete
            $('.delete-btn').on('click', function () {
                let url = $(this).data('url');
                Swal.fire({
                    title: "{{translate('messages.are_you_sure')}}",
                    text: "{{translate('messages.you_wont_be_able_to_revert_this')}}",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#FC6A57',
                    cancelButtonColor: '#363636',
                    confirmButtonText: "{{translate('messages.yes_delete_it')}}"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            data: { _token: '{{csrf_token()}}' },
                            success: function () {
                                location.reload();
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush