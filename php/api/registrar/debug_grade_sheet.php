<?php
/**
 * DEBUG: Show what the Excel parser reads from a grade sheet
 * GET ?id=<submission_id>
 * Remove this file in production!
 */
require_once '../../config.php';
if (!isLoggedIn() || !hasRole('registrar')) { die('Access denied'); }

header('Content-Type: application/json');

$id   = (int)($_GET['id'] ?? 0);
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT file_path, subject_id, semester, school_year FROM grade_submissions WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$sub = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$sub || empty($sub['file_path'])) { echo json_encode(['error' => 'Not found']); exit(); }

$docRoot  = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
$filePath = $docRoot . str_replace('/', DIRECTORY_SEPARATOR, $sub['file_path']);

if (!file_exists($filePath)) { echo json_encode(['error' => 'File missing: ' . $filePath]); exit(); }

$rows = readXlsxRows($filePath);
echo json_encode(['file' => $filePath, 'rows' => $rows, 'row_count' => count($rows ?? [])], JSON_PRETTY_PRINT);

function readXlsxRows($filePath) {
    $zip = new ZipArchive();
    if ($zip->open($filePath) !== true) return ['error' => 'Cannot open zip'];
    $sharedStrings = [];
    $ssXml = $zip->getFromName('xl/sharedStrings.xml');
    if ($ssXml !== false) {
        $ss = @simplexml_load_string($ssXml);
        if ($ss) {
            foreach ($ss->si as $si) {
                $text = '';
                foreach ($si->r as $r) { $text .= (string)($r->t ?? ''); }
                if ($text === '' && isset($si->t)) $text = (string)$si->t;
                $sharedStrings[] = $text;
            }
        }
    }
    $sheetXml = false;
    for ($s = 1; $s <= 5; $s++) {
        $sheetXml = $zip->getFromName("xl/worksheets/sheet{$s}.xml");
        if ($sheetXml !== false) break;
    }
    $zip->close();
    if (!$sheetXml) return ['error' => 'No sheet found'];
    $sheet = @simplexml_load_string($sheetXml);
    if (!$sheet) return ['error' => 'Cannot parse sheet'];
    $rows = [];
    foreach ($sheet->sheetData->row as $row) {
        $rowData = [];
        foreach ($row->c as $cell) {
            $ref    = (string)$cell['r'];
            $col    = preg_replace('/[0-9]/', '', $ref);
            $colIdx = colLetterToIndex($col);
            while (count($rowData) < $colIdx) $rowData[] = '';
            $type  = (string)($cell['t'] ?? '');
            $value = (string)($cell->v ?? '');
            if ($type === 's') $value = '(shared:' . $value . ')=' . ($sharedStrings[(int)$value] ?? '?');
            $rowData[$colIdx] = "[$type]$value";
        }
        $rows[] = $rowData;
    }
    return $rows;
}
function colLetterToIndex($l) {
    $l = strtoupper($l); $idx = 0;
    for ($i = 0; $i < strlen($l); $i++) $idx = $idx * 26 + (ord($l[$i]) - 64);
    return $idx - 1;
}
