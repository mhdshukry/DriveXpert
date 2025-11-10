<?php
declare(strict_types=1);

/**
 * Utility helpers for building consistent rental detail payloads for admin/UI modals.
 */

/**
 * Build a detail payload for a single rental row that can be safely JSON encoded for UI consumption.
 *
 * @param array $row                       Base rental data including customer/car columns.
 * @param array<int, array> $extrasByRental Grouped extras keyed by rental id.
 * @param array<int, array> $insuranceByRental Grouped insurance keyed by rental id.
 * @param array<int, array> $finesByRental Grouped fines keyed by rental id.
 * @param array<string, string> $currencyMeta Currency metadata (symbol/code) for formatting.
 *
 * @return array<string, mixed>
 */
function buildRentalDetailPayload(
    array $row,
    array $extrasByRental,
    array $insuranceByRental,
    array $finesByRental,
    array $currencyMeta
): array {
    $rentalId = (int) ($row['rental_id'] ?? 0);
    $dateFrom = $row['date_from'] ?? null;
    $dateTo = $row['date_to'] ?? null;

    $durationDays = 1;
    if ($dateFrom && $dateTo) {
        $fromDate = DateTime::createFromFormat('Y-m-d', (string) $dateFrom) ?: null;
        $toDate = DateTime::createFromFormat('Y-m-d', (string) $dateTo) ?: null;
        if ($fromDate instanceof DateTime && $toDate instanceof DateTime) {
            $diff = $fromDate->diff($toDate);
            $durationDays = max(1, (int) $diff->days);
        }
    }

    $rentPerDay = isset($row['rent_per_day']) ? (float) $row['rent_per_day'] : null;
    $baseCost = $rentPerDay !== null ? round($rentPerDay * $durationDays, 2) : null;

    $extrasList = [];
    $extrasTotal = 0.0;
    foreach ($extrasByRental[$rentalId] ?? [] as $extra) {
        $dailyCost = isset($extra['daily_cost']) ? (float) $extra['daily_cost'] : 0.0;
        $totalCost = round($dailyCost * $durationDays, 2);
        $extrasList[] = [
            'name' => (string) ($extra['name'] ?? 'Add-on'),
            'dailyCost' => $dailyCost,
            'totalCost' => $totalCost,
        ];
        $extrasTotal += $totalCost;
    }
    $extrasTotal = round($extrasTotal, 2);

    $insuranceDetail = null;
    $insuranceTotal = 0.0;
    if (isset($insuranceByRental[$rentalId])) {
        $insurance = $insuranceByRental[$rentalId];
        $insuranceTotal = isset($insurance['cost']) ? (float) $insurance['cost'] : 0.0;
        $insuranceDetail = [
            'name' => (string) ($insurance['name'] ?? 'Insurance'),
            'cost' => round($insuranceTotal, 2),
        ];
    }

    $fineList = [];
    $fineTotal = 0.0;
    foreach ($finesByRental[$rentalId] ?? [] as $fine) {
        $amount = isset($fine['amount']) ? (float) $fine['amount'] : 0.0;
        $fineList[] = [
            'reason' => (string) ($fine['reason'] ?? 'Fine'),
            'amount' => round($amount, 2),
        ];
        $fineTotal += $amount;
    }
    $fineTotal = round($fineTotal, 2);

    $recordedTotal = 0.0;
    if (isset($row['total_cost'])) {
        $recordedTotal = (float) $row['total_cost'];
    } elseif (isset($row['total_fees'])) {
        $recordedTotal = (float) $row['total_fees'];
    } elseif (isset($row['rental_total'])) {
        $recordedTotal = (float) $row['rental_total'];
    }
    $recordedTotal = round($recordedTotal, 2);

    $computedGrandTotal = 0.0;
    if ($baseCost !== null) {
        $computedGrandTotal += $baseCost;
    }
    $computedGrandTotal += $extrasTotal + $insuranceTotal + $fineTotal;
    $computedGrandTotal = round($computedGrandTotal, 2);

    $statusRaw = strtolower((string) ($row['status'] ?? $row['rental_status'] ?? 'pending'));
    $statusMeta = resolveRentalStatusMeta($statusRaw);

    $customerName = $row['customer_name'] ?? $row['customer'] ?? $row['name'] ?? '';

    return [
        'rentalId' => $rentalId,
        'status' => $statusMeta['key'],
        'statusLabel' => $statusMeta['label'],
        'statusClass' => $statusMeta['class'],
        'requestedAt' => $row['created_at'] ?? null,
        'currency' => $currencyMeta,
        'customer' => [
            'name' => (string) $customerName,
            'email' => $row['customer_email'] ?? $row['email'] ?? null,
            'phone' => $row['customer_phone'] ?? $row['phone'] ?? null,
            'nic' => $row['nic_number'] ?? null,
        ],
        'car' => [
            'brand' => $row['car_brand'] ?? $row['brand'] ?? null,
            'model' => $row['car_model'] ?? $row['model'] ?? null,
            'label' => buildCarLabel($row),
            'seatCount' => isset($row['seat_count']) ? (int) $row['seat_count'] : null,
            'maxSpeed' => isset($row['max_speed']) ? (int) $row['max_speed'] : null,
            'efficiency' => isset($row['km_per_liter']) ? (float) $row['km_per_liter'] : null,
            'rentPerDay' => $rentPerDay,
        ],
        'schedule' => [
            'from' => $dateFrom,
            'to' => $dateTo,
            'durationDays' => $durationDays,
        ],
        'pricing' => [
            'baseRate' => $rentPerDay,
            'baseCost' => $baseCost,
            'extras' => $extrasList,
            'extrasTotal' => $extrasTotal,
            'insurance' => $insuranceDetail,
            'insuranceTotal' => $insuranceTotal,
            'fines' => $fineList,
            'finesTotal' => $fineTotal,
            'recordedTotal' => $recordedTotal,
            'grandTotal' => $computedGrandTotal > 0 ? $computedGrandTotal : $recordedTotal,
        ],
    ];
}

