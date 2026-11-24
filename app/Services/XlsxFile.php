<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use RuntimeException;
use SimpleXMLElement;
use XMLWriter;
use ZipArchive;

class XlsxFile
{
    public static function download(
        string $filename,
        array $headers,
        iterable $rows,
        array $guide = [],
        array $referenceHeaders = [],
        iterable $referenceRows = [],
        array $validations = [],
    )
    {
        $referenceRows = is_array($referenceRows) ? $referenceRows : iterator_to_array($referenceRows);

        if (! class_exists(ZipArchive::class) || ! class_exists(XMLWriter::class)) {
            abort(500, 'Ekspor Excel memerlukan ekstensi PHP zip dan xmlwriter.');
        }

        $archivePath = tempnam(sys_get_temp_dir(), 'hadirkantor-xlsx-');
        $sheetPath = tempnam(sys_get_temp_dir(), 'hadirkantor-sheet-');
        $guidePath = $guide ? tempnam(sys_get_temp_dir(), 'hadirkantor-guide-') : null;
        $referencePath = $referenceHeaders ? tempnam(sys_get_temp_dir(), 'hadirkantor-reference-') : null;
        if ($archivePath === false || $sheetPath === false || ($guide && $guidePath === false) || ($referenceHeaders && $referencePath === false)) {
            abort(500, 'File Excel sementara tidak dapat dibuat.');
        }

        try {
            if ($guidePath) {
                self::writeWorksheet($guidePath, ['Kolom', 'Petunjuk pengisian'], $guide);
            }
            if ($referencePath) {
                self::writeWorksheet($referencePath, $referenceHeaders, $referenceRows);
            }
            self::writeWorksheet($sheetPath, $headers, $rows, $validations);

            $zip = new ZipArchive;
            if ($zip->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('File Excel tidak dapat dibuat.');
            }
            $guideContentType = $guidePath ? '<Override PartName="/xl/worksheets/sheet2.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' : '';
            $guideSheet = $guidePath ? '<sheet name="Petunjuk" sheetId="2" r:id="rId2"/>' : '';
            $guideRelationship = $guidePath ? '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet2.xml"/>' : '';
            $referenceContentType = $referencePath ? '<Override PartName="/xl/worksheets/sheet3.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' : '';
            $referenceSheet = $referencePath ? '<sheet name="Referensi" sheetId="3" r:id="rId3"/>' : '';
            $referenceRelationship = $referencePath ? '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet3.xml"/>' : '';
            $styleRelationshipId = $referencePath ? 'rId4' : ($guidePath ? 'rId3' : 'rId2');
            $definedNames = '';
            foreach ($validations as $column => $name) {
                $referenceColumn = match ($name) {
                    'DepartmentOptions' => 'A',
                    'ApprovalLevelOptions' => 'B',
                    'OfficeOptions' => 'C',
                    'RoleOptions' => 'D',
                    default => null,
                };
                if ($referenceColumn !== null) {
                    $lastReferenceRow = max(2, count($referenceRows) + 1);
                    $definedNames .= '<definedName name="'.$name.'">\'Referensi\'!$'.$referenceColumn.'$2:$'.$referenceColumn.'$'.$lastReferenceRow.'</definedName>';
                }
            }
            $definedNamesXml = $definedNames === '' ? '' : '<definedNames>'.$definedNames.'</definedNames>';
            $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'.$guideContentType.$referenceContentType.'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>');
            $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
            $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Karyawan" sheetId="1" r:id="rId1"/>'.$guideSheet.$referenceSheet.'</sheets>'.$definedNamesXml.'</workbook>');
            $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'.$guideRelationship.$referenceRelationship.'<Relationship Id="'.$styleRelationshipId.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');
            $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Aptos"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Aptos"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF567765"/></patternFill></fill></fills><borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>');
            $zip->addFile($sheetPath, 'xl/worksheets/sheet1.xml');
            if ($guidePath) {
                $zip->addFile($guidePath, 'xl/worksheets/sheet2.xml');
            }
            if ($referencePath) {
                $zip->addFile($referencePath, 'xl/worksheets/sheet3.xml');
            }
            $zip->close();
        } catch (\Throwable $exception) {
            @unlink($archivePath);
            throw $exception;
        } finally {
            @unlink($sheetPath);
            if ($guidePath) {
                @unlink($guidePath);
            }
            if ($referencePath) {
                @unlink($referencePath);
            }
        }

        return response()->download($archivePath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /** @return array<int, array{row: int, cells: array<int, string>}> */
    public static function read(UploadedFile $file): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Impor Excel memerlukan ekstensi PHP zip. Aktifkan ekstensi zip pada PHP yang menjalankan aplikasi.');
        }

        if (! class_exists(SimpleXMLElement::class) || ! function_exists('simplexml_load_string')) {
            throw new RuntimeException('Impor Excel memerlukan ekstensi PHP xml (SimpleXML). Aktifkan ekstensi xml pada PHP yang menjalankan aplikasi, lalu mulai ulang PHP/Herd.');
        }

        $zip = new ZipArchive;
        if ($zip->open($file->getRealPath()) !== true) {
            throw new RuntimeException('File Excel tidak dapat dibuka.');
        }

        try {
            $sheetContents = $zip->getFromName('xl/worksheets/sheet1.xml');
            if ($sheetContents === false || strlen($sheetContents) > 25_000_000) {
                throw new RuntimeException('Sheet pertama tidak ditemukan atau terlalu besar.');
            }

            $sharedStrings = [];
            $sharedContents = $zip->getFromName('xl/sharedStrings.xml');
            if ($sharedContents !== false) {
                $sharedXml = self::parseXml($sharedContents);
                $sharedXml->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
                foreach ($sharedXml->xpath('//x:si') ?: [] as $item) {
                    $item->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
                    $sharedStrings[] = implode('', array_map('strval', $item->xpath('.//x:t') ?: []));
                }
            }

            $sheet = self::parseXml($sheetContents);
            $sheet->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $rows = [];
            foreach ($sheet->xpath('//x:sheetData/x:row') ?: [] as $row) {
                $row->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
                $rowNumber = (int) $row['r'];
                $cells = [];
                foreach ($row->xpath('./x:c') ?: [] as $cell) {
                    $cell->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
                    preg_match('/^([A-Z]+)/', (string) $cell['r'], $match);
                    $column = self::columnNumber($match[1] ?? 'A');
                    $type = (string) $cell['t'];
                    if ($type === 'inlineStr') {
                        $value = implode('', array_map('strval', $cell->xpath('./x:is//x:t') ?: []));
                    } else {
                        $value = (string) ($cell->v ?? '');
                        if ($type === 's') {
                            $value = $sharedStrings[(int) $value] ?? '';
                        }
                    }
                    $cells[$column] = trim($value);
                }

                $hasData = count(array_filter($cells, fn (string $value) => $value !== '')) > 0;
                if ($rowNumber > 1 && ! $hasData) {
                    continue;
                }

                $rows[] = ['row' => $rowNumber, 'cells' => $cells];
            }

            return $rows;
        } finally {
            $zip->close();
        }
    }

