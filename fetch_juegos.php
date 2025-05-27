<?php

$pagina = $argv[1] ?? 1;

if (!is_numeric($pagina) || $pagina < 1) {
    $pagina = 1;
}

$url = "http://localhost/videojuegos/videojuegos.php?pagina=$pagina&formato=json";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo "Error CURL: " . curl_error($ch) . "\n";
    exit(1);
}

curl_close($ch);

echo "Respuesta JSON para página $pagina:\n";
echo $response . "\n";
?>
