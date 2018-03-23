<?php

set_time_limit(0);
date_default_timezone_set('UTC');

if (empty($argv[1])) {
    echo "Please specify path to CLDR subdivision file.\n";
    exit;
} elseif (!file_exists($argv[1])) {
    echo "CLDR file doesn't exist. - FILE: {$argv[1]}\n";
    exit;
}

$str = file_get_contents($argv[1]);
$p = xml_parser_create();
xml_parse_into_struct($p, $str, $vals, $index);
xml_parser_free($p);

echo "- Parsing XML File ... ";
foreach ($vals as $v) {
    if ($v['tag'] == 'SUBDIVISION') {
        $countryCode = strtoupper(substr($v['attributes']['TYPE'], 0, 2));
        $stateCode = strtoupper(substr($v['attributes']['TYPE'], 2));
        $stateLabel = $v['value'];
        $data[$countryCode][$stateCode] = $stateLabel;
    }
}
echo "\n";

foreach ($data as $country => $state) {
    $formatFile = "../resources/address_format/{$country}.json";
    if (file_exists($formatFile)) {
        $formatData = file_get_contents($formatFile);
        $formatData = json_decode($formatData, true);
        if (strpos($formatData['format'], 'administrativeArea')) {
            $subFile = "../resources/subdivision/{$country}.json";
            if (!file_exists($subFile)) {
                echo "  - {$country} : GENERATING SUBDIVISION FILE...\n";
                create_subdivision_file($country, $state);
            } else {
                echo "  - {$country} : SUBDIVISION FILE ALREADY EXISTS.\n";
            }
        } else {
            echo "  - {$country} : STATE IS NOT PART OF ADDRESS FORMAT.\n";
        }
    } else {
        echo "  - {$country} : ADDRESS FORMAT FILE DOESN'T EXIST.\n";
    }
}

function create_subdivision_file($country, $state)
{
    $subFile = "../resources/subdivision/{$country}.json";

    $data = [
        'country_code' => $country,
        'parent_id' => null,
        'locale' => 'und',
        'subdivisions' => []
    ];

    foreach ($state as $code => $label) {
        $data['subdivisions']["{$country}-{$code}"] = [
            'code' => $code,
            'name' => $label
        ];
    }
    file_put_json($subFile, $data);

    $depthFile = "../resources/subdivision/depths.json";
    $depthData = json_decode(file_get_contents($depthFile), true);
    $depthData[$country] = 1;
    file_put_json($depthFile, $depthData);
}

function file_put_json($filename, $data)
{
    $data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    // Indenting with tabs instead of 4 spaces gives us 20% smaller files.
    $data = str_replace('    ', "\t", $data);
    file_put_contents($filename, $data);
}