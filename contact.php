<?php
// contact.php
header('Content-Type: application/json; charset=utf-8');

// --- Helper: read input (supports JSON body or form-encoded) ---
$input = null;
$raw = file_get_contents('php://input');
if ($raw) {
    $data = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $input = $data;
    }
}
if ($input === null) {
    // fallback to $_POST (form-encoded)
    $input = $_POST;
}

// --- Basic server-side validation & sanitization functions ---
function sanitize_text($str) {
    $s = trim($str);
    // remove any null bytes
    $s = str_replace("\0", '', $s);
    // prevent header injection
    $s = preg_replace("/[\r\n].*/", "", $s);
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function sanitize_message($str) {
    $s = trim($str);
    $s = str_replace("\0", '', $s);
    // fairly permissive but remove dangerous header-like lines
    $s = preg_replace("/(Content-Type:|Bcc:|Cc:|To:)/i", "", $s);
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$firstName = isset($input['firstName']) ? sanitize_text($input['firstName']) : '';
$lastName  = isset($input['lastName'])  ? sanitize_text($input['lastName'])  : '';
$email     = isset($input['email'])     ? filter_var(trim($input['email']), FILTER_SANITIZE_EMAIL) : '';
$phone     = isset($input['phone'])     ? sanitize_text($input['phone']) : '';
$message   = isset($input['message'])   ? sanitize_message($input['message']) : '';

// --- Validate required fields ---
$errors = [];
if ($firstName === '') { $errors[] = 'First name is required.'; }
if ($lastName === '')  { $errors[] = 'Last name is required.'; }
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Valid email is required.'; }
if ($message === '')   { $errors[] = 'Message is required.'; }

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// --- Build email ---
$to = 'ashwnshamba@gmail.com'; // destination email
$subject = "Website Contact: {$firstName} {$lastName}";
$body = "You have a new message from your website contact form.\n\n";
$body .= "Name: {$firstName} {$lastName}\n";
$body .= "Email: {$email}\n";
if ($phone !== '') {
    $body .= "Phone: {$phone}\n";
}
$body .= "\nMessage:\n{$message}\n";

// Prevent header injection by removing problematic characters from mail headers
function safe_header_value($str) {
    return trim(str_replace(["\r", "\n"], '', $str));
}

$fromName = safe_header_value($firstName . ' ' . $lastName);
$replyTo = safe_header_value($email);

// Headers
$headers = [];
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-Type: text/plain; charset=utf-8';
$headers[] = 'From: ' . $fromName . " <no-reply@" . $_SERVER['SERVER_NAME'] . '>';
$headers[] = 'Reply-To: ' . $replyTo;
$headers[] = 'X-Mailer: PHP/' . phpversion();

// --- Send email using mail() ---
$ok = false;
try {
    $ok = @mail($to, $subject, $body, implode("\r\n", $headers));
} catch (Exception $e) {
    $ok = false;
}

if (!$ok) {
    // Mail failed — give a generic error (don't leak server internals)
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to send message. Please try again later.']);
    exit;
}

// Success
http_response_code(200);
echo json_encode(['success' => true, 'message' => 'Message sent.']);
exit;
