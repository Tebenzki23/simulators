<?php
/**
 * Nexus Browser Backend Proxy
 * Bypasses X-Frame-Options by fetching content server-side.
 */

// Allow from any origin (since this is a local OS sim)
header("Access-Control-Allow-Origin: *");

if (!isset($_GET['url']) || empty($_GET['url'])) {
    die("Error: No URL provided.");
}

$url = $_GET['url'];

// Basic validation to ensure it's a URL
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    die("Error: Invalid URL format.");
}

// Parse the URL to get the base for the <base> tag
$parsed_url = parse_url($url);
$base_url = $parsed_url['scheme'] . '://' . $parsed_url['host'];
if (isset($parsed_url['port'])) {
    $base_url .= ':' . $parsed_url['port'];
}
$base_url .= '/';

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local XAMPP compatibility
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

// Mimic a modern Windows Chrome browser to avoid bot blocks
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

// Fetch the content
$content = curl_exec($ch);
$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    $error = curl_error($ch);
    curl_close($ch);
    die("Proxy Error: Failed to fetch the URL. ($error)");
}
curl_close($ch);

// Pass through the original content type if it's not HTML (e.g., images, css)
if ($content_type && strpos($content_type, 'text/html') === false) {
    header("Content-Type: $content_type");
    echo $content;
    exit;
}

// Force HTML content type
header("Content-Type: text/html; charset=UTF-8");

// Inject the <base> tag to fix relative links (CSS, JS, images, links)
// We try to insert it right after <head>, or at the very beginning if <head> is missing
$base_tag = "<base href=\"$base_url\">";

if (stripos($content, '<head>') !== false) {
    $content = preg_replace('/<head>/i', "<head>\n    " . $base_tag, $content, 1);
} else {
    // If no <head>, prepend it
    $content = $base_tag . "\n" . $content;
}

// Output the modified content
echo $content;
?>
