<?php
ob_start();
include("partials/header.php");
require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

function generateLIN($conn) {
    $prefix = "JPM";
    $year = date("Y");
    $sql = "SELECT LIN FROM students WHERE LIN LIKE '$prefix/$year/%' ORDER BY student_id DESC LIMIT 1";
    $res = mysqli_query($conn, $sql);
    $nextNum = 1;
    if ($res && mysqli_num_rows($res) > 0) {
        $last = mysqli_fetch_assoc($res)['LIN'];
        $parts = explode('/', $last);
        $lastNum = intval(end($parts));
        $nextNum = $lastNum + 1;
    }
    return "$prefix/$year/" . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
}

function normalizeDateValue($value) {
    if ($value === null || $value === '') {
        return '';
    }
    if (is_numeric($value)) {
        try {
            return Date::excelToDateTimeObject($value)->format('Y-m-d');
        } catch (Exception $e) {
            // continue to string parse
        }
    }
    $value = trim($value);
    if ($value === '') {
        return '';
    }
    $value = str_replace('/', '-', $value);
    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return '';
    }
    return date('Y-m-d', $timestamp);
}

function mapHeaderName($header) {
    $header = trim(strtolower($header));
    $header = preg_replace('/[\s_]+/', ' ', $header);

    if (preg_match('/^(first\s*name|firstname|first_name)$/', $header)) return 'first_name';
    if (preg_match('/^(last\s*name|lastname|last_name)$/', $header)) return 'last_name';
    if (preg_match('/^gender$/', $header)) return 'gender';
    if (preg_match('/^(date\s*of\s*birth|dob|birth\s*date)$/', $header)) return 'dob';
    if (preg_match('/^(lin|reg\s*no|registration\s*number|reg\s*number)$/', $header)) return 'LIN';
    if (preg_match('/^(status)$/', $header)) return 'status';
    if (preg_match('/^(class\s*name|class|class_name|class_id)$/', $header)) return 'class_name';
    if (preg_match('/^(stream\s*name|stream|stream_name|stream_id)$/', $header)) return 'stream_name';
    return null;
}

function lookupClassId($conn, $name) {
    if (is_numeric($name)) {
        return intval($name);
    }
    $name = mysqli_real_escape_string($conn, trim($name));
    if ($name === '') {
        return null;
    }
    $sql = "SELECT id FROM classes WHERE LOWER(class_name) = LOWER('$name') LIMIT 1";
    $res = mysqli_query($conn, $sql);
    if ($res && mysqli_num_rows($res) > 0) {
        return intval(mysqli_fetch_assoc($res)['id']);
    }
    return null;
}

function lookupStreamId($conn, $name, $classId = null) {
    if (is_numeric($name)) {
        return intval($name);
    }
    $name = mysqli_real_escape_string($conn, trim($name));
    if ($name === '') {
        return null;
    }
    $sql = "SELECT id FROM streams WHERE LOWER(stream_name) = LOWER('$name')";
    if ($classId) {
        $sql .= " AND class_id = $classId";
    }
    $sql .= " LIMIT 1";
    $res = mysqli_query($conn, $sql);
    if ($res && mysqli_num_rows($res) > 0) {
        return intval(mysqli_fetch_assoc($res)['id']);
    }
    return null;
}

function getUploadErrorMessage($errorCode) {
    switch ($errorCode) {
        case UPLOAD_ERR_INI_SIZE:   return 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
        case UPLOAD_ERR_FORM_SIZE:  return 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form.';
        case UPLOAD_ERR_PARTIAL:    return 'The uploaded file was only partially uploaded.';
        case UPLOAD_ERR_NO_FILE:    return 'No file was uploaded.';
        case UPLOAD_ERR_NO_TMP_DIR: return 'Missing a temporary folder.';
        case UPLOAD_ERR_CANT_WRITE: return 'Failed to write file to disk.';
        case UPLOAD_ERR_EXTENSION:  return 'A PHP extension stopped the file upload.';
        default:                    return 'Unknown upload error occurred.';
    }
}

