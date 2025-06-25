<?php
// Leer archivo JSON
$jsonFile = 'tweets.json';
if (!file_exists($jsonFile)) {
    die("El archivo tweets.json no existe. Ejecuta primero el script Python para obtener los tweets.");
}

$json = file_get_contents($jsonFile);
$data = json_decode($json, true);

$tweets = $data['tweets'] ?? [];
$users = $data['users'] ?? [];

// Configuración de paginación
$tweets_per_page = 10;
$total_pages = ceil(count($tweets) / $tweets_per_page);
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, min($total_pages, $page));

$start_index = ($page - 1) * $tweets_per_page;
$current_tweets = array_slice($tweets, $start_index, $tweets_per_page);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tweets sobre videojuegos - Página <?= $page ?></title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .tweet { background: #f0f0f0; margin-bottom: 10px; padding: 10px; border-radius: 5px; }
        .username { font-weight: bold; color: #333; }
        .date { font-size: 0.9em; color: #666; }
        .pagination a { margin: 0 5px; text-decoration: none; }
        .pagination a.active { font-weight: bold; }
    </style>
</head>
<body>
    <h2>Tweets sobre videojuegos - Página <?= $page ?></h2>

    <?php if (empty($current_tweets)): ?>
        <p>No se encontraron tweets.</p>
    <?php else: ?>
        <?php foreach ($current_tweets as $tweet): ?>
            <div class="tweet">
                <div class="username">@<?= htmlspecialchars($users[$tweet['author_id']] ?? 'desconocido') ?></div>
                <div class="date"><?= date('d M Y H:i', strtotime($tweet['created_at'])) ?></div>
                <p><?= nl2br(htmlspecialchars($tweet['text'])) ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?= $i ?>" class="<?= ($i === $page) ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
</body>
</html>