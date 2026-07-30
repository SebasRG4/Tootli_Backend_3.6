@extends('layouts.admin.app')

@section('title', 'Auditoría de Depósitos Efectivos')

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/cash.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{'Auditoría de Depósitos Efectivos'}}
                </span>
            </h1>
        </div>

        <div class="card">
            <div class="card-header border-0 pb-0">
                <ul class="nav nav-tabs mb-0 border-0">
                    <li class="nav-item">
                        <a class="nav-link {{$status == 'pending' ? 'active' : ''}}" href="{{route('admin.delivery-man.cash-deposit-list', ['status' => 'pending'])}}">{{'Pendientes'}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$status == 'approved' ? 'active' : ''}}" href="{{route('admin.delivery-man.cash-deposit-list', ['status' => 'approved'])}}">{{'Aprobados'}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$status == 'denied' ? 'active' : ''}}" href="{{route('admin.delivery-man.cash-deposit-list', ['status' => 'denied'])}}">{{'Rechazados'}}</a>
                    </li>
                </ul>
            </div>
            
            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th>{{'IDENTIFICACIÓN'}}</th>
                            <th>{{'repartidor'}}</th>
                            <th>{{'monte'}}</th>
                            <th>{{'Fecha Reporte'}}</th>
                            <th>{{'Comprobante'}}</th>
                            <th class="text-center">{{'acción'}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deposits as $deposit)
                            <tr>
                                <td>{{$deposit->id}}</td>
                                <td>
                                    <a href="{{route('admin.users.delivery-man.preview', [$deposit->delivery_man_id])}}">
                                        {{$deposit->delivery_man->f_name}} {{$deposit->delivery_man->l_name}}
                                    </a>
                                </td>
                                <td>{{ \App\CentralLogics\Helpers::format_currency($deposit->amount) }}</td>
                                <td>{{ $deposit->created_at->format('d M Y, h:i A') }}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#photoModal{{$deposit->id}}">
                                        {{'Ver foto'}}
                                    </button>
                                </td>
                                <td class="text-center">
                                    @if($deposit->status == 'pending')
                                        <div class="btn--container justify-content-center">
                                            <form action="{{route('admin.delivery-man.cash-deposit-update')}}" method="POST">
                                                @csrf
                                                <input type="hidden" name="id" value="{{$deposit->id}}">
                                                <input type="hidden" name="status" value="approved">
                                                <button type="submit" class="btn btn-sm btn-success">{{'aprobar'}}</button>
                                            </form>
                                            <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#denyModal{{$deposit->id}}">
                                                {{'rechazar'}}
                                            </button>
                                        </div>
                                    @else
                                        <span class="badge badge-soft-{{$deposit->status == 'approved' ? 'success' : 'danger'}}">
                                            {{translate($deposit->status)}}
                                        </span>
                                    @endif
                                </td>
                            </tr>

                            <!-- Photo Modal -->
                            <div class="modal fade" id="photoModal{{$deposit->id}}" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">{{'Comprobante de Depósito'}} - {{$deposit->delivery_man->f_name}}</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body text-center">
                                            <img src="{{asset('storage/app/public/delivery-man/deposit/' . $deposit->photo)}}" 
                                                 onerror="this.src='{{asset('assets/admin/img/900x400/img1.jpg')}}'"
                                                 class="img-fluid rounded border" alt="">
                                            <div class="mt-3 text-left">
                                                <p><strong>{{'monte'}}:</strong> {{ \App\CentralLogics\Helpers::format_currency($deposit->amount) }}</p>
                                                @if($deposit->latitude)
                                                    <p><strong>{{'Ubicación Reporte'}}:</strong> 
                                                        <a href="https://www.google.com/maps?q={{$deposit->latitude}},{{$deposit->longitude}}" target="_blank">
                                                            {{'Ver en mapas'}}
                                                        </a>
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Deny Modal -->
                            <div class="modal fade" id="denyModal{{$deposit->id}}" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">{{'Rechazar Depósito'}}</h5>
                                        </div>
                                        <form action="{{route('admin.delivery-man.cash-deposit-update')}}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <input type="hidden" name="id" value="{{$deposit->id}}">
                                                <input type="hidden" name="status" value="denied">
                                                <div class="form-group">
                                                    <label>{{'Razón del rechazo'}}</label>
                                                    <textarea name="admin_note" class="form-control" rows="3" required></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{'Cancelar'}}</button>
                                                <button type="submit" class="btn btn-danger">{{'rechazar'}}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {!! $deposits->links() !!}
            </div>
        </div>
    </div>
@endsection
