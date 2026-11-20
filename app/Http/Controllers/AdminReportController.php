<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\OfficeSetting;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use XMLWriter;
use ZipArchive;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminReportController extends Controller
{
    private const ATTENDANCE_STATUSES = ['Tepat Waktu', 'Terlambat'];

    public function index(Request $request): View
    {
        $filters = $this->validatedFilters($request);
        $attendance = Attendance::query()
            ->whereBetween('attendance_date', [$filters['from'], $filters['to']])
            ->when($filters['office_id'], fn ($query) => $query->where('office_id', $filters['office_id']));
        $leaveRequests = LeaveRequest::query()
            ->where('start_date', '<=', $filters['to'])
            ->where('end_date', '>=', $filters['from'])
            ->when($filters['office_id'], fn ($query) => $query->where('office_id', $filters['office_id']));
        $attendanceSummary = (clone $attendance)
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as late_total', ['Terlambat'])
            ->first();
        $leaveSummary = (clone $leaveRequests)
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as pending_total, COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as approved_total', ['Menunggu', 'Disetujui'])
            ->first();

        return view('admin.reports.index', [
            'filters' => $filters,
            'offices' => OfficeSetting::query()->orderBy('name')->get(),
            'employeeCount' => User::query()->whereNotIn('role', ['admin', 'superadmin'])->where('is_active', true)->count(),
            'attendanceCount' => $attendanceSummary->total,
            'lateCount' => $attendanceSummary->late_total,
            'leaveCount' => $leaveSummary->total,
            'pendingLeaveCount' => $leaveSummary->pending_total,
            'approvedLeaveCount' => $leaveSummary->approved_total,
            'canViewAttendanceReport' => $request->user()->canAccessMenu('report_attendance'),
            'canViewLeaveReport' => $request->user()->canAccessMenu('report_leaves'),
        ]);
    }

    public function attendance(Request $request): View
    {
        $filters = $this->validatedFilters($request, self::ATTENDANCE_STATUSES);

        return view('admin.reports.attendance', [
            'filters' => $filters,
            'offices' => OfficeSetting::query()->orderBy('name')->get(),
            'records' => $this->attendanceQuery($filters)->paginate(25)->withQueryString(),
            'canViewReportOverview' => $request->user()->canAccessMenu('report_overview'),
        ]);
    }

    public function leaveRequests(Request $request): View
    {
        $filters = $this->validatedFilters($request, LeaveRequest::STATUSES);

        return view('admin.reports.leaves', [
            'filters' => $filters,
            'offices' => OfficeSetting::query()->orderBy('name')->get(),
            'records' => $this->leaveQuery($filters)->paginate(25)->withQueryString(),
            'canViewReportOverview' => $request->user()->canAccessMenu('report_overview'),
        ]);
    }

    public function printAttendance(Request $request): View
    {
        $filters = $this->validatedFilters($request, self::ATTENDANCE_STATUSES);

        return view('admin.reports.print-attendance', [
            'filters' => $filters,
            'officeName' => $filters['office_id'] ? OfficeSetting::find($filters['office_id'])?->name : 'Semua lokasi',
            'records' => $this->attendanceQuery($filters)->get(),
            'printedAt' => now(),
        ]);
    }

    public function printLeaveRequests(Request $request): View
    {
        $filters = $this->validatedFilters($request, LeaveRequest::STATUSES);

        return view('admin.reports.print-leaves', [
            'filters' => $filters,
            'officeName' => $filters['office_id'] ? OfficeSetting::find($filters['office_id'])?->name : 'Semua lokasi',
            'records' => $this->leaveQuery($filters)->get(),
            'printedAt' => now(),
        ]);
    }

    public function exportAttendance(Request $request): BinaryFileResponse
    {
        $filters = $this->validatedFilters($request, self::ATTENDANCE_STATUSES);
        $rows = $this->attendanceQuery($filters);

        return $this->xlsxDownload(
            $rows,
            ['Tanggal', 'Nama karyawan', 'Username', 'NIK', 'Departemen', 'Lokasi kerja', 'Jam masuk', 'Jam pulang', 'Status'],
            fn (Attendance $row) => [
                $row->attendance_date->format('d-m-Y'),
                $row->user?->name,
                $row->user?->username,
                $row->user?->nik,
                $row->user?->department?->name,
                $row->office?->name,
                $row->check_in?->format('H:i'),
                $row->check_out?->format('H:i'),
                $row->status,
            ],
            'laporan-absensi.xlsx',
        );
    }

    public function exportLeaveRequests(Request $request): BinaryFileResponse
    {
        $filters = $this->validatedFilters($request, LeaveRequest::STATUSES);
        $rows = $this->leaveQuery($filters);

        return $this->xlsxDownload(
            $rows,
            ['Diajukan', 'Nama karyawan', 'Username', 'NIK', 'Departemen', 'Lokasi kerja', 'Jenis izin', 'Kategori khusus', 'Tanggal mulai', 'Tanggal selesai', 'Status', 'Alasan', 'Diproses oleh', 'Catatan keputusan'],
            fn (LeaveRequest $row) => [
                $row->created_at?->format('d-m-Y H:i'),
                $row->user?->name,
                $row->user?->username,
                $row->user?->nik,
                $row->user?->department?->name,
                $row->office?->name,
                $row->leave_type,
                $row->special_type,
                $row->start_date->format('d-m-Y'),
                $row->end_date->format('d-m-Y'),
                $row->status,
                $row->reason,
                $row->reviewer?->name,
                $row->review_note,
            ],
            'laporan-pengajuan-izin.xlsx',
        );
    }

    private function validatedFilters(Request $request, array $statuses = []): array
    {
        $rules = [
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'office_id' => ['nullable', 'integer', 'exists:office_settings,id'],
            'q' => ['nullable', 'string', 'max:100'],
        ];
        if ($statuses) {
            $rules['status'] = ['nullable', Rule::in($statuses)];
        }

        $data = $request->validate($rules);

        $defaultFrom = isset($data['to'])
            ? \Illuminate\Support\Carbon::parse($data['to'])->startOfMonth()->toDateString()
            : now()->startOfMonth()->toDateString();

        return [
            'from' => $data['from'] ?? $defaultFrom,
            'to' => $data['to'] ?? now()->toDateString(),
            'office_id' => $data['office_id'] ?? '',
            'status' => $data['status'] ?? '',
            'q' => trim($data['q'] ?? ''),
        ];
    }

    private function attendanceQuery(array $filters)
    {
        return Attendance::query()
            ->with(['user.department', 'office'])
            ->whereBetween('attendance_date', [$filters['from'], $filters['to']])
            ->when($filters['office_id'], fn ($query) => $query->where('office_id', $filters['office_id']))
            ->when($filters['status'], fn ($query) => $query->where('status', $filters['status']))
            ->when($filters['q'], fn ($query) => $query->whereHas('user', fn ($userQuery) => $userQuery->where(function ($query) use ($filters) {
                $search = '%'.$filters['q'].'%';
                $query->where('name', 'like', $search)
                    ->orWhere('username', 'like', $search)
                    ->orWhere('nik', 'like', $search);
            })))
            ->orderByDesc('attendance_date')
            ->orderBy('user_id');
    }

    private function leaveQuery(array $filters)
    {
        return LeaveRequest::query()
            ->with(['user.department', 'office', 'reviewer'])
            ->where('start_date', '<=', $filters['to'])
            ->where('end_date', '>=', $filters['from'])
            ->when($filters['office_id'], fn ($query) => $query->where('office_id', $filters['office_id']))
            ->when($filters['status'], fn ($query) => $query->where('status', $filters['status']))
            ->when($filters['q'], fn ($query) => $query->whereHas('user', fn ($userQuery) => $userQuery->where(function ($query) use ($filters) {
                $search = '%'.$filters['q'].'%';
                $query->where('name', 'like', $search)
                    ->orWhere('username', 'like', $search)
                    ->orWhere('nik', 'like', $search);
            })))
            ->orderByDesc('start_date')
            ->orderByDesc('id');
    }

    private function xlsxDownload($query, array $headers, Closure $rowMapper, string $filename): BinaryFileResponse
    {
        if (! class_exists(ZipArchive::class)) {
            abort(500, 'Ekspor Excel memerlukan ekstensi PHP zip.');
        }

        $archivePath = tempnam(sys_get_temp_dir(), 'hadirkantor-xlsx-');
        $sheetPath = tempnam(sys_get_temp_dir(), 'hadirkantor-sheet-');
        if ($archivePath === false || $sheetPath === false) {
            abort(500, 'File laporan sementara tidak dapat dibuat.');
        }

        try {
            $lastRow = (clone $query)->count() + 1;
            $lastColumn = $this->spreadsheetColumn(count($headers));
            $xml = new XMLWriter;
            $xml->openUri($sheetPath);
            $xml->startDocument('1.0', 'UTF-8');
            $xml->startElement('worksheet');
            $xml->writeAttribute('xmlns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $xml->startElement('dimension');
            $xml->writeAttribute('ref', 'A1:'.$lastColumn.$lastRow);
            $xml->endElement();
            $xml->startElement('sheetViews');
            $xml->startElement('sheetView');
            $xml->writeAttribute('workbookViewId', '0');
            $xml->startElement('pane');
            $xml->writeAttribute('ySplit', '1');
            $xml->writeAttribute('topLeftCell', 'A2');
            $xml->writeAttribute('activePane', 'bottomLeft');
            $xml->writeAttribute('state', 'frozen');
            $xml->endElement();
            $xml->endElement();
            $xml->endElement();
            $xml->startElement('cols');
            foreach ($headers as $index => $header) {
                $xml->startElement('col');
                $xml->writeAttribute('min', (string) ($index + 1));
                $xml->writeAttribute('max', (string) ($index + 1));
                $xml->writeAttribute('width', (string) min(34, max(14, mb_strlen($header) + 5)));
                $xml->writeAttribute('customWidth', '1');
                $xml->endElement();
            }
            $xml->endElement();
            $xml->startElement('sheetData');
            $rowNumber = 1;
            $this->writeSpreadsheetRow($xml, $headers, $rowNumber++, 1);
            $query->reorder()->chunkById(500, function ($records) use ($xml, $rowMapper, &$rowNumber): void {
                foreach ($records as $record) {
                    $this->writeSpreadsheetRow($xml, $rowMapper($record), $rowNumber, $rowNumber % 2 === 0 ? 2 : 3);
                    $rowNumber++;
                }
            });
            $xml->endElement();
            $xml->startElement('autoFilter');
            $xml->writeAttribute('ref', 'A1:'.$lastColumn.$lastRow);
            $xml->endElement();
            $xml->startElement('pageMargins');
            $xml->writeAttribute('left', '0.25');
            $xml->writeAttribute('right', '0.25');
            $xml->writeAttribute('top', '0.5');
            $xml->writeAttribute('bottom', '0.5');
            $xml->writeAttribute('header', '0.2');
            $xml->writeAttribute('footer', '0.2');
            $xml->endElement();
            $xml->startElement('pageSetup');
            $xml->writeAttribute('orientation', 'landscape');
            $xml->writeAttribute('fitToWidth', '1');
            $xml->writeAttribute('fitToHeight', '0');
            $xml->writeAttribute('paperSize', '9');
            $xml->endElement();
            $xml->endElement();
            $xml->endDocument();
            $xml->flush();

            $zip = new ZipArchive;
            if ($zip->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('File Excel tidak dapat dibuat.');
            }
            $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>');
            $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
            $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Laporan" sheetId="1" r:id="rId1"/></sheets></workbook>');
            $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');
            $zip->addFromString('xl/styles.xml', $this->spreadsheetStyles());
            $zip->addFile($sheetPath, 'xl/worksheets/sheet1.xml');
            $zip->close();
        } catch (\Throwable $exception) {
            @unlink($archivePath);
            throw $exception;
        } finally {
            @unlink($sheetPath);
        }

        return response()->download($archivePath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function spreadsheetStyles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="2"><font><sz val="11"/><name val="Aptos"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Aptos Display"/></font></fonts>'
            .'<fills count="4"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF567765"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFEAF2EC"/><bgColor indexed="64"/></patternFill></fill></fills>'
            .'<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left style="thin"><color rgb="FFD8E2DA"/></left><right style="thin"><color rgb="FFD8E2DA"/></right><top style="thin"><color rgb="FFD8E2DA"/></top><bottom style="thin"><color rgb="FFD8E2DA"/></bottom><diagonal/></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="4"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center" horizontal="center" wrapText="1"/></xf><xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyFill="1" applyBorder="1"/><xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"/></cellXfs>'
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            .'</styleSheet>';
    }

    private function writeSpreadsheetRow(XMLWriter $xml, array $values, int $rowNumber, int $style): void
    {
        $xml->startElement('row');
        $xml->writeAttribute('r', (string) $rowNumber);
        if ($style === 1) {
            $xml->writeAttribute('ht', '28');
            $xml->writeAttribute('customHeight', '1');
        }
        foreach (array_values($values) as $index => $value) {
            $xml->startElement('c');
            $xml->writeAttribute('r', $this->spreadsheetColumn($index + 1).$rowNumber);
            $xml->writeAttribute('s', (string) $style);
            $xml->writeAttribute('t', 'inlineStr');
            $xml->startElement('is');
            $xml->startElement('t');
            $xml->writeAttribute('xml:space', 'preserve');
            $xml->text((string) ($value ?? ''));
            $xml->endElement();
            $xml->endElement();
            $xml->endElement();
        }
        $xml->endElement();
    }

    private function spreadsheetColumn(int $number): string
    {
        $column = '';
        while ($number > 0) {
            $number--;
            $column = chr(65 + ($number % 26)).$column;
            $number = intdiv($number, 26);
        }

        return $column;
    }
}
