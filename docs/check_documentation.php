<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$checkDrift = in_array('--drift', $argv, true);
$errors = [];
$warnings = [];
$checked = 0;

function report(array &$bucket, string $file, int $line, string $message): void
{
    $bucket[] = ($file !== '' ? $file . ($line > 0 ? ':' . $line : '') . ': ' : '') . $message;
}

function relativePath(string $root, string $path): string
{
    return str_replace('\\', '/', ltrim(substr($path, strlen($root)), '\\/'));
}

function lineNumber(string $text, int $offset): int
{
    return substr_count(substr($text, 0, $offset), "\n") + 1;
}

function normalizedGeneratedContent(string $content): string
{
    $content = str_replace(["\r\n", "\r"], "\n", $content);
    return preg_replace(
        '/Cập nhật theo mã nguồn ngày \*\*\d{2}\/\d{2}\/\d{4}\*\*/',
        'Cập nhật theo mã nguồn ngày **<DATE>**',
        $content
    ) ?? $content;
}

function copyTree(string $source, string $destination): void
{
    if (is_file($source)) {
        if (!is_dir(dirname($destination))) mkdir(dirname($destination), 0777, true);
        copy($source, $destination);
        return;
    }
    if (!is_dir($source)) return;
    if (!is_dir($destination)) mkdir($destination, 0777, true);
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($iterator as $item) {
        $target = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
        if ($item->isDir()) {
            if (!is_dir($target)) mkdir($target, 0777, true);
        } else {
            if (!is_dir(dirname($target))) mkdir(dirname($target), 0777, true);
            copy($item->getPathname(), $target);
        }
    }
}

function removeTree(string $path): void
{
    if (!is_dir($path)) return;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($iterator as $item) {
        $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }
    rmdir($path);
}

$documents = [
    'MODULE_INDEX.md',
    'docs/PROJECT_FUNCTION_INDEX.md',
    'docs/PROJECT_DEBUGGING_INDEX.md',
    'docs/PROJECT_DATABASE_INDEX.md',
    'docs/PROJECT_MODULE_DETAIL_INDEX.md',
];

foreach ($documents as $relative) {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    if (!is_file($path)) {
        report($errors, $relative, 0, 'Không tìm thấy tài liệu bắt buộc.');
        continue;
    }
    $checked++;
    $content = (string) file_get_contents($path);

    if (substr_count($content, '<details>') !== substr_count($content, '</details>')) {
        report($errors, $relative, 0, 'Số thẻ <details> mở và đóng không bằng nhau.');
    }

    preg_match_all('/<!-- GENERATED_([A-Z0-9_]+)_START -->/', $content, $starts);
    preg_match_all('/<!-- GENERATED_([A-Z0-9_]+)_END -->/', $content, $ends);
    $startNames = $starts[1] ?? [];
    $endNames = $ends[1] ?? [];
    sort($startNames);
    sort($endNames);
    if ($startNames !== $endNames) {
        report($errors, $relative, 0, 'Marker GENERATED_*_START và GENERATED_*_END không khớp.');
    }

    $knownBadPhrases = [
        'nhân sự/nhân sự',
        'thông báo chưa đọc thông báo',
        'đăng nhập Google đăng nhập Google',
    ];
    foreach ($knownBadPhrases as $phrase) {
        $offset = mb_stripos($content, $phrase);
        if ($offset !== false) {
            report($errors, $relative, lineNumber($content, $offset), 'Phát hiện cụm từ lặp: “' . $phrase . '”.');
        }
    }

    preg_match_all('/\[([^\]]+)\]\(([^)]+)\)/u', $content, $links, PREG_OFFSET_CAPTURE);
    foreach ($links[2] ?? [] as [$target, $offset]) {
        if (preg_match('~^(https?://|mailto:|vscode://|#)~i', $target)) continue;
        $target = trim($target, '<>');
        [$filePart, $anchor] = array_pad(explode('#', $target, 2), 2, '');
        if ($filePart === '') continue;
        $resolved = realpath(dirname($path) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, rawurldecode($filePart)));
        if ($resolved === false || (!is_file($resolved) && !is_dir($resolved))) {
            report($errors, $relative, lineNumber($content, $offset), 'Liên kết tới file không tồn tại: ' . $target);
            continue;
        }
        if (is_file($resolved) && preg_match('/^L(\d+)$/', $anchor, $lineMatch)) {
            $maximum = count(file($resolved));
            if ((int) $lineMatch[1] > $maximum) {
                report($errors, $relative, lineNumber($content, $offset), 'Liên kết vượt quá số dòng của file: ' . $target . ' (file có ' . $maximum . ' dòng).');
            }
        }
    }
}

$moduleIndex = (string) @file_get_contents($root . DIRECTORY_SEPARATOR . 'MODULE_INDEX.md');
$requiredHandwrittenSections = [
    '## Tổng quan cho người mới',
    '## Chọn module',
    '## Bản đồ luồng liên module',
    '**Luồng demo nhanh — mua hàng đến nhập kho**',
    '**Luồng demo nhanh — bán hàng đến thu tiền**',
    '**Luồng demo nhanh — kiểm tra giữ chỗ và duyệt lặp**',
    '**Luồng demo nhanh — thu tiền và giảm công nợ**',
];
foreach ($requiredHandwrittenSections as $section) {
    if (!str_contains($moduleIndex, $section)) {
        report($errors, 'MODULE_INDEX.md', 0, 'Thiếu phần viết tay bắt buộc: ' . $section);
    }
}

if ($checkDrift && !$errors) {
    $temp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'project-doc-check-' . bin2hex(random_bytes(6));
    try {
        mkdir($temp, 0777, true);
        copyTree($root . DIRECTORY_SEPARATOR . 'MODULE_INDEX.md', $temp . DIRECTORY_SEPARATOR . 'MODULE_INDEX.md');

        putenv('DOC_OUTPUT_ROOT=' . $temp);
        $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($root . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'generate_project_function_index.php');
        exec($command . ' 2>&1', $output, $exitCode);
        putenv('DOC_OUTPUT_ROOT');
        if ($exitCode !== 0) {
            report($errors, 'docs/generate_project_function_index.php', 0, 'Không thể sinh bản đối chiếu trong thư mục tạm: ' . implode(' | ', $output));
        } else {
            foreach ($documents as $relative) {
                $actual = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
                $expected = $temp . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
                if (!is_file($expected)) continue;
                if (normalizedGeneratedContent((string) file_get_contents($actual)) !== normalizedGeneratedContent((string) file_get_contents($expected))) {
                    report($errors, $relative, 0, 'Nội dung sinh tự động đã lệch mã nguồn. Chạy `composer docs:generate` rồi kiểm tra diff.');
                }
            }
        }
    } finally {
        removeTree($temp);
    }
}

foreach ($warnings as $message) fwrite(STDERR, "[CẢNH BÁO] $message\n");
foreach ($errors as $message) fwrite(STDERR, "[LỖI] $message\n");

if ($errors) {
    fwrite(STDERR, "\nKiểm tra tài liệu thất bại: " . count($errors) . " lỗi. Không có file tài liệu nào bị tự động sửa.\n");
    exit(1);
}

echo 'Kiểm tra tài liệu thành công: ' . $checked . ' file, không phát hiện lỗi';
echo $checkDrift ? ", đã đối chiếu sai lệch với mã nguồn.\n" : ". Dùng --drift để đối chiếu nội dung sinh tự động.\n";