/**
 * Collect extras, insurance, and fines for a batch of rental identifiers.
 *
 * @param mysqli $conn
 * @param list<int> $rentalIds
 * @return array{extras: array<int, array>, insurance: array<int, array>, fines: array<int, array>}
 */
function collectSupplementaryRentalData(mysqli $conn, array $rentalIds): array
{
    $rentalIds = array_values(array_filter(array_map(static fn($id) => (int) $id, $rentalIds)));
    if (empty($rentalIds)) {
        return [
            'extras' => [],
            'insurance' => [],
            'fines' => [],
        ];
    }

    $placeholders = implode(',', array_fill(0, count($rentalIds), '?'));
    $types = str_repeat('i', count($rentalIds));

    $extrasByRental = [];
    $extrasSql = "SELECT ro.rental_id, ao.option_name, ao.daily_cost
                  FROM rental_options ro
                  JOIN additional_options ao ON ro.option_id = ao.option_id
                  WHERE ro.rental_id IN ($placeholders)";
    if ($stmt = $conn->prepare($extrasSql)) {
        bindIntegerParams($stmt, $types, $rentalIds);
        if ($stmt->execute() && ($result = $stmt->get_result())) {
            while ($row = $result->fetch_assoc()) {
                $rid = (int) ($row['rental_id'] ?? 0);
                if ($rid === 0) {
                    continue;
                }
                $extrasByRental[$rid][] = [
                    'name' => $row['option_name'] ?? 'Add-on',
                    'daily_cost' => isset($row['daily_cost']) ? (float) $row['daily_cost'] : 0.0,
                ];
            }
            $result->free();
        }
        $stmt->close();
    }

    $insuranceByRental = [];
    $insuranceSql = "SELECT ri.rental_id, io.plan_name, ri.insurance_cost
                     FROM rental_insurance ri
                     JOIN insurance_options io ON ri.insurance_id = io.insurance_id
                     WHERE ri.rental_id IN ($placeholders)";
    if ($stmt = $conn->prepare($insuranceSql)) {
        bindIntegerParams($stmt, $types, $rentalIds);
        if ($stmt->execute() && ($result = $stmt->get_result())) {
            while ($row = $result->fetch_assoc()) {
                $rid = (int) ($row['rental_id'] ?? 0);
                if ($rid === 0) {
                    continue;
                }
                $insuranceByRental[$rid] = [
                    'name' => $row['plan_name'] ?? 'Insurance',
                    'cost' => isset($row['insurance_cost']) ? (float) $row['insurance_cost'] : 0.0,
                ];
            }
            $result->free();
        }
        $stmt->close();
    }

    $finesByRental = [];
    $fineSql = "SELECT rental_id, reason, amount FROM fines WHERE rental_id IN ($placeholders)";
    if ($stmt = $conn->prepare($fineSql)) {
        bindIntegerParams($stmt, $types, $rentalIds);
        if ($stmt->execute() && ($result = $stmt->get_result())) {
            while ($row = $result->fetch_assoc()) {
                $rid = (int) ($row['rental_id'] ?? 0);
                if ($rid === 0) {
                    continue;
                }
                $finesByRental[$rid][] = [
                    'reason' => $row['reason'] ?? 'Fine',
                    'amount' => isset($row['amount']) ? (float) $row['amount'] : 0.0,
                ];
            }
            $result->free();
        }
        $stmt->close();
    }

    return [
        'extras' => $extrasByRental,
        'insurance' => $insuranceByRental,
        'fines' => $finesByRental,
    ];
}

