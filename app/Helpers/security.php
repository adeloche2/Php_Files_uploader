<?php
/** أدوات أمان وتنسيق مشتركة؛ CSRF stateless لتناسب بيئات serverless. */
declare(strict_types=1);

function e(?string $v): string {
    return htmlspecialchars($v ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** رمز CSRF موقّع دون تخزين جلسة على قرص قد يكون مؤقتًا. */
function csrfToken(): string {
    $key = (string)($GLOBALS['config']['app']['key'] ?? '');
    $payload = time() . '.' . bin2hex(random_bytes(16));
    $signature = hash_hmac('sha256', $payload, $key);
    return $payload . '.' . $signature;
}

function verifyCsrfToken(mixed $value): bool {
    if (!is_string($value)) {
        return false;
    }
    $parts = explode('.', $value);
    if (count($parts) !== 3 || !ctype_digit($parts[0]) || !preg_match('/^[a-f0-9]{32}$/', $parts[1])) {
        return false;
    }
    $issuedAt = (int)$parts[0];
    if ($issuedAt > time() + 60 || $issuedAt < time() - 7200) {
        return false;
    }
    $key = (string)($GLOBALS['config']['app']['key'] ?? '');
    if ($key === '') {
        return false;
    }
    $payload = $parts[0] . '.' . $parts[1];
    return hash_equals(hash_hmac('sha256', $payload, $key), $parts[2]);
}

function positiveImageId(mixed $value): ?int {
    $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $id === false ? null : $id;
}

function formatBytes(int $bytes): string {
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MiB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 1) . ' KiB';
    return $bytes . ' B';
}
