<?php

$serviceUrl = 'https://chromium-i18n.appspot.com/ssl-address';

// Make sure we're starting from a clean slate.
if (is_dir(__DIR__ . '/assets')) {
    die('The assets/ directory already exists.');
}
// Make sure aria2 is installed.
exec('aria2c --version', $ariaVersion);
if (empty($ariaVersion) || strpos($ariaVersion[0], 'aria2 version') === false) {
    die('aria2 must be installed.');
}

// Prepare the filesystem.
mkdir(__DIR__ . '/assets');
mkdir(__DIR__ . '/assets/google');

// Fetch country data (CLDR).
echo "Fetching country data.\n";
exec('git clone --depth 1 https://github.com/unicode-org/cldr-json.git ' . __DIR__ . '/assets/cldr');

// Fetch address data (Google).
echo "Generating the url list.\n";
$urlList = generate_url_list();
file_put_contents(__DIR__ . '/assets/url_list.txt', $urlList);

// Invoke aria2 and fetch the data.
echo "Downloading the raw data from Google's endpoint.\n";
exec('aria2c -u 16 -i ' . __DIR__ . '/assets/url_list.txt -d ' . __DIR__ . '/assets/google');

echo "Download complete.\n";

/**
 * Generates a list of all urls that need to be downloaded using aria2.
 */
function generate_url_list()
{
    global $serviceUrl;

    $index = file_get_contents($serviceUrl);
    // Get all links that start with /ssl-address/data.
    // This avoids the /address/examples urls which aren't needed.
    preg_match_all("/<a\shref=\'\/ssl-address\/data\/([^\"]*)\'>/siU", $index, $matches);
    // Assemble the urls
    $list = array_map(function ($href) use ($serviceUrl) {
        // Replace the url encoded single slash with a real one.
        $href = str_replace('&#39;', "'", $href);
        // Convert 'US/CA' into 'US_CA.json'.
        $filename = str_replace('/', '_', $href) . '.json';
        $url = $serviceUrl . '/data/' . $href;
        // aria2 expects the out= parameter to be in the next row,
        // indented by two spaces.
        $url .= "\n  out=$filename";

        return $url;
    }, $matches[1]);

    return implode("\n", $list);
}

