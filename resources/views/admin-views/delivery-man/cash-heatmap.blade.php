@extends('layouts.admin.app')

@section('title', 'Mapa de Calor de Efectivo')

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/map.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{'Mapa de Calor de Efectivo (Riesgo en Calle)'}}
                </span>
            </h1>
            <p class="text-muted">Visualiza la concentración de dinero en tiempo real sobre el mapa.</p>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div id="map" style="height: 600px; width: 100%;"></div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-4">
                <div class="card bg-soft-danger border-danger">
                    <div class="card-body">
                        <h5 class="text-danger">{{'Riesgo Crítico'}}</h5>
                        <p class="mb-0">{{'Repartidores con > $1000 en mano'}}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    @php($api_key = \App\CentralLogics\Helpers::get_business_settings('map_api_key'))
    <script src="https://maps.googleapis.com/maps/api/js?key={{$api_key}}&libraries=visualization"></script>
    <script>
        function initMap() {
            const map = new google.maps.Map(document.getElementById("map"), {
                zoom: 12,
                center: { lat: {{ $delivery_men->avg('lat') ?? 0 }}, lng: {{ $delivery_men->avg('lng') ?? 0 }} },
                mapTypeId: "roadmap",
            });

            const heatmapData = [
                @foreach($delivery_men as $dm)
                    {location: new google.maps.LatLng({{ $dm['lat'] }}, {{ $dm['lng'] }}), weight: {{ $dm['cash'] }} },
                @endforeach
            ];

            const heatmap = new google.maps.visualization.HeatmapLayer({
                data: heatmapData,
                map: map,
                radius: 50,
                opacity: 0.8
            });

            // Add markers with info windows
            @foreach($delivery_men as $dm)
                @if($dm['cash'] > 0)
                    new google.maps.Marker({
                        position: { lat: {{ $dm['lat'] }}, lng: {{ $dm['lng'] }} },
                        map: map,
                        title: "{{ $dm['name'] }}",
                        icon: {
                            url: "https://maps.google.com/mapfiles/ms/icons/{{ $dm['cash'] > 500 ? 'red' : 'yellow' }}-dot.png"
                        }
                    }).addListener('click', function() {
                        new google.maps.InfoWindow({
                            content: "<strong>{{ $dm['name'] }}</strong><br>Efectivo: ${{ $dm['cash'] }}<br>Pendiente: ${{ $dm['pending'] }}"
                        }).open(map, this);
                    });
                @endif
            @endforeach
        }

        google.maps.event.addDomListener(window, 'load', initMap);
    </script>
@endpush
