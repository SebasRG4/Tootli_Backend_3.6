<!-- Page Header -->
        <div class="page-header">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                <div class="d-flex gap-2 mb-0">
                    <div class="page-header-icon">
                        <img src="{{ asset('assets/admin/img/delivery-man.png') }}" class="w--26" alt="">
                    </div>
                    <div>
                        <h1 class="page-header-title text-break mb-1">
                            <span class="text-dark">
                                {{ 'vista previa del repartidor' }}
                            </span>
                        </h1>

                        <p class="mb-0 fs-12">{{ 'Únete a' }} {{ \App\CentralLogics\Helpers::time_date_format($deliveryMan?->created_at) }}
                        </p>
                    </div>
                </div>

                @if ($deliveryMan?->application_status != 'approved')
                    <div class="btn-container">
                        <a class="btn btn-primary text-capitalize font-weight-medium fs-12" data-toggle="tooltip"
                            data-placement="top" data-original-title="{{ 'editar' }}"
                            href="{{ route('admin.users.delivery-man.edit', [$deliveryMan['id']]) }}">
                            <i class="tio-edit"></i>
                            {{ 'editar información' }}
                        </a>

                        @if ($deliveryMan?->application_status != 'denied')
                            <a class="btn btn-danger text-capitalize font-weight-medium request-alert fs-12"
                                data-url="{{ route('admin.users.delivery-man.application', [$deliveryMan['id'], 'denied']) }}"
                                data-message="{{ 'quieres rechazar esta solicitud' }}"
                                href="javascript:">
                                {{ 'rechazar' }}
                            </a>
                        @endif

                        <a class="btn btn-success text-capitalize font-weight-medium request-alert fs-12"
                            data-url="{{ route('admin.users.delivery-man.application', [$deliveryMan['id'], 'approved']) }}"
                            data-message="{{ 'quieres aprobar esta solicitud' }}"
                            href="javascript:">
                            {{ 'aprobar' }}
                        </a>
                    </div>
                @endif
            </div>