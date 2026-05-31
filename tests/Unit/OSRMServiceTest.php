<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\OSRMService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;

class OSRMServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    /** @test */
    public function it_returns_null_when_osrm_url_is_not_configured()
    {
        Config::set('services.osrm.url', '');

        $service = new OSRMService();
        $result = $service->drivingRoute(-99.1332, 19.4326, -99.1412, 19.4340);

        $this->assertNull($result);
    }

    /** @test */
    public function it_successfully_parses_osrm_route_response()
    {
        Config::set('services.osrm.url', 'http://osrm-fake-url.test');

        Http::fake([
            'http://osrm-fake-url.test/route/v1/driving/*' => Http::response([
                'routes' => [
                    [
                        'distance' => 5430.5, // 5.43 km
                        'duration' => 612.0,  // 10.2 minutes
                        'geometry' => [
                            'type' => 'LineString',
                            'coordinates' => [
                                [-99.1332, 19.4326],
                                [-99.1412, 19.4340],
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $service = new OSRMService();
        $result = $service->drivingRoute(-99.1332, 19.4326, -99.1412, 19.4340);

        $this->assertNotNull($result);
        $this->assertEquals(5.43, $result['distance_km']);
        $this->assertEquals(612.0, $result['duration_seconds']);
        $this->assertEquals(11, $result['duration_minutes']); // ceil(612 / 60)
        $this->assertCount(2, $result['coordinates']);
        $this->assertEquals(-99.1332, $result['coordinates'][0][0]);
    }

    /** @test */
    public function it_handles_osrm_http_errors_gracefully()
    {
        Config::set('services.osrm.url', 'http://osrm-fake-url.test');

        Http::fake([
            'http://osrm-fake-url.test/route/v1/driving/*' => Http::response([], 500)
        ]);

        $service = new OSRMService();
        $result = $service->drivingRoute(-99.1332, 19.4326, -99.1412, 19.4340);

        $this->assertNull($result);
    }
}
