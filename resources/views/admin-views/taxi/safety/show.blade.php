@extends('layouts.admin.app')

@section('title', translate('Alert Details'))

@push('css_or_js')
    <style>
        .info-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            padding: 20px;
            margin-bottom: 20px;
        }

        .info-card h6 {
            color: #6c757d;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f1f1f1;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .map-container {
            height: 300px;
            border-radius: 12px;
            overflow: hidden;
        }

        .action-buttons .btn {
            margin-right: 10px;
            margin-bottom: 10px;
        }

        .emergency-banner {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .insecure-banner {
            background: linear-gradient(135deg, #fd7e14, #e8690a);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Back Button -->
        <div class="mb-3">
            <a href="{{ route('admin.taxi.safety.index') }}" class="btn btn-outline-secondary">
                <i class="tio-arrow-left"></i> {{ translate('Back to Alerts') }}
            </a>
        </div>

        <!-- Alert Banner -->
        @if($alert->alert_type == 'emergency')
            <div class="emergency-banner">
                <h3><i class="tio-warning mr-2"></i> 🆘 EMERGENCY ALERT</h3>
                <p class="mb-0">This user called 911. Immediate attention required!</p>
            </div>
        @else
            <div class="insecure-banner">
                <h3><i class="tio-shield mr-2"></i> 🛡️ User Feels Insecure</h3>
                <p class="mb-0">Please contact the user immediately to verify their safety.</p>
            </div>
        @endif

        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-6">
                <!-- Alert Info -->
                <div class="info-card">
                    <h6><i class="tio-info mr-2"></i>{{ translate('Alert Information') }}</h6>
                    <div class="info-item">
                        <span>{{ translate('Alert ID') }}</span>
                        <strong>#{{ $alert->id }}</strong>
                    </div>
                    <div class="info-item">
                        <span>{{ translate('Type') }}</span>
                        <span class="badge badge-{{ $alert->alert_type == 'emergency' ? 'danger' : 'warning' }}">
                            {{ strtoupper($alert->alert_type) }}
                        </span>
                    </div>
                    <div class="info-item">
                        <span>{{ translate('Status') }}</span>
                        <span
                            class="badge badge-{{ $alert->status == 'resolved' ? 'success' : ($alert->status == 'pending' ? 'warning' : 'info') }}">
                            {{ strtoupper($alert->status) }}
                        </span>
                    </div>
                    <div class="info-item">
                        <span>{{ translate('Created') }}</span>
                        <strong>{{ $alert->created_at->format('d/m/Y H:i:s') }}</strong>
                    </div>
                    @if($alert->contacted_at)
                        <div class="info-item">
                            <span>{{ translate('Contacted At') }}</span>
                            <strong>{{ $alert->contacted_at->format('d/m/Y H:i:s') }}</strong>
                        </div>
                    @endif
                    @if($alert->resolved_at)
                        <div class="info-item">
                            <span>{{ translate('Resolved At') }}</span>
                            <strong>{{ $alert->resolved_at->format('d/m/Y H:i:s') }}</strong>
                        </div>
                    @endif
                </div>

                <!-- User Info -->
                <div class="info-card">
                    <h6><i class="tio-user mr-2"></i>{{ translate('User Information') }}</h6>
                    <div class="info-item">
                        <span>{{ translate('Name') }}</span>
                        <strong>{{ $alert->user->f_name ?? '' }} {{ $alert->user->l_name ?? '' }}</strong>
                    </div>
                    <div class="info-item">
                        <span>{{ translate('Phone') }}</span>
                        <a href="tel:{{ $alert->user->phone }}" class="text-primary">
                            <i class="tio-call"></i> {{ $alert->user->phone ?? 'N/A' }}
                        </a>
                    </div>
                    <div class="info-item">
                        <span>{{ translate('Email') }}</span>
                        <span>{{ $alert->user->email ?? 'N/A' }}</span>
                    </div>
                </div>

                <!-- Driver Info -->
                @if($alert->taxiRide->driver)
                    <div class="info-card">
                        <h6><i class="tio-car mr-2"></i>{{ translate('Driver Information') }}</h6>
                        <div class="info-item">
                            <span>{{ translate('Name') }}</span>
                            <strong>{{ $alert->taxiRide->driver->f_name }} {{ $alert->taxiRide->driver->l_name }}</strong>
                        </div>
                        <div class="info-item">
                            <span>{{ translate('Phone') }}</span>
                            <a href="tel:{{ $alert->taxiRide->driver->phone }}" class="text-primary">
                                <i class="tio-call"></i> {{ $alert->taxiRide->driver->phone }}
                            </a>
                        </div>
                        <div class="info-item">
                            <span>{{ translate('Vehicle Plate') }}</span>
                            <strong>{{ $alert->taxiRide->driver->vehicle->plate ?? 'N/A' }}</strong>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Column -->
            <div class="col-lg-6">
                <!-- Map -->
                <div class="info-card">
                    <h6><i class="tio-map mr-2"></i>{{ translate('Location') }}</h6>
                    @if($alert->user_location_lat && $alert->user_location_lng)
                        <div class="map-container mb-3">
                            <iframe width="100%" height="100%" frameborder="0"
                                src="https://www.google.com/maps/embed/v1/place?key={{ env('GOOGLE_MAPS_API_KEY') }}&q={{ $alert->user_location_lat }},{{ $alert->user_location_lng }}&zoom=15"
                                allowfullscreen>
                            </iframe>
                        </div>
                        <a href="https://www.google.com/maps?q={{ $alert->user_location_lat }},{{ $alert->user_location_lng }}"
                            target="_blank" class="btn btn-outline-primary btn-block">
                            <i class="tio-map"></i> {{ translate('Open in Google Maps') }}
                        </a>
                    @else
                        <p class="text-muted">{{ translate('No location data available') }}</p>
                    @endif
                </div>

                <!-- Ride Info -->
                <div class="info-card">
                    <h6><i class="tio-car mr-2"></i>{{ translate('Ride Information') }}</h6>
                    <div class="info-item">
                        <span>{{ translate('Ride ID') }}</span>
                        <a href="{{ route('admin.taxi.rides.details', $alert->taxi_ride_id) }}" class="text-primary">
                            #{{ $alert->taxi_ride_id }}
                        </a>
                    </div>
                    <div class="info-item">
                        <span>{{ translate('Status') }}</span>
                        <span class="badge badge-info">{{ $alert->taxiRide->status }}</span>
                    </div>
                    <div class="info-item">
                        <span>{{ translate('Pickup') }}</span>
                        <span class="text-right" style="max-width: 200px;">{{ $alert->taxiRide->pickup_address }}</span>
                    </div>
                    <div class="info-item">
                        <span>{{ translate('Dropoff') }}</span>
                        <span class="text-right" style="max-width: 200px;">{{ $alert->taxiRide->dropoff_address }}</span>
                    </div>
                </div>

                <!-- Admin Notes -->
                @if($alert->admin_notes)
                    <div class="info-card">
                        <h6><i class="tio-document-text mr-2"></i>{{ translate('Admin Notes') }}</h6>
                        <p class="mb-0">{{ $alert->admin_notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="info-card action-buttons">
            <h6><i class="tio-flash mr-2"></i>{{ translate('Actions') }}</h6>

            @if($alert->status == 'pending')
                <form action="{{ route('admin.taxi.safety.contact', $alert->id) }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="notes" value="Contacted user via phone">
                    <button type="submit" class="btn btn-info">
                        <i class="tio-call"></i> {{ translate('Mark as Contacted') }}
                    </button>
                </form>
            @endif

            @if($alert->status != 'resolved')
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#resolveModal">
                    <i class="tio-checkmark-circle"></i> {{ translate('Mark as Resolved') }}
                </button>
            @endif

            @if($alert->alert_type == 'emergency' && $alert->status != 'escalated')
                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#escalateModal">
                    <i class="tio-warning"></i> {{ translate('Escalate to Authorities') }}
                </button>
            @endif

            <a href="{{ route('admin.taxi.safety.report', $alert->id) }}" target="_blank" class="btn btn-secondary">
                <i class="tio-download"></i> {{ translate('Generate Report') }}
            </a>
        </div>

        <!-- Recordings -->
        @if($recordings->count() > 0)
            <div class="info-card">
                <h6><i class="tio-mic mr-2"></i>{{ translate('Audio Recordings') }}</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>{{ translate('Date') }}</th>
                                <th>{{ translate('Duration') }}</th>
                                <th>{{ translate('Size') }}</th>
                                <th>{{ translate('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recordings as $recording)
                                <tr>
                                    <td>{{ $recording->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $recording->formatted_duration }}</td>
                                    <td>{{ $recording->file_size_kb }} KB</td>
                                    <td>
                                        <audio controls style="height: 30px;">
                                            <source src="{{ $recording->audio_url }}" type="audio/mpeg">
                                        </audio>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <!-- Resolve Modal -->
    <div class="modal fade" id="resolveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.taxi.safety.resolve', $alert->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ translate('Resolve Alert') }}</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>{{ translate('Resolution Notes') }}</label>
                            <textarea name="notes" class="form-control" rows="3"
                                placeholder="{{ translate('Enter resolution details...') }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">{{ translate('Cancel') }}</button>
                        <button type="submit" class="btn btn-success">{{ translate('Mark as Resolved') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Escalate Modal -->
    <div class="modal fade" id="escalateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.taxi.safety.escalate', $alert->id) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">{{ translate('Escalate to Authorities') }}</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <strong>{{ translate('Warning!') }}</strong>
                            {{ translate('This action indicates that the case has been reported to authorities.') }}
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Escalation Notes') }}</label>
                            <textarea name="notes" class="form-control" rows="3"
                                placeholder="{{ translate('Enter details about escalation...') }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">{{ translate('Cancel') }}</button>
                        <button type="submit" class="btn btn-danger">{{ translate('Escalate') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection