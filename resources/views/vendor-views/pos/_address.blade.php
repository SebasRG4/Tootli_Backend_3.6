@if (session()->has('address'))

@php
    $address = session()->get('address')
@endphp
<ul>
    <li>
        <span>{{ translate('messages.pos_contact_name') }}</span>
        <strong>{{ $address['contact_person_name'] }}</strong>
    </li>
    <li>
        <span>{{ translate('messages.pos_contact_phone') }}</span>
        <strong>{{ $address['contact_person_number'] }}</strong>
    </li>
</ul>
<div class="location">
    <i class="tio-poi"></i>
    <span>
        {{ $address['address'] }}
    </span>
</div>
@endif