$errors       = [];
$inserted     = 0;
$skipped      = 0;
$duplicateLins = [];
$rowErrors    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['student_file'])) {
        $errors[] = 'No file was uploaded. Please select a file to upload.';
    } elseif ($_FILES['student_file']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload error: ' . getUploadErrorMessage($_FILES['student_file']['error']);
    } else {
        $file       = $_FILES['student_file'];
        $ext        = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed    = ['xls', 'xlsx', 'ods', 'csv'];
        $maxFileSize = 5 * 1024 * 1024;

        if (!in_array($ext, $allowed)) {
            $errors[] = 'Only XLS, XLSX, ODS, and CSV files are allowed.';
        } elseif ($file['size'] > $maxFileSize) {
            $errors[] = 'File size exceeds the maximum allowed limit of 5MB.';
        } elseif ($file['size'] === 0) {
            $errors[] = 'The uploaded file is empty.';
        } else {
            $uploadDir = __DIR__ . '/uploads';
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    $errors[] = 'Unable to create upload folder. Check server permissions.';
                }
            }
            if (empty($errors) && !is_writable($uploadDir)) {
                $errors[] = 'Upload folder is not writable. Check server permissions.';
            }

            $uploadedPath = $uploadDir . '/student_import_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;

            if (empty($errors) && !move_uploaded_file($file['tmp_name'], $uploadedPath)) {
                $phpError = error_get_last();
                $detail   = $phpError['message'] ?? 'unknown';
                $errors[] = 'File upload failed. Please try again. (' . htmlspecialchars($detail) . ')';
            } elseif (empty($errors)) {
                try {
                    $reader      = IOFactory::createReaderForFile($uploadedPath);
                    $spreadsheet = $reader->load($uploadedPath);
                    $worksheet   = $spreadsheet->getActiveSheet();
                    $rows        = $worksheet->toArray(null, true, true, true);

                    $headerRow = null;
                    $columnMap = [];

                    foreach ($rows as $rowNumber => $rowValues) {
                        $rowText = implode(' ', array_map('strval', $rowValues));
                        if (trim($rowText) === '') continue;
                        $headerRow = $rowNumber;
                        foreach ($rowValues as $col => $value) {
                            $mapped = mapHeaderName((string)$value);
                            if ($mapped) {
                                $columnMap[$col] = $mapped;
                            }
                        }
                        break;
                    }

                    if ($headerRow === null || empty($columnMap)) {
                        $errors[] = 'The uploaded file does not contain recognizable header columns.';
                    } else {
                        // FIX: iterate by actual array keys, not integer index
                        foreach ($rows as $rowNumber => $rowValues) {
                            if ($rowNumber <= $headerRow) continue;

                            // Skip completely blank rows
                            $rowText = implode('', array_map('strval', $rowValues));
                            if (trim($rowText) === '') continue;

                            $record = [];
                            foreach ($columnMap as $col => $field) {
                                $record[$field] = isset($rowValues[$col]) ? trim((string)$rowValues[$col]) : '';
                            }

                            $firstName  = strtoupper(trim($record['first_name'] ?? ''));
                            $lastName   = strtoupper(trim($record['last_name']  ?? ''));
                            $gender     = strtoupper(trim($record['gender']     ?? ''));
                            $dob        = normalizeDateValue($record['dob']     ?? '');
                            $lin        = trim($record['LIN']                   ?? '');
                            $status     = strtolower(trim($record['status']     ?? ''));
                            $className  = trim($record['class_name']            ?? '');
                            $streamName = trim($record['stream_name']           ?? '');

                            // Validate required fields
                            if (
                                $firstName === '' ||
                                $lastName  === '' ||
                                !in_array($gender, ['MALE', 'FEMALE']) ||
                                $dob       === '' ||
                                !in_array($status, ['day', 'boarding'])
                            ) {
                                $skipped++;
                                continue;
                            }

                            if ($lin === '') {
                                $lin = generateLIN($conn);
                            }

                            $classId  = lookupClassId($conn, $className);
                            $streamId = lookupStreamId($conn, $streamName, $classId);

                            // Check for duplicate LIN
                            $checkDuplicate = "SELECT student_id FROM students WHERE LIN = '" . mysqli_real_escape_string($conn, $lin) . "' LIMIT 1";
                            $resDup = mysqli_query($conn, $checkDuplicate);
                            if ($resDup && mysqli_num_rows($resDup) > 0) {
                                $duplicateLins[] = $lin;
                                $skipped++;
                                continue;
                            }

                            $firstName  = mysqli_real_escape_string($conn, $firstName);
                            $lastName   = mysqli_real_escape_string($conn, $lastName);
                            $gender     = mysqli_real_escape_string($conn, $gender);
                            $dob        = mysqli_real_escape_string($conn, $dob);
                            $lin        = mysqli_real_escape_string($conn, $lin);
                            $status     = mysqli_real_escape_string($conn, $status);
                            $classSql   = $classId  ? intval($classId)  : 'NULL';
                            $streamSql  = $streamId ? intval($streamId) : 'NULL';

                            $sql = "INSERT INTO students (first_name, last_name, gender, dob, LIN, class_id, stream_id, status)
                                    VALUES ('{$firstName}', '{$lastName}', '{$gender}', '{$dob}', '{$lin}', {$classSql}, {$streamSql}, '{$status}')";

                            if (mysqli_query($conn, $sql)) {
                                $inserted++;
                            } else {
                                $skipped++;
                                $rowErrors[] = "Row {$rowNumber}: " . mysqli_error($conn);
                            }
                        }
                    }
                } catch (Exception $e) {
                    $errors[] = 'Unable to read the uploaded file. Please use a valid Excel/ODS/CSV template. (' . htmlspecialchars($e->getMessage()) . ')';
                }

                if (file_exists($uploadedPath)) {
                    unlink($uploadedPath);
                }
            }
        }
    }
}
?>

