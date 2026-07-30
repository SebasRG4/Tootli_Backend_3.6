    <!-- Page Header -->
    <div class="page-header pb-0">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-20">
            <div>
                <h1 class="page-header-title text-break fs-24 m-0">
                    <span class="page-header-icon">
                        <img src="{{ asset('assets/admin/img/store-new.png') }}" class="w--26" alt="">
                    </span>
                    <span>{{ $store->name }}</span>
                </h1>
            </div>
            <div class="d-flex align-items-center flex-wrap gap-3">

                <a href=" {{ !isset($store->vendor->status) || $store->vendor->status == 0 ? route('admin.store.edit', [$store->id, 'pending'=>1]) : route('admin.store.edit', [$store->id]) }}"
                    class="btn btn--primary border-0 bg--soft-priamry-10 text-primary m-0 float-right">
                    <i class="tio-edit"></i> {{  $store->module_type == 'rental' ? 'Editar proveedor' :'Editar tienda' }}
                </a>
                @if (!isset($store->vendor->status) || $store->vendor->status == 0)
                    @if (!isset($store->vendor->status))
                        <a class="btn btn--danger border-0 bg--soft-danger-10 text-danger m-0 text-capitalize font-weight-bold float-right "
                            href="javascript:" data-toggle="modal" data-target="#confirmation-reason-btn"><i
                                class="tio-clear font-weight-bold pr-1"></i> {{ 'Rechazar' }}</a>
                    @endif
                    <a class="btn btn--primary border-0 m-0 text-capitalize font-weight-bold float-right swal_fire_alert"
                        data-url="{{ route('admin.store.application', [$store['id'], 1]) }}"
                         data-title="{{'está seguro ?'}}"
                                       data-image_url="{{ asset('assets/admin/img/off-danger.png') }}"
                                       data-confirm_button_text="{{ 'Sí' }}"
                                       data-cancel_button_text="{{ 'No' }}"
                                       data-message="{{'desea aprobar la solicitud de incorporación del proveedor.'}}"
                        href="javascript:"><i
                            class="tio-done font-weight-bold pr-1"></i>{{ 'aprobar' }}</a>
                @endif
            </div>
        </div>
        @if ($store->vendor->status)
            <!-- Nav Scroller -->
            <div class="js-nav-scroller hs-nav-scroller-horizontal">
                <span class="hs-nav-scroller-arrow-prev d-none">
                    <a class="hs-nav-scroller-arrow-link" href="javascript:;">
                        <i class="tio-chevron-left"></i>
                    </a>
                </span>

                <span class="hs-nav-scroller-arrow-next d-none">
                    <a class="hs-nav-scroller-arrow-link" href="javascript:;">
                        <i class="tio-chevron-right"></i>
                    </a>
                </span>

                <!-- Nav -->
                <ul class="nav nav-tabs page-header-tabs mb-2">
                    <li class="nav-item">
                        <a class="nav-link {{ request('tab') == null ? 'active' : '' }}"
                            href="{{ route('admin.store.view', $store->id) }}">{{ 'descripción general' }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('tab') == 'order' ? 'active' : '' }}"
                            href="{{ route('admin.store.view', ['store' => $store->id, 'tab' => 'order']) }}"
                            aria-disabled="true">{{ 'Pedidos' }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('tab') == 'item' ? 'active' : '' }}"
                            href="{{ route('admin.store.view', ['store' => $store->id, 'tab' => 'item']) }}"
                            aria-disabled="true">{{ 'Productos' }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('tab') == 'reviews' ? 'active' : '' }}"
                            href="{{ route('admin.store.view', ['store' => $store->id, 'tab' => 'reviews']) }}"
                            aria-disabled="true">{{ 'opiniones' }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('tab') == 'discount' ? 'active' : '' }}"
                            href="{{ route('admin.store.view', ['store' => $store->id, 'tab' => 'discount']) }}"
                            aria-disabled="true">{{ 'descuentos' }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('tab') == 'transaction' ? 'active' : '' }}"
                            href="{{ route('admin.store.view', ['store' => $store->id, 'tab' => 'transaction']) }}"
                            aria-disabled="true">{{ 'actas' }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('tab') == 'settings' ? 'active' : '' }}"
                            href="{{ route('admin.store.view', ['store' => $store->id, 'tab' => 'settings']) }}"
                            aria-disabled="true">{{ 'Configuración' }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('tab') == 'conversations' ? 'active' : '' }}"
                            href="{{ route('admin.store.view', ['store' => $store->id, 'tab' => 'conversations']) }}"
                            aria-disabled="true">{{ 'Conversaciones' }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('tab') == 'meta-data' ? 'active' : '' }}"
                            href="{{ route('admin.store.view', ['store' => $store->id, 'tab' => 'meta-data']) }}"
                            aria-disabled="true">{{ 'metadatos' }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link  {{ request('tab') == 'disbursements' ? 'active' : '' }}"
                            href="{{ route('admin.store.view', ['store' => $store->id, 'tab' => 'disbursements']) }}"
                            aria-disabled="true">{{ 'desembolsos' }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link  {{ request('tab') == 'business_plan' ? 'active' : '' }}"
                            href="{{ route('admin.store.view', ['store' => $store->id, 'tab' => 'business_plan']) }}"
                            aria-disabled="true">{{ 'plan de negocios' }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link  {{ request('tab') == 'tootliclick' ? 'active' : '' }}"
                            href="{{ route('admin.store.view', ['store' => $store->id, 'tab' => 'tootliclick']) }}"
                            aria-disabled="true">{{ 'TootliClic' }}</a>
                    </li>
                </ul>
                <!-- End Nav -->
            </div>
            <!-- End Nav Scroller -->
        @endif
    </div>
    <!-- End Page Header -->


    <!-- Confiramtion Reason Modal -->
    <div class="modal shedule-modal fade" id="confirmation-reason-btn" tabindex="-1"
        aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content pb-2 max-w-500">
                <form action="{{ route('admin.store.application', [$store['id'], 0]) }}" method="get">
                <div class="modal-header">
                    <button type="button"
                        class="close bg-modal-btn w-30px h-30 rounded-circle position-absolute right-0 top-0 m-2 z-2"
                        data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <img src="{{ asset('assets/admin/img/delete-confirmation.png') }}" alt="icon"
                            class="mb-3">
                        <h3 class="mb-2">{{ 'Está seguro ?' }}</h3>
                        <p class="mb-0">{{ '¿Quiere rechazar esta solicitud de incorporación?' }}</p>
                    </div>
                    <div class="px-3 mt-4">
                        <h5 class="mb-2">{{ 'Razón' }}</h5>
                        <textarea name="rejection_note" id="" class="form-control" rows="2" required
                            placeholder="{{ 'Escriba aquí el motivo del rechazo...' }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer justify-content-center border-0 pt-0 gap-2">
                    <button type="button" class="btn min-w-120px btn--reset" data-dismiss="modal">{{ 'No' }}</button>
                    <button type="submit" class="btn min-w-120px btn--primary">{{ 'Sí' }}</button>
                </div>
            </form>
            </div>
        </div>
    </div>
