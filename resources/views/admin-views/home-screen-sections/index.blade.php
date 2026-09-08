@extends('layouts.admin.app')

@section('title', 'secciones de la pantalla de inicio')

@push('css_or_js')
    <style>
        .sortable-row {
            cursor: grab;
            transition: background 0.2s;
        }

        .sortable-row:active {
            cursor: grabbing;
        }

        .drag-handle {
            cursor: grab;
            color: #9CA3AF;
            font-size: 18px;
            padding: 0 8px;
        }

        .drag-handle:hover {
            color: #4B5563;
        }

        .ui-sortable-helper {
            background: #fff !important;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
            border-radius: 6px;
        }

        .ui-sortable-placeholder {
            height: 55px;
            background: #E8F5E9 !important;
            border: 2px dashed #4CAF50;
            visibility: visible !important;
        }

        .section-key-badge {
            font-family: monospace;
            font-size: 12px;
            padding: 2px 8px;
            border-radius: 4px;
            background: #F3F4F6;
            color: #6B7280;
        }

        /* Phone Simulator CSS */
        .phone-mockup {
            width: 320px;
            height: 650px;
            border: 12px solid #1f2937;
            border-radius: 40px;
            margin: 0 auto;
            position: relative;
            background: #f9fafb;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .phone-notch {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            height: 25px;
            background: #1f2937;
            border-bottom-left-radius: 16px;
            border-bottom-right-radius: 16px;
            z-index: 10;
        }
        .phone-header {
            background: #fff;
            padding: 35px 15px 15px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .phone-header-fake-search {
            background: #f3f4f6;
            height: 30px;
            border-radius: 15px;
            flex-grow: 1;
            margin-right: 10px;
        }
        .phone-screen {
            flex-grow: 1;
            overflow-y: auto;
            padding: 10px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .phone-screen::-webkit-scrollbar {
            display: none;
        }
        /* High Fidelity Mocks */
        .hf-section { margin-bottom: 15px; }
        .hf-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .hf-title { font-size: 14px; font-weight: 700; color: #111827; }
        .hf-subtitle { font-size: 11px; color: #007AFF; font-weight: 600; }
        
        .hf-banner-img { width: 100%; height: 120px; background: #e5e7eb; border-radius: 12px; }
        
        .hf-categories { display: flex; gap: 12px; overflow: hidden; padding-bottom: 5px; }
        .hf-cat-item { display: flex; flex-direction: column; align-items: center; gap: 6px; }
        .hf-cat-circle { width: 55px; height: 55px; border-radius: 28px; background: #f3f4f6; }
        .hf-cat-text { width: 45px; height: 6px; background: #e5e7eb; border-radius: 3px; }
        
        .hf-cards { display: flex; gap: 10px; overflow: hidden; }
        .hf-card { width: 130px; min-width: 130px; background: #fff; border-radius: 12px; padding: 10px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); border: 1px solid #f3f4f6; position: relative; }
        .hf-card-img { width: 100%; height: 90px; background: #f3f4f6; border-radius: 8px; margin-bottom: 10px; }
        .hf-card-line-1 { width: 85%; height: 8px; background: #d1d5db; border-radius: 4px; margin-bottom: 8px; }
        .hf-card-line-2 { width: 50%; height: 12px; background: #111827; border-radius: 6px; }
        .hf-card-add { position: absolute; bottom: 10px; right: 10px; width: 26px; height: 26px; background: #10b981; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 16px; font-weight: bold; }
        .hf-card-add::before { content: '+'; }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/banner.png')}}" class="w--26" alt="">
                </span>
                <span>{{'secciones de la pantalla de inicio'}}</span>
            </h1>
            <p class="text-muted mt-1">
                {{'arrastre secciones para cambiar el orden'}}
            </p>
        </div>

        <!-- Sections List & Phone Simulator -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header border-0 py-2">
                        <h5 class="card-title">
                            {{'secciones'}}
                            <span class="badge badge-soft-dark ml-2">{{count($sections)}}</span>
                        </h5>
                        <small class="text-muted"><i class="tio-drag"></i> {{'arrastrar para reordenar'}}</small>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-borderless table-thead-bordered table-align-middle">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="border-0" style="width: 50px;"></th>
                                        <th class="border-0" style="width: 60px;">{{'SL'}}</th>
                                        <th class="border-0">{{'sección'}}</th>
                                        <th class="border-0">{{'llave'}}</th>
                                        <th class="border-0 text-center">{{'estado'}}</th>
                                    </tr>
                                </thead>
                                <tbody id="sortable-sections">
                                    @foreach($sections as $key => $section)
                                        <tr class="sortable-row" data-id="{{$section->id}}" data-key="{{$section->key}}" data-title="{{translate('messages.' . $section->title)}}">
                                            <td>
                                                <span class="drag-handle"><i class="tio-drag"></i></span>
                                            </td>
                                            <td class="sl-number">{{$key + 1}}</td>
                                            <td>
                                                <span class="font-weight-semibold">{{translate('messages.' . $section->title)}}</span>
                                            </td>
                                            <td>
                                                <span class="section-key-badge">{{$section->key}}</span>
                                            </td>
                                            <td class="text-center">
                                                <label class="toggle-switch toggle-switch-sm">
                                                    <input type="checkbox" class="toggle-switch-input change-status status-checkbox"
                                                        data-url="{{route('admin.home-screen-sections.status', $section->id)}}"
                                                        {{$section->status ? 'checked' : ''}}>
                                                    <span class="toggle-switch-label">
                                                        <span class="toggle-switch-indicator"></span>
                                                    </span>
                                                </label>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 mt-4 mt-lg-0">
                <div class="phone-mockup">
                    <div class="phone-notch"></div>
                    <div class="phone-header">
                        <div class="phone-header-fake-search"></div>
                        <i class="tio-shopping-cart-outlined text-muted" style="font-size: 20px;"></i>
                    </div>
                    <div class="phone-screen" id="phone-screen-container">
                        <!-- Dynamic blocks will be injected here by JS -->
                        <div id="sim-dynamic-blocks" class="d-flex flex-column" style="gap: 10px;">
                        </div>
                        
                        <div style="height: 50px;"></div> <!-- Bottom padding -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script>
        $(document).ready(function () {
            // Phone Simulator Sync Logic
            function syncSimulator() {
                let $simContainer = $('#sim-dynamic-blocks');
                $simContainer.empty(); // clear existing
                
                $('#sortable-sections tr.sortable-row').each(function () {
                    let key = $(this).data('key');
                    let title = $(this).data('title');
                    let isActive = $(this).find('.status-checkbox').is(':checked');
                    
                    if (isActive) {
                        let html = '';
                        if(key === 'banner' || key === 'promotional_banner' || key === 'middle_banner') {
                            html = `<div class="hf-section"><div class="hf-header"><div class="hf-title">${title}</div></div><div class="hf-banner-img" style="height: 100px;"></div></div>`;
                        } else if(key === 'promo_code') {
                            html = `<div class="hf-section"><div class="hf-banner-img" style="height: 60px; background: #fef08a;"></div></div>`;
                        } else if(key === 'categories' || key === 'category') {
                            html = `<div class="hf-section">
                                <div class="hf-categories">
                                    <div class="hf-cat-item"><div class="hf-cat-circle"></div><div class="hf-cat-text"></div></div>
                                    <div class="hf-cat-item"><div class="hf-cat-circle"></div><div class="hf-cat-text"></div></div>
                                    <div class="hf-cat-item"><div class="hf-cat-circle"></div><div class="hf-cat-text"></div></div>
                                    <div class="hf-cat-item"><div class="hf-cat-circle"></div><div class="hf-cat-text"></div></div>
                                    <div class="hf-cat-item"><div class="hf-cat-circle"></div><div class="hf-cat-text"></div></div>
                                </div>
                            </div>`;
                        } else {
                            html = `<div class="hf-section">
                                <div class="hf-header">
                                    <div class="hf-title">${title}</div>
                                    <div class="hf-subtitle">Ver todo</div>
                                </div>
                                <div class="hf-cards">
                                    <div class="hf-card">
                                        <div class="hf-card-img"></div>
                                        <div class="hf-card-line-1"></div>
                                        <div class="hf-card-line-2"></div>
                                        <div class="hf-card-add"></div>
                                    </div>
                                    <div class="hf-card">
                                        <div class="hf-card-img"></div>
                                        <div class="hf-card-line-1"></div>
                                        <div class="hf-card-line-2"></div>
                                        <div class="hf-card-add"></div>
                                    </div>
                                    <div class="hf-card" style="width: 30px; min-width: 30px; padding: 10px 0 10px 10px; border-top-right-radius: 0; border-bottom-right-radius: 0; border-right: none;">
                                        <div class="hf-card-img" style="border-top-right-radius: 0; border-bottom-right-radius: 0;"></div>
                                    </div>
                                </div>
                            </div>`;
                        }
                        
                        let $block = $(html).attr('data-key', key);
                        $simContainer.append($block);
                    }
                });
            }

            // Initial Sync
            syncSimulator();

            // Drag & drop sortable
            $('#sortable-sections').sortable({
                handle: '.drag-handle',
                placeholder: 'ui-sortable-placeholder',
                axis: 'y',
                update: function (event, ui) {
                    syncSimulator(); // sync immediately on drop
                    
                    let sections = [];
                    $('#sortable-sections tr.sortable-row').each(function () {
                        sections.push($(this).data('id'));
                    });

                    $.ajax({
                        url: "{{ route('admin.home-screen-sections.priority') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            sections: sections
                        },
                        success: function (response) {
                            toastr.success("{{'pedido actualizado exitosamente'}}");
                            // Update SL numbers
                            $('#sortable-sections tr.sortable-row').each(function (index) {
                                $(this).find('.sl-number').text(index + 1);
                            });
                        },
                        error: function () {
                            toastr.error("{{'no se pudo actualizar el pedido'}}");
                            location.reload();
                        }
                    });
                }
            });

            // Status toggle
            $('.change-status').on('change', function () {
                syncSimulator(); // sync immediately on toggle
                
                let url = $(this).data('url');
                $.get(url, function () {
                    toastr.success("{{'estado actualizado'}}");
                });
            });
        });
    </script>
@endpush