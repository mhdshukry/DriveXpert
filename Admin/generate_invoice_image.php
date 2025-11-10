<?php
declare(strict_types=1);

include '../config.php';
include 'admin_guard.php';

$historyId = filter_input(INPUT_GET, 'history_id', FILTER_VALIDATE_INT);

if (!$historyId) {
    http_response_code(400);
    echo 'Invalid invoice request.';
    exit;
}

$stmt = $conn->prepare(
    'SELECT h.history_id, h.rental_id, u.name AS customer_name, u.email AS customer_email,
			u.phone AS customer_phone, h.date_from, h.date_to, h.total_fees, h.car_model,
			h.car_brand, h.seat_count, h.max_speed, h.km_per_liter, h.fine_details, h.additional_options
	 FROM rental_history h
	 JOIN users u ON h.customer_id = u.user_id
	 WHERE h.history_id = ?'
);

if (!$stmt) {
    http_response_code(500);
    exit('Unable to prepare invoice query.');
}

$stmt->bind_param('i', $historyId);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$data) {
    http_response_code(404);
    exit('Invoice not found.');
}

$fontCandidates = [
    __DIR__ . '/Arial.ttf',
    __DIR__ . '/arial.ttf',
    __DIR__ . '/../Assets/Fonts/Manrope-Regular.ttf',
    __DIR__ . '/../Assets/Fonts/OpenSans-Regular.ttf',
];

$fontPath = null;
foreach ($fontCandidates as $candidate) {
    if (is_string($candidate) && file_exists($candidate)) {
        $fontPath = $candidate;
        break;
    }
}

if ($fontPath === null) {
    $fontPath = __DIR__ . '/Arial.ttf';
}

$fromTimestamp = !empty($data['date_from']) ? strtotime((string) $data['date_from']) : false;
$toTimestamp = !empty($data['date_to']) ? strtotime((string) $data['date_to']) : false;

if ($fromTimestamp && $toTimestamp && $toTimestamp >= $fromTimestamp) {
    $daysRented = max(1, (int) floor(($toTimestamp - $fromTimestamp) / 86400) + 1);
} else {
    $daysRented = 1;
}

$totalFees = isset($data['total_fees']) ? (float) $data['total_fees'] : 0.0;
$perDayCost = $daysRented > 0 ? round($totalFees / $daysRented, 2) : round($totalFees, 2);

$fineEntries = parseFineEntries($data['fine_details'] ?? null);
$additionalOptions = normalizeOptionList($data['additional_options'] ?? null);

$totalFine = 0.0;
foreach ($fineEntries as $entry) {
    $totalFine += $entry['amount'] ?? 0.0;
}
$totalFine = round($totalFine, 2);
$totalCost = round($totalFees + $totalFine, 2);

$currencySymbol = '₱';
$invoiceNumber = 'DX-' . str_pad((string) $historyId, 6, '0', STR_PAD_LEFT);
$issuedDate = date('M d, Y');
$rentalWindow = formatDateRange($data['date_from'] ?? null, $data['date_to'] ?? null);
$durationLabel = sprintf('%d day%s', $daysRented, $daysRented === 1 ? '' : 's');
$extrasText = buildBulletList($additionalOptions, 'No optional add-ons recorded.');
$fineSummary = buildFineSummaryText($fineEntries, $currencySymbol);
$paymentStatus = $totalCost <= 0 ? 'Settled in full' : 'Paid';

$width = 2480; // Approximate A4 width at 300 DPI
$height = 3508; // Approximate A4 height at 300 DPI
$image = imagecreatetruecolor($width, $height);
imageantialias($image, true);

$pageMarginX = 160;
$pageMarginY = 140;
$columnGutter = 80;
$headerHeight = 460;
$contentStartY = $headerHeight + 80;

$background = imagecolorallocate($image, 248, 250, 252);
imagefilledrectangle($image, 0, 0, $width, $height, $background);