<div class="container-fluid my-4">

    <div class="row mb-3">
        <div class="col-lg-8">
            <h4 class="text-uppercase fw-bold mb-3">Upload Student Registration Form</h4>
            <p>Download the registration template from the students page, fill it, then upload it here. Accepted file types: XLS, XLSX, ODS, CSV.</p>
        </div>
        <div class="col-lg-4 text-lg-end">
            <a href="<?= SITEURL ?>students.php" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i> Back to Students
            </a>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <h5 class="alert-heading"><i class="fa-solid fa-triangle-exclamation"></i> Upload Failed</h5>
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <hr>
            <p class="mb-0 small">Please check your file and try again. Make sure it's a valid Excel/ODS/CSV file under 5MB.</p>
        </div>
    <?php endif; ?>

    <?php if ($inserted > 0 || $skipped > 0): ?>
        <div class="alert alert-success">
            <h5 class="alert-heading"><i class="fa-solid fa-circle-check"></i> Import Complete</h5>
            <p><strong><?= $inserted ?></strong> student(s) imported successfully.</p>
            <?php if ($skipped > 0): ?>
                <p><strong><?= $skipped ?></strong> row(s) were skipped (invalid data or duplicates).</p>
            <?php endif; ?>
            <?php if (!empty($duplicateLins)): ?>
                <p>Duplicate LIN(s) skipped: <code><?= htmlspecialchars(implode(', ', array_unique($duplicateLins))) ?></code></p>
            <?php endif; ?>
            <?php if (!empty($rowErrors)): ?>
                <h6 class="mb-2">Rows skipped due to database errors:</h6>
                <ul class="mb-0">
                    <?php foreach ($rowErrors as $rowError): ?>
                        <li><?= htmlspecialchars($rowError) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <hr>
            <a href="<?= SITEURL ?>students.php" class="btn btn-success btn-sm">
                <i class="fa-solid fa-users"></i> View Students
            </a>
        </div>
    <?php endif; ?>

    <!-- THE FORM (this was the missing piece) -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fa-solid fa-file-import"></i> Upload Excel / CSV File</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" id="uploadForm">
                        <div class="mb-3">
                            <label for="student_file" class="form-label fw-semibold">Select File</label>
                            <input type="file"
                                   class="form-control"
                                   name="student_file"
                                   id="student_file"
                                   accept=".xls,.xlsx,.ods,.csv">
                            <div class="form-text">Accepted formats: XLS, XLSX, ODS, CSV. Max size: 5MB.</div>
                        </div>

                        <div class="alert alert-info py-2 small mb-3">
                            <strong>Required columns in your file:</strong><br>
                            First Name, Last Name, Gender (<em>Male/Female</em>), DOB, Status (<em>day/boarding</em>)<br>
                            <strong>Optional:</strong> LIN, Class Name, Stream Name
                        </div>

                        <button type="submit" class="btn btn-primary w-100" id="uploadBtn">
                            <i class="fa-solid fa-upload"></i> Upload and Import
                        </button>
                    </form>
                </div>
            </div>

            <div class="mt-3">
                <a href="<?= SITEURL ?>files/student.ods" class="btn btn-outline-success">
                    <i class="fa-solid fa-download"></i> Download Registration Template
                </a>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <p class="small text-muted">Rows missing a valid name, gender, date of birth, or status will be skipped automatically.</p>
    </div>
</div>

<script>
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    const fileInput = document.getElementById('student_file');
    const uploadBtn = document.getElementById('uploadBtn');

    if (!fileInput.files[0]) {
        e.preventDefault();
        alert('Please select a file to upload.');
        return;
    }

    const file = fileInput.files[0];
    const maxSize = 5 * 1024 * 1024;

    if (!file.name.match(/\.(xls|xlsx|ods|csv)$/i)) {
        e.preventDefault();
        alert('Please select a valid Excel or CSV file (XLS, XLSX, ODS, CSV).');
        return;
    }

    if (file.size > maxSize) {
        e.preventDefault();
        alert('File size exceeds 5MB limit. Please choose a smaller file.');
        return;
    }

    if (file.size === 0) {
        e.preventDefault();
        alert('The selected file is empty. Please choose a valid file.');
        return;
    }

    uploadBtn.disabled = true;
    uploadBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
});
</script>

<?php include("partials/footer.php"); ?>