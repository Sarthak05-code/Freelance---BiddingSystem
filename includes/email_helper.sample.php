<?php
define("ABSTRACT_API_KEY", "YOUR API KEY HERE");

/**
 * Validate an email using Abstract API's Email Reputation endpoint
 */
function is_email_deliverable(string $email): bool
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    if (ABSTRACT_API_KEY === "" || empty(ABSTRACT_API_KEY)) {
        return true;
    }

    $url =
        "https://emailreputation.abstractapi.com/v1/?api_key=" .
        ABSTRACT_API_KEY .
        "&email=" .
        urlencode($email);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
    ]);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error || !$response) {
        return true; // fail open — don't block registration if the API is down
    }

    $data = json_decode($response, true);

    // Based on actual response structure from emailreputation endpoint
    $deliverability = $data["email_deliverability"] ?? [];
    $status = $deliverability["status"] ?? "unknown";
    $isFormatValid = $deliverability["is_format_valid"] ?? true;

    // Reject if format is invalid or status is undeliverable
    if (!$isFormatValid || $status === "undeliverable") {
        return false;
    }

    return true;
}