$colors = [
    'primaryText' => imagecolorallocate($image, 255, 255, 255),
    'title' => imagecolorallocate($image, 15, 23, 42),
    'muted' => imagecolorallocate($image, 100, 116, 139),
    'value' => imagecolorallocate($image, 30, 41, 59),
    'accent' => imagecolorallocate($image, 219, 17, 17),
    'cardBg' => imagecolorallocate($image, 255, 255, 255),
    'cardBorder' => imagecolorallocate($image, 226, 232, 240),
    'line' => imagecolorallocate($image, 203, 213, 225),
    'footerBg' => imagecolorallocate($image, 15, 23, 42),
    'footerText' => imagecolorallocate($image, 148, 163, 184),
];

drawVerticalGradient($image, 0, 0, $width, $headerHeight, [17, 24, 39], [88, 28, 135]);
$accentBand = imagecolorallocate($image, 30, 64, 175);
imagefilledrectangle($image, 0, $headerHeight - 100, $width, $headerHeight, $accentBand);

$logo = loadLogoImage(__DIR__ . '/../Assets/Images/DriveXpert.png');
if ($logo) {
    $logoWidth = max(1, imagesx($logo));
    $logoHeight = max(1, imagesy($logo));
    $targetWidth = 320;
    $targetHeight = (int) round(($logoHeight / $logoWidth) * $targetWidth);
    imagecopyresampled($image, $logo, $pageMarginX, $pageMarginY - 20, 0, 0, $targetWidth, $targetHeight, $logoWidth, $logoHeight);
    imagedestroy($logo);
} else {
    imagettftext($image, 48, 0, $pageMarginX, $pageMarginY + 40, $colors['primaryText'], $fontPath, 'DriveXpert Rentals');
}

imagettftext($image, 20, 0, $pageMarginX, $pageMarginY + 120, $colors['primaryText'], $fontPath, 'DriveXpert Rentals | drivexpert.com | +63 123 456 7890');
imagettftext($image, 20, 0, $pageMarginX, $pageMarginY + 160, $colors['primaryText'], $fontPath, '123 Skylane Drive, Manila, Philippines');

drawHeaderMeta($image, $width - $pageMarginX - 360, $pageMarginY + 10, [
    'Invoice' => $invoiceNumber,
    'Issued' => $issuedDate,
    'Rental Ref' => '#' . (int) $data['rental_id'],
    'Status' => strtoupper($paymentStatus),
], $fontPath, $colors);

$contentWidth = $width - ($pageMarginX * 2);
$columnWidth = (int) floor(($contentWidth - $columnGutter) / 2);
$leftColumnX = $pageMarginX;
$rightColumnX = $pageMarginX + $columnWidth + $columnGutter;
$leftColumnY = $contentStartY;
$rightColumnY = $contentStartY;

$customerRows = [
    ['label' => 'Customer Name', 'value' => $data['customer_name'] ?? 'N/A'],
    ['label' => 'Email Address', 'value' => $data['customer_email'] ?? 'N/A'],
    ['label' => 'Contact Number', 'value' => formatPhoneNumber($data['customer_phone'] ?? null)],
];
$leftColumnY = drawCard($image, $leftColumnX, $leftColumnY, $columnWidth, 'Customer Profile', $customerRows, $fontPath, $colors);

$reservationRows = [
    ['label' => 'Journey Window', 'value' => $rentalWindow],
    ['label' => 'Duration', 'value' => $durationLabel],
    ['label' => 'Issued On', 'value' => $issuedDate],
    ['label' => 'Rental Reference', 'value' => '#' . (int) $data['history_id']],
];
$leftColumnY = drawCard($image, $leftColumnX, $leftColumnY, $columnWidth, 'Reservation Overview', $reservationRows, $fontPath, $colors);