/**
 * Resolve status label/class metadata for a given rental status key.
 *
 * @param string $rawStatus
 * @return array{key:string,label:string,class:string}
 */
function resolveRentalStatusMeta(string $rawStatus): array
{
    $statusKey = preg_replace('/[^a-z_]/', '', strtolower($rawStatus)) ?: 'pending';
    $map = [
        'pending' => ['label' => 'Pending Confirmation', 'class' => 'status-pill status-pending'],
        'confirmed' => ['label' => 'Confirmed', 'class' => 'status-pill status-confirmed'],
        'completed' => ['label' => 'Completed', 'class' => 'status-pill status-completed'],
        'cancelled' => ['label' => 'Cancelled', 'class' => 'status-pill status-cancelled'],
        'canceled' => ['label' => 'Cancelled', 'class' => 'status-pill status-cancelled'],
        'rejected' => ['label' => 'Rejected', 'class' => 'status-pill status-declined'],
    ];

    if (isset($map[$statusKey])) {
        return ['key' => $statusKey, 'label' => $map[$statusKey]['label'], 'class' => $map[$statusKey]['class']];
    }

    return ['key' => $statusKey, 'label' => ucwords(str_replace('_', ' ', $statusKey)), 'class' => 'status-pill'];
}

/**
 * Helper to bind an array of integer ids to a prepared statement.
 */
function bindIntegerParams(mysqli_stmt $stmt, string $types, array $values): void
{
    $bindParams = [$types];
    foreach ($values as $index => $value) {
        $values[$index] = (int) $value;
        $bindParams[] = &$values[$index];
    }
    call_user_func_array([$stmt, 'bind_param'], $bindParams);
}

/**
 * Build a user-facing car label from a rental row.
 */
function buildCarLabel(array $row): string
{
    $brand = trim((string) ($row['car_brand'] ?? $row['brand'] ?? ''));
    $model = trim((string) ($row['car_model'] ?? $row['model'] ?? ''));
    $label = trim($brand . ' ' . $model);
    if ($label === '') {
        return 'DriveXpert Vehicle';
    }
    return $label;
}