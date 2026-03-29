<?php
// ── Auth ──────────────────────────────────────────────────────────────────────
function auth_guard(): void {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

// ── CSRF ──────────────────────────────────────────────────────────────────────
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_input(): void {
    echo '<input type="hidden" name="csrf" value="' . htmlspecialchars(csrf_token()) . '">';
}

function csrf_verify(): bool {
    return !empty($_POST['csrf'])
        && hash_equals(csrf_token(), $_POST['csrf']);
}

// ── Flash messages ────────────────────────────────────────────────────────────
function flash(string $type, string $msg): void {
    $_SESSION['flash'] = compact('type', 'msg');
}

function get_flash(): ?array {
    if (!isset($_SESSION['flash'])) return null;
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $f;
}

// ── File upload ───────────────────────────────────────────────────────────────
function upload_image(array $file, string $prefix = 'img'): array {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'No file uploaded or upload error.'];
    }
    if ($file['size'] > MAX_IMG_SIZE) {
        return ['ok' => false, 'error' => 'Image must be under 4 MB.'];
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    if (!in_array($mime, ALLOWED_IMG, true)) {
        return ['ok' => false, 'error' => 'Only JPG, PNG, and WebP images allowed.'];
    }
    $ext  = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$mime];
    $name = $prefix . '_' . uniqid('', true) . '.' . $ext;
    $dest = UPLOAD_DIR . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return ['ok' => false, 'error' => 'Could not save file. Check assets/images/ permissions.'];
    }
    return ['ok' => true, 'filename' => $name];
}

function delete_image(string $filename): void {
    if (!$filename) return;
    $path = UPLOAD_DIR . $filename;
    if (file_exists($path)) @unlink($path);
}

// ── Slug ──────────────────────────────────────────────────────────────────────
function make_slug(string $text): string {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $text), '-'));
}

function unique_slug(mysqli $conn, string $table, string $text, int $excludeId = 0): string {
    $base = make_slug($text);
    $slug = $base;
    $n    = 1;
    while (true) {
        $st = $conn->prepare("SELECT id FROM `$table` WHERE slug=? AND id!=?");
        $st->bind_param('si', $slug, $excludeId);
        $st->execute();
        $st->store_result();
        if ($st->num_rows === 0) { $st->close(); break; }
        $st->close();
        $slug = $base . '-' . $n++;
    }
    return $slug;
}

// ── e() shorthand ─────────────────────────────────────────────────────────────
function e(mixed $val): string {
    return htmlspecialchars((string)($val ?? ''), ENT_QUOTES, 'UTF-8');
}
