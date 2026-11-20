<?php

return [
    'office' => [
        'name' => env('OFFICE_NAME', 'Kantor Pusat'),
        'latitude' => (float) env('OFFICE_LATITUDE', '-6.2087634'),
        'longitude' => (float) env('OFFICE_LONGITUDE', '106.845599'),
        'radius_meters' => (int) env('OFFICE_RADIUS_METERS', 150),
        'start_time' => env('OFFICE_START_TIME', '08:00:00'),
        'end_time' => env('OFFICE_END_TIME', '17:00:00'),
        'grace_minutes' => (int) env('OFFICE_GRACE_MINUTES', 15),
    ],
];
