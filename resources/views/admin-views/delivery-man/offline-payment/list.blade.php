@extends('layouts.admin.app')

@section('title', translate('messages.Delivery_Man_Offline_Payments'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/offline_payment.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{translate('messages.Delivery_Man_Offline_Payments')}}
                </span>
            </h1>
            <div class="mt-2 text-primary">
                {{translate('messages.review_and_approve_delivery_man_payments')}}
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row g-3">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header py-2 border-0">
                        <div class="search--button-wrapper justify-content-end">
                            <!-- Nav Tabs -->
                            <ul class="nav nav-tabs mb-0 border-0">
                                <li class="nav-item">
                                    <a class="nav-link {{ $status == 'pending' ? 'active' : '' }}" href="{{ route('admin.delivery-man.offline_payment_list', ['status' => 'pending']) }}">{{ translate('messages.Pending') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ $status == 'approved' ? 'active' : '' }}" href="{{ route('admin.delivery-man.offline_payment_list', ['status' => 'approved']) }}">{{ translate('messages.Approved') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ $status == 'denied' ? 'active' : '' }}" href="{{ route('admin.delivery-man.offline_payment_list', ['status' => 'denied']) }}">{{ translate('messages.Denied') }}</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Table -->
                    <div class="table-responsive datatable-custom">
                        <table id="columnSearchDatatable"
                               class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                               data-hs-datatables-options='{
                                 "order": [],
                                 "orderCellsTop": true,
                                 "paging":false
                               }'>
                            <thead class="thead-light">
                            <tr>
                                <th>{{translate('messages.sl')}}</th>
                                <th>{{translate('messages.Delivery_Man')}}</th>
                                <th>{{translate('messages.Amount')}}</th>
                                <th>{{translate('messages.Payment_Method')}}</th>
                                <th>{{translate('messages.Payment_Info')}}</th>
                                <th class="text-center">{{translate('messages.Status')}}</th>
                                <th class="text-center">{{translate('messages.Action')}}</th>
                            </tr>
                            </thead>

                            <tbody id="set-rows">
                            @foreach($payments as $key=>$payment)
                                <tr>
                                    <td>{{$key+$payments->firstItem()}}</td>
                                    <td>
                                        @if($payment->delivery_man)
                                            <a class="text-dark" href="{{route('admin.users.delivery-man.preview',[$payment->delivery_man_id, 'tab'=> 'transaction'])}}">
                                                {{$payment->delivery_man->f_name}} {{$payment->delivery_man->l_name}}
                                            </a>
                                        @else
                                            <span class="text-muted">{{translate('messages.Not_Found')}}</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{\App\CentralLogics\Helpers::format_currency($payment->amount)}}
                                    </td>
                                    <td>
                                        {{$payment->offline_payment_method ? $payment->offline_payment_method->method_name : translate('messages.Unknown')}}
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn--primary" data-toggle="modal" data-target="#paymentInfoModal{{$payment->id}}">
                                            {{translate('messages.View_Info')}}
                                        </button>
                                    </td>
                                    <td class="text-center">
                                        @if($payment->status == 'pending')
                                            <label class="badge badge-soft-info">{{translate('messages.pending')}}</label>
                                        @elseif($payment->status == 'approved')
                                            <label class="badge badge-soft-success">{{translate('messages.approved')}}</label>
                                        @else
                                            <label class="badge badge-soft-danger">{{translate('messages.denied')}}</label>
                                        @endif
                                    </td>
                                    <td>
                                        @if($payment->status == 'pending')
                                            <div class="btn--container justify-content-center">
                                                <button class="btn btn-sm btn--success" data-toggle="modal" data-target="#approveModal{{$payment->id}}">
                                                    {{translate('messages.Approve')}}
                                                </button>
                                                <button class="btn btn-sm btn--danger" data-toggle="modal" data-target="#denyModal{{$payment->id}}">
                                                    {{translate('messages.Deny')}}
                                                </button>
                                            </div>
                                        @else
                                            <div class="text-center">
                                                <span class="text-muted">{{translate('messages.Processed')}}</span>
                                            </div>
                                        @endif
                                    </td>
                                </tr>

                                <!-- Payment Info Modal -->
                                <div class="modal fade" id="paymentInfoModal{{$payment->id}}" tabindex="-1" role="dialog" aria-labelledby="paymentInfoModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="paymentInfoModalLabel">{{translate('messages.Payment_Information')}}</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <ul class="list-group">
                                                    @php
                                                        $info = json_decode($payment->payment_info, true);
                                                    @endphp
                                                    @if($info)
                                                        @foreach($info as $field => $value)
                                                            @if($field != 'method_id' && $field != 'method_name')
                                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                    <strong>{{translate(str_replace('_', ' ', $field))}}</strong>
                                                                    <span>{{$value}}</span>
                                                                </li>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                    @if($payment->admin_note)
                                                    <li class="list-group-item">
                                                        <strong>{{translate('messages.Admin_Note')}}:</strong>
                                                        <p class="mt-2 text-muted">{{$payment->admin_note}}</p>
                                                    </li>
                                                    @endif
                                                </ul>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('messages.Close')}}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Approve Modal -->
                                <div class="modal fade" id="approveModal{{$payment->id}}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">{{translate('messages.Approve_Payment')}}</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form action="{{route('admin.delivery-man.offline_payment_verify')}}" method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <input type="hidden" name="id" value="{{$payment->id}}">
                                                    <input type="hidden" name="status" value="approved">
                                                    <p>{{translate('messages.Are_you_sure_to_approve_this_payment?_The_amount_will_be_deducted_from_delivery_man_cash_in_hand.')}}</p>
                                                    <div class="form-group">
                                                        <label class="input-label">{{translate('messages.Admin_Note')}} ({{translate('messages.Optional')}})</label>
                                                        <textarea name="admin_note" class="form-control" rows="3"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('messages.Cancel')}}</button>
                                                    <button type="submit" class="btn btn-success">{{translate('messages.Approve')}}</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Deny Modal -->
                                <div class="modal fade" id="denyModal{{$payment->id}}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">{{translate('messages.Deny_Payment')}}</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form action="{{route('admin.delivery-man.offline_payment_verify')}}" method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <input type="hidden" name="id" value="{{$payment->id}}">
                                                    <input type="hidden" name="status" value="denied">
                                                    <div class="form-group">
                                                        <label class="input-label">{{translate('messages.Admin_Note')}} ({{translate('messages.Required')}})</label>
                                                        <textarea name="admin_note" class="form-control" rows="3" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('messages.Cancel')}}</button>
                                                    <button type="submit" class="btn btn-danger">{{translate('messages.Deny')}}</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            </tbody>
                        </table>
                        @if(count($payments) === 0)
                        <div class="empty--data">
                            <img src="{{asset('/public/assets/admin/svg/illustrations/sorrow.svg')}}" alt="public">
                            <h5>
                                {{translate('no_data_found')}}
                            </h5>
                        </div>
                        @endif
                        <div class="page-area px-4 pb-3">
                            <div class="d-flex align-items-center justify-content-end">
                                <div>
                                    {!! $payments->links() !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Table -->
                </div>
            </div>
        </div>
    </div>
@endsection
