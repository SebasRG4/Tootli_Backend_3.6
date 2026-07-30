@extends('layouts.vendor.app')

@section('title', 'Configuración de notificación')
@section('content')
    <div class="content container-fluid">

        <!-- Title -->
        <div class="mb-3 d-flex align-items-start gap-2">
            <img src="{{asset('assets/admin/img/bell-2.png')}}" alt="">
            <div class="w-0 flex-grow mb-2">
                 <h1 class="page-header-title m-0">{{ 'Configuración de notificación' }}</h1>
                {{ 'Desde aquí usted configura qué tipos de notificaciones puede recibir' }} {{ $business_name }}
            </div>
        </div>

        <div class="card">

            <div class="card-body p-0">
                <div class="table-responsive datatable-custom">
                    <table class="font-size-sm table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                        <tr>
                            <th>{{ 'SL' }}</th>
                            <th >{{'Temas'}}</th>
                            <th >{{'Notificación push'}}</th>
                            <th  >{{'Correo'}}</th>
                            <th class="text-center">{{'SMS'}}</th>
                        </tr>
                        </thead>

                        <tbody>
                        @foreach ($data as  $key => $item)
                            @php($item_admin_data = \App\CentralLogics\Helpers::getNotificationStatusDataAdmin($module_type == 'rental' ? 'provider' : 'store',$item->key))
                            <tr>
                                <td>{{ $key +1 }}</td>
                                <td>
                                    <h5 class="text-capitalize">{{ translate($item->title) }}</h5>
                                    <div class="white-space-initial max-w-400px">
                                        {{ translate($item->sub_title) }}
                                    </div>
                                </td>
                                <td>
                                    @if ($item_admin_data->push_notification_status == 'disable')
                                        <span class="badge badge-pill badge--info pr-6">  {{ 'N / A' }}</span>
                                    @elseif($item_admin_data->push_notification_status == 'inactive')
                                        <label class="toggle-switch toggle-switch-sm" data-toggle="tooltip" title="{{ 'Esta notificación fue desactivada por el administrador.'  }}">
                                            <input type="checkbox"
                                                    class="status toggle-switch-input dynamic-checkbox"  disabled>
                                            <span class="toggle-switch-label text">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    @else

                                        <label class="toggle-switch toggle-switch-sm" data-toggle="tooltip"  @if ($item->push_notification_status  == 'active')
                                            title="{{ 'Desactivar las notificaciones automáticas para' .' '.translate($item->title)  }}"
                                        @else
                                            title="{{ 'Activar notificaciones push para' .' '.translate($item->title)  }}"
                                        @endif >
                                            <input type="checkbox"
                                                   id="push_notification_{{$item->key}}"
                                                   data-id="push_notification_{{$item->key}}"
                                                   data-type="toggle" data-image-on="{{asset('assets/admin/img/modal/mail-success.png')}}" data-image-off="{{asset('assets/admin/img/modal/mail-warning.png')}}" data-title-on="{{ 'Quiere habilitar la notificación push para' .' '.  translate($item->title) }} ?" data-title-off="{{ 'Quiere deshabilitar la notificación push para' .' '.  translate($item->title) }} ?" data-text-on="<p>{{ 'La notificación push estará habilitada para'  .' '.  translate($item->title) }}</p>" data-text-off="<p>{{ 'La notificación push se desactivará durante'  .' '.  translate($item->title) }}</p>" class="status toggle-switch-input dynamic-checkbox"  {{ $item->push_notification_status  == 'active' ? 'checked' : '' }}>
                                            <span class="toggle-switch-label text">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                        <form action="{{route('vendor.business-settings.notification_status_change',['key'=> $item->key  ,'type' => 'push_notification'])}}" method="get" id="push_notification_{{$item->key}}_form">
                                        </form>
                                    @endif
                                </td>

                                <td>
                                    @if ($item_admin_data->mail_status == 'disable')
                                        <span class="badge badge-pill badge--info pr-6">  {{ 'N / A' }}</span>
                                    @elseif($item_admin_data->mail_status == 'inactive')
                                        <label class="toggle-switch toggle-switch-sm" data-toggle="tooltip" title="{{ 'Este correo fue desactivado por el administrador.' }}">
                                            <input type="checkbox"
                                                   class="status toggle-switch-input dynamic-checkbox"  disabled>
                                            <span class="toggle-switch-label text">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    @else

                                        <label class="toggle-switch toggle-switch-sm" data-toggle="tooltip"
                                        @if ($item->mail_status  == 'active')
                                            data-toggle="tooltip" title="{{ 'Desactivar el correo para' .' '.translate($item->title)  }}"
                                            @else
                                            data-toggle="tooltip" title="{{ 'Activar el correo para' .' '.translate($item->title)  }}"
                                            @endif>

                                            <input type="checkbox" data-type="toggle"
                                                   id="mail_{{ $item->key }}"
                                                   data-id="mail_{{ $item->key }}"
                                                   data-image-on="{{asset('assets/admin/img/modal/mail-success.png')}}" data-image-off="{{asset('assets/admin/img/modal/mail-warning.png')}}" data-title-on="{{ 'Quiere habilitar el correo para' .' '.  translate($item->title) }} ?" data-title-off="{{ 'Quiere desactivar el correo para' .' '.  translate($item->title) }} ?" data-text-on="<p>{{ 'El correo estará habilitado para'  .' '.  translate($item->title) }}</p>" data-text-off="<p>{{ 'El correo se desactivará durante'  .' '.  translate($item->title) }}</p>" class="status toggle-switch-input dynamic-checkbox" {{ $item->mail_status  == 'active' ? 'checked' : '' }}>
                                            <span class="toggle-switch-label text">
                                               <span class="toggle-switch-indicator"></span>
                                           </span>
                                        </label>
                                        <form action="{{route('vendor.business-settings.notification_status_change',['key'=> $item->key  ,'type' => 'Mail'])}}" method="get" id="mail_{{$item->key}}_form">
                                        </form>
                                    @endif
                                </td>

                                <td class="text-center">
                                    @if ($item_admin_data->sms_status == 'disable')
                                        <span class="badge badge-pill badge--info">  {{ 'N / A' }}</span>
                                    @elseif($item_admin_data->sms_status == 'inactive')
                                        <label class="toggle-switch toggle-switch-sm" data-toggle="tooltip" title="{{ 'Este SMS fue desactivado por el administrador.'  }}">
                                            <input type="checkbox"
                                                   class="status toggle-switch-input dynamic-checkbox"  disabled>
                                            <span class="toggle-switch-label text">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    @else

                                        <label class="toggle-switch toggle-switch-sm" data-toggle="tooltip"    @if ($item->mail_status  == 'active')
                                            data-toggle="tooltip" title="{{ 'Desactivar el correo para' .' '.translate($item->title)  }}"
                                            @else
                                            data-toggle="tooltip" title="{{ 'Activar el correo para' .' '.translate($item->title)  }}"
                                            @endif>
                                            <input type="checkbox"
                                                   id="SMS_{{ $item->key }}"
                                                   data-id="SMS_{{ $item->key }}"
                                                   data-type="toggle" data-image-on="{{asset('assets/admin/img/modal/mail-success.png')}}" data-image-off="{{asset('assets/admin/img/modal/mail-warning.png')}}" data-title-on="{{ 'Quiere desactivar el SMS para' .' '.  translate($item->title) }} ?" data-title-off="{{ 'Quiere desactivar el SMS para' .' '.  translate($item->title) }} ?" data-text-on="<p>{{ 'Los SMS estarán habilitados para'  .' '.  translate($item->title) }}</p>" data-text-off="<p>{{ 'Los SMS se desactivarán durante'  .' '.  translate($item->title) }}</p>" class="status toggle-switch-input dynamic-checkbox" {{ $item->sms_status  == 'active' ? 'checked' : '' }}>
                                            <span class="toggle-switch-label text">
                                               <span class="toggle-switch-indicator"></span>
                                           </span>
                                        </label>
                                        <form action="{{route('vendor.business-settings.notification_status_change',['key'=> $item->key ,'type' => 'SMS'])}}" method="get" id="SMS_{{$item->key}}_form">
                                        </form>
                                    @endif
                                </td>
                            </tr>

                        @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

@endsection
