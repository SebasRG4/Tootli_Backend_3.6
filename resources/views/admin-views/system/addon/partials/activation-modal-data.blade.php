<div class="modal-header border-0 pb-0 d-flex justify-content-end">
    <button
        type="button"
        class="btn-close border-0"
        data-dismiss="modal"
        aria-label="Close"
    ><i class="tio-clear"></i></button>
</div>
<div class="modal-body px-4 px-sm-5">
    <div class="mb-4 text-center">
        @php($logo=\App\Models\BusinessSetting::where('key','logo')->first())
        <img
            width="200"
            src="{{\App\CentralLogics\Helpers::get_full_url('business', $logo?->value?? '', $logo?->storage[0]?->value ?? 'public','upload_image')}}"

            alt="image"
            class="dark-support onerror-image"  data-onerror-image="{{ asset('assets/admin/img/img1.jpg') }}" />
    </div>
    <h2 class="text-center mb-4">{{$addon_name}}</h2>

    <form action="{{route('admin.business-settings.system-addon.activation')}}" method="post" id="customer_login_modal" autocomplete="off">
        @csrf
        <div class="form-group mb-4">
            <label for="username">{{ 'Nombre de uso de Codecanyon' }}</label>
            <input
                name="username" id="username"
                class="form-control"
                placeholder="{{'Ej: Riad Uddin'}}" required
            />
        </div>
        <div class="form-group mb-6">
            <label for="purchase_code">{{ 'Código de compra' }}</label>
            <input
                name="purchase_code" id="purchase_code"
                class="form-control"
                placeholder="{{'Ej: 987652'}}" required
            />
            <input type="text" name="path" class="form-control" value="{{$path}}" hidden>
        </div>

        <div class="btn--container justify-content-center gap-3 mb-3">
            <button type="button" class="fs-16 btn btn-secondary flex-grow-1" data-dismiss="modal">{{ 'Cancelar' }}</button>
            <button type="submit" class="fs-16 btn btn--primary flex-grow-1">{{ 'Activar' }}</button>
        </div>
    </form>
</div>
