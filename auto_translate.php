<?php

$file_path = '/Users/giovannavilchis/Herd/back3.6/resources/lang/es/messages.php';

$content = file_get_contents($file_path);

// Regex to find all key-value pairs: 'key' => 'value',
preg_match_all("/('(?:\\\\'|[^'])*')\s*=>\s*('(?:\\\\'|[^'])*')/", $content, $matches, PREG_SET_ORDER);

echo "Found " . count($matches) . " keys to check/translate.\n";

function translate_chunk($texts) {
    if (empty($texts)) return [];
    
    $separator = " |~| ";
    $combined_text = implode($separator, $texts);
    
    $url = "https://translate.googleapis.com/translate_a/single?client=gtx&sl=en&tl=es&dt=t&q=" . urlencode($combined_text);
    
    $options = [
        "http" => [
            "header" => "User-Agent: Mozilla/5.0\r\n",
            "method" => "GET",
            "timeout" => 10
        ],
        "ssl" => [
            "verify_peer" => false,
            "verify_peer_name" => false,
        ]
    ];
    $context = stream_context_create($options);
    
    $response = @file_get_contents($url, false, $context);
    if ($response === false) return null;
    
    $result = json_decode($response, true);
    if (!$result || !isset($result[0])) return null;
    
    $translated_combined = "";
    foreach ($result[0] as $item) {
        if (isset($item[0])) {
            $translated_combined .= $item[0];
        }
    }
    
    // Split
    $translated_texts = preg_split('/\s*\|\s*~\s*\|\s*/', $translated_combined);
    
    if (count($translated_texts) === count($texts)) {
        return $translated_texts;
    }
    
    return null;
}

function translate_individual($texts) {
    $translated = [];
    foreach ($texts as $text) {
        $url = "https://translate.googleapis.com/translate_a/single?client=gtx&sl=en&tl=es&dt=t&q=" . urlencode($text);
        $options = [
            "http" => [
                "header" => "User-Agent: Mozilla/5.0\r\n",
                "timeout" => 5
            ],
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ]
        ];
        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);
        
        if ($response !== false) {
            $result = json_decode($response, true);
            if ($result && isset($result[0])) {
                $t = "";
                foreach ($result[0] as $item) {
                    if (isset($item[0])) {
                        $t .= $item[0];
                    }
                }
                $translated[] = $t;
            } else {
                $translated[] = $text;
            }
        } else {
            $translated[] = $text;
        }
        usleep(100000); // 100ms
    }
    return $translated;
}

$batch_size = 40;
$translated_dict = [];
$keys_values = [];

foreach ($matches as $match) {
    $k = $match[1];
    $v = $match[2];
    $clean_v = str_replace("\\'", "'", substr($v, 1, -1));
    $keys_values[] = [$k, $v, $clean_v];
}

$total = count($keys_values);
echo "Starting translation...\n";

for ($i = 0; $i < $total; $i += $batch_size) {
    $chunk = array_slice($keys_values, $i, $batch_size);
    $texts = array_column($chunk, 2);
    
    $translated = translate_chunk($texts);
    if (!$translated) {
        $translated = translate_individual($texts);
    }
    
    foreach ($chunk as $j => $item) {
        $k = $item[0];
        $orig_v = $item[1];
        $clean_v = $item[2];
        
        if (isset($translated[$j])) {
            $t = $translated[$j];
            $t = str_replace("'", "\\'", $t);
            
            // capitalize if needed
            if (!empty($clean_v) && ctype_upper($clean_v[0]) && !empty($t) && ctype_lower($t[0])) {
                $t = ucfirst($t);
            }
            
            $translated_dict[$k] = "'" . $t . "'";
        } else {
            $translated_dict[$k] = $orig_v;
        }
    }
    
    if ($i % 400 === 0) {
        echo "Processed $i/$total...\n";
    }
    usleep(200000); // 200ms
}

echo "Translation done. Replacing in file...\n";

$lines = explode("\n", $content);
$new_lines = [];
foreach ($lines as $line) {
    if (preg_match("/('(?:\\\\'|[^'])*')\s*=>\s*('(?:\\\\'|[^'])*')/", $line, $match, PREG_OFFSET_CAPTURE)) {
        $k = $match[1][0];
        if (isset($translated_dict[$k])) {
            $start = $match[2][1];
            $length = strlen($match[2][0]);
            $new_line = substr_replace($line, $translated_dict[$k], $start, $length);
            $new_lines[] = $new_line;
            continue;
        }
    }
    $new_lines[] = $line;
}

file_put_contents($file_path, implode("\n", $new_lines));

echo "All done!\n";
