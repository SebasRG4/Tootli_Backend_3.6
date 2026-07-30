<div>
    <select id="mail-route-selector" class="custom-select w-auto min-width-170px">
        <option value="admin" {{ Request::is('admin/business-settings/email-setup/admin*') ? 'selected' : '' }}><a href="https://support.6amtech.com/">{{ 'Plantillas de correo de administrador' }}</a></option>
        <option value="store" {{ Request::is('admin/business-settings/email-setup/store*') ? 'selected' : '' }}><a href="https://support.6amtech.com/">{{ 'Almacenar plantillas de correo' }}</a></option>
        <option value="dm" {{ Request::is('admin/business-settings/email-setup/dm*') ? 'selected' : '' }}><a href="https://support.6amtech.com/">{{ 'Plantillas de correo de repartidor' }}</a></option>
        <option value="user" {{ Request::is('admin/business-settings/email-setup/user*') ? 'selected' : '' }}><a href="https://support.6amtech.com/">{{ 'Plantillas de correo para clientes' }}</a></option>
    </select>
    <div class="d-flex justify-content-end mt-2">
        <div class="text--primary-2 py-1 d-flex flex-wrap align-items-center" type="button"   id="see-how-it-works"  >
            <strong class="mr-2">{{'¡Mira cómo funciona!'}}</strong>
            <div>
                <i class="tio-info-outined"></i>
            </div>
        </div>
    </div>
</div>
