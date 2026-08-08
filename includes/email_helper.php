<?php
define("ABSTRACT_API_KEY", "c899406cc9a4451db9aaa280d19d53f6");

/**
 * Validate an email using Abstract API
 */
function is_email_deliverable(string $email): bool
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    if (
        ABSTRACT_API_KEY === "c899406cc9a4451db9aaa280d19d53f6" ||
        empty(ABSTRACT_API_KEY)
    ) {
        return true;
    }

    $url =
        "https://emailvalidation.abstractapi.com/v1/?api_key=" .
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
    $isFormatValid = $data["is_valid_format"]["value"] ?? true;
    $isDisposable = $data["is_disposable_email"]["value"] ?? false;
    $deliverability = $data["deliverability"] ?? "UNKNOWN";

    if (
        !$isFormatValid ||
        $isDisposable ||
        $deliverability === "UNDELIVERABLE"
    ) {
        return false;
    }

    return true;
}