$vehicleLabel = trim(((string) ($data['car_brand'] ?? '')) . ' ' . ((string) ($data['car_model'] ?? '')));
$vehicleRows = [
    ['label' => 'Vehicle', 'value' => $vehicleLabel !== '' ? $vehicleLabel : 'DriveXpert Fleet Vehicle'],
    ['label' => 'Seating Capacity', 'value' => isset($data['seat_count']) ? $data['seat_count'] . ' passengers' : 'Not specified'],
    ['label' => 'Max Speed', 'value' => isset($data['max_speed']) ? $data['max_speed'] . ' km/h' : 'Not specified'],
    ['label' => 'Fuel Efficiency', 'value' => isset($data['km_per_liter']) ? $data['km_per_liter'] . ' km/l' : 'Not specified'],
];
$rightColumnY = drawCard($image, $rightColumnX, $rightColumnY, $columnWidth, 'Vehicle Details', $vehicleRows, $fontPath, $colors);

$chargesRows = [
    ['label' => 'Daily Rate', 'value' => formatCurrency($perDayCost, $currencySymbol) . ' / day'],
    ['label' => 'Recorded Rental Fees', 'value' => formatCurrency($totalFees, $currencySymbol)],
    ['label' => 'Fine Adjustments', 'value' => formatCurrency($totalFine, $currencySymbol)],
    ['label' => 'Grand Total', 'value' => formatCurrency($totalCost, $currencySymbol), 'style' => 'highlight'],
];
$rightColumnY = drawCard($image, $rightColumnX, $rightColumnY, $columnWidth, 'Charges & Adjustments', $chargesRows, $fontPath, $colors);

$notesRows = [
    ['label' => 'Add-on Packages', 'value' => $extrasText],
    ['label' => 'Fine Details', 'value' => $fineSummary],
];
$leftColumnY = drawCard($image, $leftColumnX, $leftColumnY, $columnWidth, 'Extras & Notes', $notesRows, $fontPath, $colors);

$bodyBottom = max($leftColumnY, $rightColumnY);

$currentY = drawTotalsBanner($image, $pageMarginX, $bodyBottom, $contentWidth, [
    'Base Rental' => formatCurrency($totalFees, $currencySymbol),
    'Fines' => formatCurrency($totalFine, $currencySymbol),
    'Total Due' => formatCurrency($totalCost, $currencySymbol),
], $fontPath, $colors);

$currentY = drawSignatureStrip($image, $pageMarginX, $currentY, $contentWidth, $fontPath, $colors);

$footerY = max($currentY + 60, $height - 260);
if ($footerY + 180 > $height) {
    $footerY = $height - 180;
}

drawFooter($image, $footerY, $width, $colors, $fontPath, $pageMarginX);

header('Content-Type: image/jpeg');
header('Content-Disposition: attachment; filename=rental_invoice_' . (int) $data['rental_id'] . '.jpg');
imagejpeg($image, null, 95);
imagedestroy($image);
exit;

