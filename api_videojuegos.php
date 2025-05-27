<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$filename = __DIR__ . "/data.json";

function loadData() {
    global $filename;
    if (!file_exists($filename)) {
        file_put_contents($filename, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    $json = file_get_contents($filename);
    return json_decode($json, true);
}

function saveData(array $data) {
    global $filename;
    file_put_contents($filename, json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function getRequestData() {
    $input = file_get_contents("php://input");
    return json_decode($input, true);
}

$method = $_SERVER["REQUEST_METHOD"];
$path = trim(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), '/');
$segments = explode('/', $path);

$id = null;
if (is_numeric(end($segments))) {
    $id = (int)array_pop($segments);
}

if (!in_array("api_videojuegos.php", $segments)) {
    http_response_code(404);
    echo json_encode(["error" => "Ruta no válida"]);
    exit();
}

$videojuegos = loadData();

switch ($method) {
    case "GET":
        if ($id === null) {

            echo json_encode($videojuegos, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } else {

            foreach ($videojuegos as $juego) {
                if ($juego["id"] === $id) {
                    echo json_encode($juego, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                    exit();
                }
            }
            http_response_code(404);
            echo json_encode(["error" => "Juego no encontrado"]);
        }
        break;

    case "POST":
        $data = getRequestData();
        if (!$data || !isset($data["Name"], $data["Price"])) {
            http_response_code(400);
            echo json_encode(["error" => "Datos inválidos o incompletos"]);
            break;
        }

        $maxId = 0;
        foreach ($videojuegos as $juego) {
            if ($juego["id"] > $maxId) $maxId = $juego["id"];
        }
        $data["id"] = $maxId + 1;
        $videojuegos[] = $data;
        saveData($videojuegos);
        http_response_code(201);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        break;

    case "PUT":
        if ($id === null) {
            http_response_code(400);
            echo json_encode(["error" => "Se requiere ID para actualizar"]);
            break;
        }
        $data = getRequestData();
        $updated = false;
        foreach ($videojuegos as &$juego) {
            if ($juego["id"] === $id) {
                foreach ($data as $key => $value) {
                    if ($key !== "id") {
                        $juego[$key] = $value;
                    }
                }
                $updated = true;
                saveData($videojuegos);
                echo json_encode($juego, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                break;
            }
        }
        if (!$updated) {
            http_response_code(404);
            echo json_encode(["error" => "Juego no encontrado para actualizar"]);
        }
        break;

    case "DELETE":
        if ($id === null) {
            http_response_code(400);
            echo json_encode(["error" => "Se requiere ID para eliminar"]);
            break;
        }
        $deleted = false;
        foreach ($videojuegos as $index => $juego) {
            if ($juego["id"] === $id) {
                unset($videojuegos[$index]);
                $deleted = true;
                saveData($videojuegos);
                http_response_code(204);
                break;
            }
        }
        if (!$deleted) {
            http_response_code(404);
            echo json_encode(["error" => "Juego no encontrado para eliminar"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
}
