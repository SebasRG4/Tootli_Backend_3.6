@extends('layouts.admin.app')

@section('title', translate('Order Products'))

@push('css_or_js')
    <style>
        .sortable-item {
            cursor: move;
            background: #fff;
            border: 1px solid #eee;
            margin-bottom: 8px;
            padding: 12px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.2s;
        }
        .sortable-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .sortable-placeholder {
            border: 2px dashed #ccc;
            background: #f9f9f9;
            height: 50px;
            margin-bottom: 8px;
            border-radius: 8px;
        }
        .category-container {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        .category-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 15px;
            color: #334257;
            border-bottom: 2px solid #e7eaf3;
            padding-bottom: 10px;
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon"><i class="tio-format-bullets"></i></span>
                {{ translate('Organizar Menú (TootliClick)') }}
            </h1>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('admin.item.reorder') }}" method="GET">
                    <div class="row align-items-end g-2">
                        <div class="col-sm-8">
                            <label class="input-label">{{ translate('Selecciona un Restaurante') }}</label>
                            <select name="store_id" class="form-control js-select2-custom" onchange="this.form.submit()">
                                <option value="">--- {{ translate('Seleccionar') }} ---</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}" {{ isset($selected_store) && $selected_store->id == $store->id ? 'selected' : '' }}>
                                        {{ $store->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <button type="submit" class="btn btn-primary btn-block">{{ translate('Cargar Productos') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if(isset($selected_store))
            <div class="alert alert-soft-info mb-4">
                <i class="tio-info-outined mr-1"></i>
                {{ translate('Arrastra los productos para cambiar su orden en TootliClick. Los cambios se guardan automáticamente.') }}
            </div>

            @foreach($items_by_category as $categoryName => $items)
                <div class="category-container">
                    <div class="category-title">{{ $categoryName }}</div>
                    <div class="sortable-list" data-category="{{ $categoryName }}">
                        @foreach($items as $item)
                            <div class="sortable-item" data-id="{{ $item->id }}">
                                <div class="handle">
                                    <i class="tio-move-up-down"></i>
                                </div>
                                <img src="{{ $item->image_full_url }}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;" onerror="this.src='{{ asset('assets/admin/img/160x160/img2.jpg') }}'">
                                <div class="flex-grow-1">
                                    <span class="font-weight-bold">{{ $item->name }}</span>
                                    <div class="text-muted small">${{ number_format($item->price, 2) }}</div>
                                </div>
                                <div class="badge badge-soft-secondary">
                                    ID: {{ $item->id }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </div>
@endsection

@push('script_2')
    <script src="https://code.jquery.com/ui/1.13.0/jquery-ui.min.js"></script>
    <script>
        $(document).ready(function() {
            $(".sortable-list").sortable({
                placeholder: "sortable-placeholder",
                handle: ".handle",
                update: function(event, ui) {
                    let order = [];
                    $(this).children(".sortable-item").each(function() {
                        order.push($(this).data('id'));
                    });

                    $.ajax({
                        url: "{{ route('admin.item.update-reorder') }}",
                        method: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            order: order
                        },
                        success: function(response) {
                            toastr.success(response.message);
                        },
                        error: function() {
                            toastr.error("{{ translate('Error al guardar el orden') }}");
                        }
                    });
                }
            });
        });
    </script>
@endpush