function parseFineEntries($fineDetails): array
{
    if ($fineDetails === null || $fineDetails === '') {
        return [];
    }

    if (is_string($fineDetails)) {
        $decoded = json_decode($fineDetails, true);
        if (json_last_error() === JSON_ERROR_NONE && $decoded !== null) {
            $fineDetails = $decoded;
        }
    }

    $entries = [];

    if (is_array($fineDetails)) {
        if (isAssociativeArray($fineDetails) && isset($fineDetails['amount'])) {
            $fineDetails = [$fineDetails];
        } elseif (isAssociativeArray($fineDetails)) {
            $normalized = [];
            foreach ($fineDetails as $key => $value) {
                if (is_array($value) && isset($value['amount'])) {
                    $normalized[] = [
                        'reason' => $value['reason'] ?? (is_string($key) ? (string) $key : 'Fine'),
                        'amount' => $value['amount'],
                    ];
                } else {
                    $normalized[] = [
                        'reason' => is_string($key) ? (string) $key : 'Fine',
                        'amount' => $value,
                    ];
                }
            }
            $fineDetails = $normalized;
        }

        foreach ($fineDetails as $item) {
            if (is_array($item)) {
                $reason = $item['reason'] ?? $item['title'] ?? null;
                $amountSource = $item['amount'] ?? $item['value'] ?? null;
                if ($amountSource === null && count($item) === 1) {
                    $amountSource = reset($item);
                    $key = key($item);
                    if ($reason === null && is_string($key)) {
                        $reason = (string) $key;
                    }
                }
            } else {
                $reason = null;
                $amountSource = $item;
            }

            $amount = extractNumericAmount($amountSource);
            if ($amount === null) {
                continue;
            }

            $entries[] = [
                'reason' => $reason ? (string) $reason : 'Fine',
                'amount' => $amount,
            ];
        }
    } else {
        $amount = extractNumericAmount($fineDetails);
        if ($amount !== null) {
            $entries[] = [
                'reason' => 'Fine',
                'amount' => $amount,
            ];
        }
    }

    return $entries;
}

function extractNumericAmount($value): ?float
{
    if ($value === null) {
        return null;
    }

    if (is_numeric($value)) {
        return round((float) $value, 2);
    }

    if (is_string($value) && preg_match('/-?\d+(?:\.\d+)?/', $value, $matches)) {
        return round((float) $matches[0], 2);
    }

    return null;
}

function isAssociativeArray(array $array): bool
{
    return array_keys($array) !== range(0, count($array) - 1);
}

function formatCurrency(float $amount, string $symbol = '₱'): string
{
    return $symbol . number_format($amount, 2);
}

function formatDisplayDate(?string $date): string
{
    if (!$date) {
        return 'N/A';
    }

    $dateTime = DateTime::createFromFormat('Y-m-d', $date);
    if (!$dateTime) {
        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $date);
    }

    if ($dateTime instanceof DateTime) {
        return $dateTime->format('M d, Y');
    }

    $timestamp = strtotime($date);
    return $timestamp ? date('M d, Y', $timestamp) : 'N/A';
}

function formatDateRange(?string $from, ?string $to): string
{
    $fromLabel = formatDisplayDate($from);
    $toLabel = formatDisplayDate($to);

    if ($fromLabel === 'N/A' && $toLabel === 'N/A') {
        return 'Date not recorded';
    }

    return $fromLabel . ' -> ' . $toLabel;
}

function formatPhoneNumber(?string $phone): string
{
    if ($phone === null) {
        return 'N/A';
    }

    $clean = preg_replace('/[^0-9+]/', ' ', $phone);
    $clean = trim(preg_replace('/\s+/', ' ', (string) $clean));

    return $clean !== '' ? $clean : 'N/A';
}

function normalizeOptionList($raw): array
{
    if ($raw === null || $raw === '') {
        return [];
    }

    if (is_string($raw)) {
        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && $decoded !== null) {
            $raw = $decoded;
        } else {
            $parts = preg_split('/(?:,|;|\n)+/', $raw);
            return array_values(array_filter(array_map('trim', $parts)));
        }
    }

    if (is_array($raw)) {
        $options = [];
        foreach ($raw as $item) {
            if (is_array($item)) {
                $label = $item['name'] ?? $item['option_name'] ?? $item['label'] ?? null;
                if ($label !== null) {
                    $options[] = (string) $label;
                }
            } else {
                $options[] = (string) $item;
            }
        }
        return array_values(array_filter(array_map('trim', $options)));
    }

    return [];
}

function buildBulletList(array $items, string $emptyMessage): string
{
    if (empty($items)) {
        return $emptyMessage;
    }

    return '- ' . implode(PHP_EOL . '- ', $items);
}

