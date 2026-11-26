<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\OfficeSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    public function checkIn(Request $request): RedirectResponse
    {
        $data = $this->validateAttendance($request);
        $settings = OfficeSetting::findOrFail($data['office_id']);
        $workDays = $settings->work_days ?? [];
        $dayName = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'][now()->dayOfWeekIso - 1];
        if (! in_array($dayName, $workDays, true)) {
            return back()->withErrors(['attendance' => 'Hari ini tidak termasuk jadwal hari kerja kantor.']);
        }
        $distance = $this->distance((float) $data['latitude'], (float) $data['longitude'], $settings['latitude'], $settings['longitude']);

        if ($distance > $settings['radius_meters']) {
            return back()->withErrors(['attendance' => 'Lokasi Anda berada di luar radius kantor.']);
        }

        $today = now()->toDateString();
        $photoPath = $data['photo']->store('attendance-photos', 'local');
        if (! $photoPath) {
            return back()->withErrors(['photo' => 'Foto gagal disimpan. Silakan coba lagi.']);
        }

        $attendance = Attendance::firstOrCreate(
            ['user_id' => Auth::id(), 'attendance_date' => $today],
            ['office_id' => $settings->id, 'check_in' => now(), 'latitude' => $data['latitude'], 'longitude' => $data['longitude'], 'distance_meters' => $distance, 'check_in_photo_path' => $photoPath, 'status' => now()->gt(today()->setTimeFromTimeString($settings['start_time'])->addMinutes($settings['grace_minutes'])) ? 'Terlambat' : 'Tepat Waktu']
        );

        if (! $attendance->wasRecentlyCreated) {
            Storage::disk('local')->delete($photoPath);
        }

        return $attendance->wasRecentlyCreated
            ? back()->with('status', 'Absensi masuk berhasil dicatat.')
            : back()->withErrors(['attendance' => 'Absensi masuk hari ini sudah tercatat.']);
    }

    public function checkOut(Request $request): RedirectResponse
    {
        $data = $this->validateAttendance($request);
        $attendance = Attendance::where('user_id', Auth::id())->where('attendance_date', now()->toDateString())->first();
        if (! $attendance || ! $attendance->check_in) {
            return back()->withErrors(['attendance' => 'Catat absensi masuk terlebih dahulu.']);
        }
        if ($attendance->check_out) {
            return back()->withErrors(['attendance' => 'Absensi pulang hari ini sudah tercatat.']);
        }

        $settings = OfficeSetting::findOrFail($data['office_id']);
        $distance = $this->distance((float) $data['latitude'], (float) $data['longitude'], $settings['latitude'], $settings['longitude']);
        if ($distance > $settings['radius_meters']) {
            return back()->withErrors(['attendance' => 'Lokasi Anda berada di luar radius kantor.']);
        }

        $photoPath = $data['photo']->store('attendance-photos', 'local');
        if (! $photoPath) {
            return back()->withErrors(['photo' => 'Foto gagal disimpan. Silakan coba lagi.']);
        }

        $attendance->update([
            'check_out_office_id' => $settings->id,
            'check_out' => now(),
            'check_out_latitude' => $data['latitude'],
            'check_out_longitude' => $data['longitude'],
            'check_out_distance_meters' => $distance,
            'check_out_photo_path' => $photoPath,
        ]);

        return back()->with('status', 'Absensi pulang berhasil dicatat.');
    }

    /** @return array<string, mixed> */
    private function validateAttendance(Request $request): array
    {
        return $request->validate([
            'office_id' => ['required', 'integer', 'exists:office_settings,id'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'photo.required' => 'Foto wajib diunggah untuk mencatat absensi.',
            'photo.image' => 'File yang diunggah harus berupa gambar.',
            'photo.mimes' => 'Format foto harus JPG, JPEG, PNG, atau WEBP.',
            'photo.max' => 'Ukuran foto maksimal 2 MB.',
        ]);
    }

    private function distance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000;
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);
        $a = sin($latDelta / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lonDelta / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
