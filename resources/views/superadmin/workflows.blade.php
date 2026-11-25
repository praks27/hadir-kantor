@extends('superadmin.layout')

@section('title', 'Alur Persetujuan Izin')
@section('page-title', 'Pengaturan alur persetujuan izin')

@section('content')
<section class="mt-7 rounded-3xl border border-slate-200/80 bg-white/90 p-6 shadow-sm sm:p-8">
    <div class="max-w-3xl space-y-2">
        <p class="text-sm font-medium text-emerald-800">Pengaturan superadmin</p>
        <h2 class="font-display text-2xl font-bold text-slate-900">Atur alur persetujuan per lokasi dan departemen</h2>
        <p class="text-sm leading-6 text-slate-500">Atur jalur persetujuan kantor, SPBU, dan LPG sesuai tingkat pemohon.</p>
    </div>

    <aside class="mt-5 flex max-w-4xl items-start gap-3 rounded-2xl border border-emerald-100 bg-emerald-50/70 p-4 sm:gap-4 sm:p-5">
        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-white text-emerald-800 shadow-sm" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><circle cx="12" cy="12" r="9"/><path d="M12 11v5m0-8h.01" stroke-linecap="round"/></svg>
        </span>
        <div class="min-w-0 space-y-1">
            <p class="text-sm font-semibold text-emerald-900">Catatan konfigurasi</p>
            <p class="text-sm leading-6 text-slate-600">Jika alur khusus kantor dikosongkan, sistem memakai alur umum. Pada SPBU/LPG, tahap kosong akan dilewati dan urutan tahap yang tersisa harus tetap mengikuti hierarki.</p>
        </div>
    </aside>

    <form method="POST" action="{{ route('superadmin.workflows.update') }}" class="mt-7 space-y-8">
        @csrf @method('PUT')
        @foreach ($groups as $workplaceType => $requesterLevels)
            <div>
                <div class="mb-4 flex items-center gap-3"><span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-emerald-50 text-emerald-800"><x-icon name="office" class="h-5 w-5"/></span><div class="space-y-1"><h3 class="font-display text-lg font-bold text-slate-900">{{ ['kantor' => 'Kantor', 'spbu' => 'SPBU / Lapangan', 'lpg' => 'Agen LPG'][$workplaceType] ?? $workplaceType }}</h3><p class="text-sm leading-5 text-slate-500">Alur berdasarkan jabatan pemohon.</p></div></div>
                @if ($workplaceType !== 'kantor')
                    <aside class="mb-4 flex items-start gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:gap-4 sm:p-5">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-emerald-50 text-emerald-800" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path d="M5 7h14M5 12h14M5 17h8" stroke-linecap="round"/><circle cx="18" cy="17" r="2"/></svg>
                        </span>
                        <div class="min-w-0 space-y-1">
                            <p class="text-sm font-semibold text-slate-800">Penugasan approver</p>
                            <p class="text-sm leading-6 text-slate-500">Approver dipilih otomatis dari departemen dan lokasi yang sesuai, dengan mempertimbangkan jumlah pengajuan tertunda.</p>
                        </div>
                    </aside>
                @endif
                <div class="grid gap-4 xl:grid-cols-2">
                    @foreach ($requesterLevels as $requesterLevel)
                        @php
                            $formKey = $workplaceType.'_'.$requesterLevel;
                            $workflow = $workflows->get($workplaceType.'.general.'.$requesterLevel);
                            $rawSelectedSteps = old('workflows.'.$formKey.'.approval_steps', $workflow?->approval_steps ?? []);
                            $selectedSteps = is_array($rawSelectedSteps) ? array_values(array_filter($rawSelectedSteps, fn ($step) => filled($step))) : [];
                            $allowedSequence = $workflowChains[$workplaceType][$requesterLevel] ?? [];
                            $availableApprovers = collect($allowedSequence)->mapWithKeys(fn ($level) => [$level => $approverLevels[$level]])->all();
                            $stageLimit = count($allowedSequence);
                        @endphp
                        <article class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5" @if ($workplaceType !== 'kantor') data-workplace-workflow data-requester-label="{{ $levels[$requesterLevel] }}" data-chain="{{ implode('|', $allowedSequence) }}" data-coverage="{{ base64_encode(json_encode($workplaceApproverCoverage[$workplaceType][$requesterLevel] ?? [])) }}" @endif>
                            <h4 class="font-semibold text-slate-800">Pemohon: {{ $levels[$requesterLevel] }}</h4>
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                @for ($stage = 0; $stage < $stageLimit; $stage++)
                                    <label class="block text-xs font-semibold text-slate-500">Tahap {{ $stage + 1 }}
                                        <select data-workflow-stage data-stage="{{ $stage }}" name="workflows[{{ $formKey }}][approval_steps][]" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-normal text-slate-700 outline-none focus:border-emerald-700">
                                            <option value="">Tidak digunakan</option>
                                            @if (filled($selectedSteps[$stage] ?? null) && ! isset($availableApprovers[$selectedSteps[$stage]]))
                                                <option value="{{ $selectedSteps[$stage] }}" selected>{{ $levels[$selectedSteps[$stage]] ?? $selectedSteps[$stage] }} — tidak sesuai alur</option>
                                            @endif
                                            @foreach ($availableApprovers as $level => $label)
                                                <option value="{{ $level }}" @selected(($selectedSteps[$stage] ?? '') === $level)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                @endfor
                            </div>
                            @if ($workplaceType !== 'kantor')
                                <div class="mt-5 rounded-2xl border border-slate-200 bg-white p-4 sm:p-5">
                                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Pratinjau alur</p>
                                    <p data-workflow-route class="mt-3 rounded-xl bg-slate-50 px-4 py-3 text-sm font-semibold leading-6 text-slate-700"></p>
                                    <p data-workflow-warning class="mt-3 border-l-2 border-amber-300 pl-3 text-xs leading-5 text-amber-700"></p>
                                    <p data-workflow-order-warning class="mt-2 border-l-2 border-rose-300 pl-3 text-xs leading-5 text-rose-600"></p>
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        @endforeach
        <section class="border-t border-slate-100 pt-7">
            <div class="mb-5 max-w-3xl space-y-2">
                <p class="text-sm font-medium text-emerald-800">Alur khusus kantor</p>
                <h3 class="font-display text-xl font-bold text-slate-900">Sesuaikan alur berdasarkan departemen</h3>
                <p class="text-sm leading-6 text-slate-500">Pilih pola untuk pemohon karyawan. Alur Supervisor dan HOD dapat mengikuti alur umum atau diubah lewat pengaturan lanjutan.</p>
            </div>
            <aside class="mb-5 flex max-w-4xl items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50/70 p-4 sm:gap-4 sm:p-5">
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-white text-emerald-800 shadow-sm" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path d="M12 3v18m0-18 7 4v8l-7 6-7-6V7l7-4Z" stroke-linejoin="round"/><path d="m9 12 2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <div class="min-w-0 space-y-1">
                    <p class="text-sm font-semibold text-slate-800">Periksa ketersediaan approver</p>
                    <p class="text-sm leading-6 text-slate-500">Pratinjau menampilkan jumlah approver aktif. Jika suatu tahap belum memiliki approver, lengkapi penempatan pengguna sebelum pengajuan memakai alur tersebut.</p>
                </div>
            </aside>
            @forelse ($departments as $department)
                @php
                    $officeDefaultSteps = [];
                    foreach ($groups['kantor'] as $level) {
                        $defaultSteps = old('workflows.kantor_'.$level.'.approval_steps', $workflows->get('kantor.general.'.$level)?->approval_steps ?? []);
                        $officeDefaultSteps[$level] = is_array($defaultSteps) ? array_values(array_filter($defaultSteps, fn ($step) => filled($step))) : [];
                    }
                    $storedStaff = $workflows->get('kantor.'.$department->id.'.staff')?->approval_steps ?? [];
                    $postedStaff = old('department_workflows.'.$department->id.'.staff.approval_steps', $storedStaff);
                    $staffSteps = is_array($postedStaff) ? array_values(array_filter($postedStaff, fn ($step) => filled($step))) : [];
                    $staffPattern = match ($staffSteps) {
                        [] => 'inherit',
                        ['hod'] => 'without-supervisor',
                        ['supervisor', 'hod'] => 'with-supervisor',
                        default => 'custom',
                    };
                @endphp
                <article
                    class="mb-5 rounded-2xl border border-slate-200 bg-slate-50/70 p-5"
                    data-department-flow
                    data-default-staff="{{ implode('|', $officeDefaultSteps['staff']) }}"
                    data-default-supervisor="{{ implode('|', $officeDefaultSteps['supervisor']) }}"
                    data-default-hod="{{ implode('|', $officeDefaultSteps['hod']) }}"
                    data-count-supervisor="{{ $approverAvailability[$department->id]['supervisor'] ?? 0 }}"
                    data-count-hod="{{ $approverAvailability[$department->id]['hod'] ?? 0 }}"
                    data-count-director="{{ $approverAvailability[$department->id]['director'] ?? 0 }}"
                >
                    <h4 class="font-display text-lg font-bold text-slate-900">{{ $department->name }}</h4>
                    <div class="mt-4 grid gap-4 lg:grid-cols-[minmax(15rem,.7fr)_minmax(0,1.3fr)]">
                        <label class="block text-sm font-semibold text-slate-700">Pola alur pemohon karyawan
                            <select data-staff-pattern class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-normal text-slate-700 outline-none focus:border-emerald-700">
                                <option value="inherit" @selected($staffPattern === 'inherit')>Ikuti alur umum</option>
                                <option value="without-supervisor" @selected($staffPattern === 'without-supervisor')>Tanpa Supervisor: langsung ke HOD</option>
                                <option value="with-supervisor" @selected($staffPattern === 'with-supervisor')>Dengan Supervisor: Supervisor lalu HOD</option>
                                <option value="custom" @selected($staffPattern === 'custom')>Kustom</option>
                            </select>
                            <span data-pattern-help class="mt-1.5 block text-xs font-normal leading-5 text-slate-500"></span>
                        </label>
                        <div class="rounded-xl border border-white bg-white p-4">
                            <h5 class="text-sm font-semibold text-slate-800">Pratinjau alur efektif</h5>
                            <div class="mt-2 space-y-2 text-xs">
                                @foreach (['staff' => 'Karyawan', 'supervisor' => 'Supervisor', 'hod' => 'HOD'] as $level => $label)
                                    <div data-preview-row="{{ $level }}" class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                                        <span class="font-semibold text-slate-600">{{ $label }}:</span>
                                        <span data-preview-route="{{ $level }}" class="text-slate-700"></span>
                                        <span data-preview-inherited="{{ $level }}" class="text-slate-400"></span>
                                        <span data-preview-warning="{{ $level }}" class="w-full text-amber-700"></span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <details data-advanced-workflow class="mt-4 rounded-xl border border-slate-200 bg-white p-4">
                        <summary class="cursor-pointer text-sm font-semibold text-slate-700">Pengaturan lanjutan per tingkat pemohon</summary>
                        <p class="mt-2 text-xs leading-5 text-slate-500">Kosongkan semua tahap pada tingkat pemohon untuk mengikuti alur umum. Tahap kosong di tengah akan dilewati.</p>
                        <div class="mt-3 grid gap-4 xl:grid-cols-2">
                            @foreach ($groups['kantor'] as $requesterLevel)
                                @php
                                    $customWorkflow = $workflows->get('kantor.'.$department->id.'.'.$requesterLevel);
                                    $rawCustomSteps = old('department_workflows.'.$department->id.'.'.$requesterLevel.'.approval_steps', $customWorkflow?->approval_steps ?? []);
                                    $customSteps = is_array($rawCustomSteps) ? array_values(array_filter($rawCustomSteps, fn ($step) => filled($step))) : [];
                                    $availableApprovers = array_intersect_key($approverLevels, array_flip(match ($requesterLevel) {
                                        'staff' => ['supervisor', 'hod'],
                                        'supervisor' => ['hod'],
                                        'hod' => ['director'],
                                        default => [],
                                    }));
                                    $stageLimit = match ($requesterLevel) { 'staff' => 2, 'supervisor', 'hod' => 1, default => 4 };
                                @endphp
                                <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                                    <h5 class="text-sm font-semibold text-slate-800">Pemohon: {{ $levels[$requesterLevel] }}</h5>
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                        @for ($stage = 0; $stage < $stageLimit; $stage++)
                                            <label class="block text-xs font-semibold text-slate-500">Tahap {{ $stage + 1 }}
                                                <select data-requester-level="{{ $requesterLevel }}" data-stage="{{ $stage }}" name="department_workflows[{{ $department->id }}][{{ $requesterLevel }}][approval_steps][]" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-normal text-slate-700 outline-none focus:border-emerald-700">
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
                    </details>
                </article>
            @empty
                <p class="rounded-xl bg-slate-50 p-4 text-sm text-slate-500">Belum ada departemen untuk diatur.</p>
            @endforelse
        </section>
        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-5"><p class="text-xs text-slate-500">Urutan Kantor, SPBU, dan LPG dibatasi menurut tingkat pemohon. Tahap dapat dilewati jika operasional memerlukannya.</p><button class="inline-flex items-center gap-2 rounded-xl bg-[#5b84a8] px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-900"><x-icon name="check" class="h-4 w-4"/> Simpan semua alur</button></div>
    </form>
</section>
<script>
    (() => {
        const labels = @json($levels);
        const patternHelp = {
            inherit: 'Karyawan mengikuti alur umum kantor.',
            'without-supervisor': 'Pengajuan karyawan langsung ditujukan ke HOD.',
            'with-supervisor': 'Pengajuan karyawan melewati Supervisor, lalu HOD.',
            custom: 'Urutan karyawan dapat diubah di pengaturan lanjutan.',
        };

        document.querySelectorAll('[data-department-flow]').forEach((card) => {
            const pattern = card.querySelector('[data-staff-pattern]');
            const advanced = card.querySelector('[data-advanced-workflow]');
            const staffFields = [...card.querySelectorAll('[data-requester-level="staff"]')]
                .sort((a, b) => Number(a.dataset.stage) - Number(b.dataset.stage));

            const selectedSteps = (level) => [...card.querySelectorAll(`[data-requester-level="${level}"]`)]
                .sort((a, b) => Number(a.dataset.stage) - Number(b.dataset.stage))
                .map((field) => field.value)
                .filter(Boolean);

            const derivePattern = () => {
                const steps = selectedSteps('staff');
                const next = steps.length === 0 ? 'inherit'
                    : steps.length === 1 && steps[0] === 'hod' ? 'without-supervisor'
                    : steps.length === 2 && steps[0] === 'supervisor' && steps[1] === 'hod' ? 'with-supervisor'
                    : 'custom';
                pattern.value = next;
            };

            const updatePreview = () => {
                card.querySelector('[data-pattern-help]').textContent = patternHelp[pattern.value] ?? patternHelp.custom;
                ['staff', 'supervisor', 'hod'].forEach((level) => {
                    const selected = selectedSteps(level);
                    const inherited = selected.length === 0;
                    const steps = inherited ? (card.dataset[`default${level[0].toUpperCase()}${level.slice(1)}`] || '').split('|').filter(Boolean) : selected;
                    const route = card.querySelector(`[data-preview-route="${level}"]`);
                    const inheritedLabel = card.querySelector(`[data-preview-inherited="${level}"]`);
                    const warning = card.querySelector(`[data-preview-warning="${level}"]`);
                    const missing = steps.filter((step) => Number(card.dataset[`count${step[0].toUpperCase()}${step.slice(1)}`] ?? 0) === 0);

                    route.textContent = steps.length
                        ? steps.map((step) => `${labels[step] ?? step} (${card.dataset[`count${step[0].toUpperCase()}${step.slice(1)}`] ?? 0} aktif)`).join(' → ')
                        : 'Belum ada tahap';
                    inheritedLabel.textContent = inherited ? '· alur umum' : '· alur khusus';
                    warning.textContent = missing.length
                        ? `Belum ada approver aktif: ${missing.map((step) => labels[step] ?? step).join(', ')}.`
                        : '';
                });
            };

            pattern.addEventListener('change', () => {
                if (pattern.value === 'inherit') staffFields.forEach((field) => { field.value = ''; });
                if (pattern.value === 'without-supervisor') {
                    staffFields.forEach((field, index) => { field.value = index === 0 ? 'hod' : ''; });
                }
                if (pattern.value === 'with-supervisor') {
                    staffFields.forEach((field, index) => { field.value = index === 0 ? 'supervisor' : index === 1 ? 'hod' : ''; });
                }
                if (pattern.value === 'custom') advanced.open = true;
                updatePreview();
            });

            card.querySelectorAll('[data-requester-level]').forEach((field) => {
                field.addEventListener('change', () => {
                    if (field.dataset.requesterLevel === 'staff') derivePattern();
                    updatePreview();
                });
            });

            updatePreview();
        });

        document.querySelectorAll('[data-workplace-workflow]').forEach((card) => {
            const chain = (card.dataset.chain || '').split('|').filter(Boolean);
            const coverage = JSON.parse(atob(card.dataset.coverage || 'e30='));
            const fields = [...card.querySelectorAll('[data-workflow-stage]')]
                .sort((a, b) => Number(a.dataset.stage) - Number(b.dataset.stage));
            const route = card.querySelector('[data-workflow-route]');
            const warning = card.querySelector('[data-workflow-warning]');
            const orderWarning = card.querySelector('[data-workflow-order-warning]');

            const updateWorkflowPreview = () => {
                const steps = fields.map((field) => field.value).filter(Boolean);
                const positions = steps.map((step) => chain.indexOf(step));
                const ordered = positions.every((position, index) => position >= 0 && (index === 0 || position > positions[index - 1]));

                route.textContent = steps.length
                    ? steps.map((step) => labels[step] ?? step).join(' → ')
                    : 'Belum ada tahap';
                orderWarning.textContent = ordered
                    ? ''
                    : 'Urutan ini tidak sesuai hierarki. Pindahkan approver ke tahap sesuai urutan jabatan.';

                if (!steps.length) {
                    warning.textContent = 'Pilih minimal satu tahap persetujuan.';
                    warning.className = 'mt-1 text-amber-700';
                    return;
                }

                const coverageUnavailable = steps.some((step) => Number(coverage[step]?.total ?? 0) === 0);
                const uncovered = steps.filter((step) => Number(coverage[step]?.total ?? 0) > 0 && Number(coverage[step]?.covered ?? 0) < Number(coverage[step]?.total ?? 0));
                if (coverageUnavailable || uncovered.length) {
                    const messages = [];
                    if (coverageUnavailable) messages.push(`Belum ada pemohon aktif pada level ${card.dataset.requesterLabel} untuk menghitung cakupan approver.`);
                    messages.push(...uncovered.map((step) => {
                        const available = coverage[step];
                        return `${labels[step] ?? step} tersedia pada ${available.covered}/${available.total} kombinasi departemen/lokasi.`;
                    }));
                    warning.textContent = messages.join(' ');
                    warning.className = 'mt-1 text-amber-700';
                } else {
                    const first = coverage[steps[0]];
                    warning.textContent = `Approver aktif tersedia pada seluruh ${first.total} kombinasi departemen/lokasi yang digunakan.`;
                    warning.className = 'mt-1 text-emerald-700';
                }
            };

            fields.forEach((field) => field.addEventListener('change', updateWorkflowPreview));
            updateWorkflowPreview();
        });
    })();
</script>
@endsection
