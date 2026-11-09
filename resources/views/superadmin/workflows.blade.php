@extends('superadmin.layout')

@section('title', 'Alur Persetujuan Izin')
@section('page-title', 'Pengaturan alur persetujuan izin')

@section('content')
<section class="mt-7 rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-sm sm:p-8">
    <div class="max-w-3xl">
        <p class="text-sm font-medium text-emerald-800">Pengaturan superadmin</p>
        <h2 class="mt-1 font-display text-2xl font-bold text-slate-900">Atur sampai empat tahap approval</h2>
        <p class="mt-2 text-sm leading-6 text-slate-500">Alur umum berlaku berdasarkan jenis lokasi dan tingkat jabatan pemohon. Untuk kantor, Anda juga dapat membuat alur khusus per departemen, misalnya melewati Supervisor yang tidak tersedia. Approver harus aktif dan berada di departemen yang sesuai.</p>
    </div>

    <form method="POST" action="{{ route('superadmin.workflows.update') }}" class="mt-7 space-y-8">
        @csrf @method('PUT')
        @foreach ($groups as $workplaceType => $requesterLevels)
            <div>
                <div class="mb-4 flex items-center gap-3"><span class="grid h-10 w-10 place-items-center rounded-xl bg-emerald-50 text-emerald-800"><x-icon name="office" class="h-5 w-5"/></span><div><h3 class="font-display text-lg font-bold text-slate-900">{{ ['kantor' => 'Kantor', 'spbu' => 'SPBU / Lapangan', 'lpg' => 'Agen LPG'][$workplaceType] ?? $workplaceType }}</h3><p class="text-sm text-slate-500">Alur berdasarkan jabatan pemohon.</p></div></div>
                <div class="grid gap-4 xl:grid-cols-2">
                    @foreach ($requesterLevels as $requesterLevel)
                        @php
                            $formKey = $workplaceType.'_'.$requesterLevel;
                            $workflow = $workflows->get($workplaceType.'.general.'.$requesterLevel);
                            $selectedSteps = old('workflows.'.$formKey.'.approval_steps', $workflow?->approval_steps ?? []);
                            $availableApprovers = $requesterLevel === 'hod' ? $approverLevels : array_diff_key($approverLevels, ['director' => true]);
                        @endphp
                        <article class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5">
                            <h4 class="font-semibold text-slate-800">Pemohon: {{ $levels[$requesterLevel] }}</h4>
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                @for ($stage = 0; $stage < 4; $stage++)
                                    <label class="block text-xs font-semibold text-slate-500">Tahap {{ $stage + 1 }}
                                        <select name="workflows[{{ $formKey }}][approval_steps][]" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-normal text-slate-700 outline-none focus:border-emerald-700">
                                            <option value="">Tidak digunakan</option>
                                            @foreach ($availableApprovers as $level => $label)
                                                <option value="{{ $level }}" @selected(($selectedSteps[$stage] ?? '') === $level)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                @endfor
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @endforeach
        <section class="border-t border-slate-100 pt-7">
            <div class="mb-5 max-w-3xl">
                <p class="text-sm font-medium text-emerald-800">Alur khusus kantor</p>
                <h3 class="mt-1 font-display text-xl font-bold text-slate-900">Sesuaikan alur berdasarkan departemen</h3>
                <p class="mt-2 text-sm leading-6 text-slate-500">Alur khusus akan menggantikan alur umum kantor untuk jabatan pemohon tersebut. Jika semua tahap dibiarkan kosong, departemen memakai alur umum. Contoh: pilih HOD pada tahap pertama untuk melewati Supervisor.</p>
            </div>
            @forelse ($departments as $department)
                <article class="mb-5 rounded-2xl border border-slate-200 bg-slate-50/70 p-5">
                    <h4 class="font-display text-lg font-bold text-slate-900">{{ $department->name }}</h4>
                    <div class="mt-4 grid gap-4 xl:grid-cols-2">
                        @foreach ($groups['kantor'] as $requesterLevel)
                            @php
                                $customWorkflow = $workflows->get('kantor.'.$department->id.'.'.$requesterLevel);
                                $customSteps = old('department_workflows.'.$department->id.'.'.$requesterLevel.'.approval_steps', $customWorkflow?->approval_steps ?? []);
                                $availableApprovers = $requesterLevel === 'hod' ? $approverLevels : array_diff_key($approverLevels, ['director' => true]);
                            @endphp
                            <div class="rounded-xl border border-slate-200 bg-white p-4">
                                <h5 class="text-sm font-semibold text-slate-800">Pemohon: {{ $levels[$requesterLevel] }}</h5>
                                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                    @for ($stage = 0; $stage < 4; $stage++)
                                        <label class="block text-xs font-semibold text-slate-500">Tahap {{ $stage + 1 }}
                                            <select name="department_workflows[{{ $department->id }}][{{ $requesterLevel }}][approval_steps][]" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-normal text-slate-700 outline-none focus:border-emerald-700">
                                                <option value="">{{ $stage === 0 ? 'Pakai alur umum' : 'Tidak digunakan' }}</option>
                                                @foreach ($availableApprovers as $level => $label)
                                                    <option value="{{ $level }}" @selected(($customSteps[$stage] ?? '') === $level)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                    @endfor
                                </div>
                            </div>
                        @endforeach
                    </div>
                </article>
            @empty
                <p class="rounded-xl bg-slate-50 p-4 text-sm text-slate-500">Belum ada departemen untuk diatur.</p>
            @endforelse
        </section>
        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-5"><p class="text-xs text-slate-500">Maksimal 4 tahap per alur. Tahap kosong akan dilewati.</p><button class="inline-flex items-center gap-2 rounded-xl bg-[#567765] px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-900"><x-icon name="check" class="h-4 w-4"/> Simpan semua alur</button></div>
    </form>
</section>
@endsection