    private static function parseXml(string $contents): SimpleXMLElement
    {
        $xml = simplexml_load_string($contents, SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOBLANKS);
        if ($xml === false) {
            throw new RuntimeException('Struktur file Excel tidak valid.');
        }

        return $xml;
    }

    private static function writeWorksheet(string $path, array $headers, iterable $rows, array $validations = []): void
    {
        $xml = new XMLWriter;
        $xml->openUri($path);
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('worksheet');
        $xml->writeAttribute('xmlns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
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
        $xml->startElement('sheetData');
        self::writeRow($xml, $headers, 1, true);
        $rowNumber = 2;
        foreach ($rows as $row) {
            self::writeRow($xml, $row, $rowNumber++);
        }
        $xml->endElement();
        $xml->startElement('autoFilter');
        $xml->writeAttribute('ref', 'A1:'.self::column(count($headers)).max(1, $rowNumber - 1));
        $xml->endElement();
        if ($validations) {
            $xml->startElement('dataValidations');
            $xml->writeAttribute('count', (string) count($validations));
            foreach ($validations as $column => $name) {
                $xml->startElement('dataValidation');
                $xml->writeAttribute('type', 'list');
                $xml->writeAttribute('allowBlank', '1');
                $xml->writeAttribute('showErrorMessage', '1');
                $xml->writeAttribute('errorTitle', 'Pilihan tidak valid');
                $xml->writeAttribute('error', 'Pilih nilai dari daftar pada sheet Referensi.');
                $xml->writeAttribute('sqref', $column.'2:'.$column.'5001');
                $xml->writeElement('formula1', '='.$name);
                $xml->endElement();
            }
            $xml->endElement();
        }
        $xml->endElement();
        $xml->endDocument();
        $xml->flush();
    }

    private static function writeRow(XMLWriter $xml, array $values, int $rowNumber, bool $header = false): void
    {
        $xml->startElement('row');
        $xml->writeAttribute('r', (string) $rowNumber);
        foreach (array_values($values) as $index => $value) {
            $xml->startElement('c');
            $xml->writeAttribute('r', self::column($index + 1).$rowNumber);
            $xml->writeAttribute('s', $header ? '1' : '0');
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

    private static function column(int $number): string
    {
        $column = '';
        while ($number > 0) {
            $number--;
            $column = chr(65 + ($number % 26)).$column;
            $number = intdiv($number, 26);
        }

        return $column;
    }

    private static function columnNumber(string $column): int
    {
        $number = 0;
        foreach (str_split($column) as $letter) {
            $number = $number * 26 + ord($letter) - 64;
        }

        return $number;
    }
}
