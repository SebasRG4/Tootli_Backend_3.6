@extends('layouts.admin.app')

@section('title', translate('messages.dm_strike_appeals_title'))

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">{{ translate('messages.dm_strike_appeals_title') }}</h1>
        </div>
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ translate('messages.dm_strike_appeal_dm') }}</th>
                                <th>{{ translate('messages.dm_strike_type') }}</th>
                                <th>{{ translate('messages.order') }}</th>
                                <th>{{ translate('messages.dm_strike_appeal_text') }}</th>
                                <th>{{ translate('messages.dm_strike_appealed_at') }}</th>
                                <th>{{ translate('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($appeals as $a)
                                <tr>
                                    <td>
                                        @if ($a->deliveryMan)
                                            <a href="{{ route('admin.users.delivery-man.preview', $a->delivery_man_id) }}">
                                                {{ trim(($a->deliveryMan->f_name ?? '') . ' ' . ($a->deliveryMan->l_name ?? '')) ?: ('#' . $a->delivery_man_id) }}
                                            </a>
                                        @else
                                            #{{ $a->delivery_man_id }}
                                        @endif
                                    </td>
                                    <td>{{ $a->incidentType?->name ?? '—' }}</td>
                                    <td>
                                        @if ($a->order_id)
                                            <a href="{{ route('admin.transactions.order.details', $a->order_id) }}">#{{ $a->order_id }}</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="fs-12" style="max-width:280px;">{{ \Illuminate\Support\Str::limit($a->appeal_text ?? '', 200) }}</td>
                                    <td class="fs-12">{{ $a->appealed_at }}</td>
                                    <td>
                                        <form method="post" action="{{ route('admin.users.delivery-man.strike.appeals.resolve', $a->id) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="resolution" value="accepted">
                                            <button type="submit" class="btn btn-sm btn-outline-success mb-1">{{ translate('messages.dm_strike_appeal_accept') }}</button>
                                        </form>
                                        <form method="post" action="{{ route('admin.users.delivery-man.strike.appeals.resolve', $a->id) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="resolution" value="rejected">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">{{ translate('messages.dm_strike_appeal_reject') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">{{ translate('messages.no_data_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($appeals->hasPages())
                <div class="card-footer">{{ $appeals->links() }}</div>
            @endif
        </div>
    </div>
@endsection
