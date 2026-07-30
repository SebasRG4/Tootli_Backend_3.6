@if (session()->has('address'))
@php $address = session()->get('address') @endphp
<div class="pos--saved-address w-100">
    <ul class="list-unstyled mb-1">
        <li class="mb-1">
            <span class="text-muted small">{{ 'nombre de contacto pos' }}:</span>
            <strong class="ml-1">{{ $address['contact_person_name'] }}</strong>
        </li>
        <li>
            <span class="text-muted small">{{ 'teléfono de contacto pos' }}:</span>
            <strong class="ml-1">{{ $address['contact_person_number'] }}</strong>
        </li>
    </ul>
    <div class="location d-flex align-items-start mb-2">
        <i class="tio-poi mt-1 mr-1 text-muted"></i>
        <span class="small">{{ $address['address'] }}</span>
    </div>
    <button type="button"
        class="btn btn-sm btn-outline-secondary pos--new-address-btn"
        data-toggle="modal"
        data-target="#paymentModal">
        <i class="tio-add-circle-outlined mr-1"></i>{{ 'pos nueva dirección' }}
    </button>
</div>
@else
<span class="text-muted small">{{ 'pos no hay dirección todavía' }}</span>
@endif