function buildFineSummaryText(array $fineEntries, string $currencySymbol): string
{
    if (empty($fineEntries)) {
        return 'No fines recorded.';
    }

    $lines = [];
    foreach ($fineEntries as $fine) {
        $reason = $fine['reason'] ?? 'Fine';
        $amount = $fine['amount'] ?? 0.0;
        $lines[] = sprintf('- %s : %s', $reason, formatCurrency((float) $amount, $currencySymbol));
    }

    return implode(PHP_EOL, $lines);
}

function drawVerticalGradient($image, int $x1, int $y1, int $x2, int $y2, array $startColor, array $endColor): void
{
    $height = max(1, $y2 - $y1);
    for ($i = 0; $i < $height; $i++) {
        $ratio = $i / $height;
        $red = (int) round($startColor[0] + ($endColor[0] - $startColor[0]) * $ratio);
        $green = (int) round($startColor[1] + ($endColor[1] - $startColor[1]) * $ratio);
        $blue = (int) round($startColor[2] + ($endColor[2] - $startColor[2]) * $ratio);
        $color = imagecolorallocate($image, $red, $green, $blue);
        imageline($image, $x1, $y1 + $i, $x2, $y1 + $i, $color);
    }
}

function drawHeaderMeta($image, int $x, int $y, array $items, string $fontPath, array $colors): void
{
    $currentY = $y;
    foreach ($items as $label => $value) {
        imagettftext($image, 12, 0, $x, $currentY, $colors['primaryText'], $fontPath, strtoupper((string) $label));
        $currentY += 26;
        imagettftext($image, 22, 0, $x, $currentY, $colors['primaryText'], $fontPath, (string) $value);
        $currentY += 42;
    }
}

function drawCard($image, int $x, int $y, int $width, string $title, array $rows, string $fontPath, array $colors): int
{
    $paddingX = 36;
    $paddingY = 34;
    $headerHeight = 40;
    $labelSize = 12;
    $valueSize = 20;

    $contentHeight = 0;
    foreach ($rows as $row) {
        $value = (string) ($row['value'] ?? 'N/A');
        $contentHeight += estimateRowHeight($value, $labelSize, $valueSize);
    }

    $cardHeight = (int) ($paddingY * 2 + $headerHeight + $contentHeight);

    imagefilledrectangle($image, $x, $y, $x + $width, $y + $cardHeight, $colors['cardBg']);
    imagerectangle($image, $x, $y, $x + $width, $y + $cardHeight, $colors['cardBorder']);
    imagefilledrectangle($image, $x, $y, $x + 8, $y + $cardHeight, $colors['accent']);

    imagettftext($image, 20, 0, $x + $paddingX, $y + $paddingY + 4, $colors['title'], $fontPath, $title);

    $currentY = $y + $paddingY + $headerHeight;
    foreach ($rows as $row) {
        $value = (string) ($row['value'] ?? 'N/A');
        $style = $row['style'] ?? '';
        $valueColor = $style === 'highlight' ? $colors['accent'] : $colors['value'];
        $currentY = drawKeyValueRow($image, $x + $paddingX, $currentY, (string) $row['label'], $value, $fontPath, $colors['muted'], $valueColor, $labelSize, $valueSize);
        $currentY += 10;
    }

    return $y + $cardHeight + 36;
}

function estimateRowHeight(string $value, int $labelSize, int $valueSize): int
{
    $lines = preg_split('/\r?\n/', trim($value));
    if ($lines === false || $lines === []) {
        $lines = ['N/A'];
    }
    $lineCount = max(1, count($lines));
    return $labelSize + 14 + $lineCount * ($valueSize + 10) + 10;
}

