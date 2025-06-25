<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$logFile = 'tweets_log.txt';

function logMessage($msg) {
    global $logFile;
    $line = "=== " . date('Y-m-d H:i:s') . " ===\n" . $msg . "\n\n";
    file_put_contents($logFile, $line, FILE_APPEND);
    echo nl2br(htmlspecialchars($msg)) . "<br>";
}

$bearer_token = "";
$query = "videojuegos";
$max_results = 30; 
$start_time = date('c', strtotime('-3 days'));

$jsonFile = 'tweets.json';

logMessage("=== Nueva ejecución: " . date('Y-m-d H:i:s') . " ===");

if (!file_exists($jsonFile)) {
    logMessage("Archivo tweets.json NO existe. Intentando hacer la petición a la API...");

    $url = "https://api.twitter.com/2/tweets/search/recent?"
        . "query=" . urlencode($query)
        . "&max_results=$max_results"
        . "&start_time=" . urlencode($start_time)
        . "&tweet.fields=created_at"
        . "&expansions=author_id"
        . "&user.fields=username";

    logMessage("URL solicitada:\n$url");

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $bearer_token"
    ]);

    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    logMessage("HTTP Status Code: $httpcode");

    if ($curlError) {
        logMessage("Error en la solicitud CURL: $curlError");
    } else {
        logMessage("Respuesta cruda:\n$response");
    }

    $data = json_decode($response, true);
    if (!$data) {
        logMessage("Error decodificando JSON.");
        $tweets = [];
        $users = [];
    } else {
        $tweets = $data['data'] ?? [];
        $users = [];
        if (isset($data['includes']['users'])) {
            foreach ($data['includes']['users'] as $user) {
                $users[$user['id']] = $user['username'];
            }
        }
        file_put_contents($jsonFile, json_encode([
            'tweets' => $tweets,
            'users' => $users
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        logMessage("Archivo tweets.json creado con éxito.");
    }
} else {
    logMessage("Archivo tweets.json existe. Leyendo tweets desde archivo.");
    $json = json_decode(file_get_contents($jsonFile), true);
    $tweets = $json['tweets'] ?? [];
    $users = $json['users'] ?? [];
}

// Paginación
$tweets_per_page = 10;
$total_pages = ceil(count($tweets) / $tweets_per_page);
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$page = max(1, min($total_pages, $page)); 

$start_index = ($page - 1) * $tweets_per_page;
$current_tweets = array_slice($tweets, $start_index, $tweets_per_page);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Últimos tweets sobre videojuegos - Página <?= $page ?></title>
    <link rel="stylesheet" href="Twitter.css">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .tweet { background: #f0f0f0; padding: 10px; margin-bottom: 10px; border-radius: 5px; }
        .username { font-weight: bold; color: #333; }
        .date { font-size: 0.9em; color: #666; }
        .pagination a { margin: 0 5px; text-decoration: none; }
        .pagination a[style] { font-weight: bold; }
    </style>
</head>
<body>
    <h2>Últimos tweets sobre videojuegos - Página <?= $page ?></h2>

    <?php if (count($current_tweets) === 0): ?>
        <p>No se encontraron tweets.</p>
    <?php else: ?>
        <?php foreach ($current_tweets as $tweet): ?>
            <div class="tweet">
                <div class="username">@<?= htmlspecialchars($users[$tweet["author_id"]] ?? "desconocido") ?></div>
                <div class="date"><?= date("d M Y H:i", strtotime($tweet["created_at"])) ?></div>
                <p><?= nl2br(htmlspecialchars($tweet["text"])) ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Navegación -->
    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?= $i ?>" <?= $i === $page ? 'style="font-weight: bold;"' : '' ?>><?= $i ?></a>
        <?php endfor; ?>
    </div>
</body>
</html>