function drawKeyValueRow($image, int $x, int $y, string $label, string $value, string $fontPath, int $labelColor, int $valueColor, int $labelSize = 12, int $valueSize = 18): int
{
    imagettftext($image, $labelSize, 0, $x, $y, $labelColor, $fontPath, strtoupper($label));

    $lines = preg_split('/\r?\n/', trim($value));
    if ($lines === false || $lines === []) {
        $lines = ['N/A'];
    }
    if (count($lines) === 1 && $lines[0] === '') {
        $lines = ['N/A'];
    }

    $textY = $y + $labelSize + 12;
    foreach ($lines as $line) {
        imagettftext($image, $valueSize, 0, $x, $textY, $valueColor, $fontPath, $line);
        $textY += $valueSize + 10;
    }

    return $textY;
}

function textWidth(string $text, int $size, string $fontPath): int
{
    $box = imagettfbbox($size, 0, $fontPath, $text);
    return (int) (($box[2] ?? 0) - ($box[0] ?? 0));
}

function drawTotalsBanner($image, int $x, int $y, int $width, array $totals, string $fontPath, array $colors): int
{
    $bannerHeight = 200;
    imagefilledrectangle($image, $x, $y, $x + $width, $y + $bannerHeight, $colors['accent']);

    imagettftext($image, 22, 0, $x + 40, $y + 60, $colors['primaryText'], $fontPath, 'Payment Summary');

    $columns = max(1, count($totals));
    $columnWidth = (int) floor(($width - 80) / $columns);

    $index = 0;
    foreach ($totals as $label => $value) {
        $colStart = $x + 40 + $index * $columnWidth;
        $colEnd = $colStart + $columnWidth;
        $centerX = (int) (($colStart + $colEnd) / 2);

        $labelText = strtoupper((string) $label);
        $valueText = (string) $value;

        $labelWidth = textWidth($labelText, 12, $fontPath);
        $valueWidth = textWidth($valueText, 28, $fontPath);

        imagettftext($image, 12, 0, $centerX - (int) ($labelWidth / 2), $y + 110, $colors['primaryText'], $fontPath, $labelText);
        imagettftext($image, 28, 0, $centerX - (int) ($valueWidth / 2), $y + 155, $colors['primaryText'], $fontPath, $valueText);

        $index++;
    }

    return $y + $bannerHeight + 48;
}

function drawSignatureStrip($image, int $x, int $y, int $width, string $fontPath, array $colors): int
{
    $lineLength = 260;
    $labels = ['Director', 'Manager', 'Customer'];
    $count = count($labels);
    $gap = $count > 1 ? ($width - ($lineLength * $count)) / ($count - 1) : 0.0;

    $baseline = $y + 70;
    $currentX = $x;

    foreach ($labels as $label) {
        $startX = (int) round($currentX);
        $endX = $startX + $lineLength;
        imageline($image, $startX, $baseline, $endX, $baseline, $colors['line']);
        imagettftext($image, 12, 0, $startX, $baseline + 26, $colors['muted'], $fontPath, $label . ' Signature');
        $currentX += $lineLength + $gap;
    }

    return $baseline + 70;
}

function drawFooter($image, int $startY, int $width, array $colors, string $fontPath, int $marginX = 160): void
{
    $footerHeight = 160;
    imagefilledrectangle($image, 0, $startY, $width, $startY + $footerHeight, $colors['footerBg']);

    imagettftext($image, 18, 0, $marginX, $startY + 60, $colors['primaryText'], $fontPath, 'Thank you for choosing DriveXpert.');
    imagettftext($image, 12, 0, $marginX, $startY + 95, $colors['footerText'], $fontPath, 'DriveXpert Rentals | drivexpert.com | +63 123 456 7890');
    imagettftext($image, 12, 0, $marginX, $startY + 125, $colors['footerText'], $fontPath, 'This invoice confirms your rental journey has been completed.');
}

function loadLogoImage(string $path)
{
    if (!file_exists($path)) {
        return null;
    }

    $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
    switch ($extension) {
        case 'png':
            return @imagecreatefrompng($path) ?: null;
        case 'jpg':
        case 'jpeg':
            return @imagecreatefromjpeg($path) ?: null;
        default:
            return null;
    }
}

