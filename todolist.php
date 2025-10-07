<?php
// === CORS ===
header("Access-Control-Allow-Origin: *"); // bisa diganti domain spesifik, misal: https://namaproject.netlify.app
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Jika preflight request (OPTIONS), cukup respon kosong agar lolos
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// === RESPONSE TYPE ===
header('Content-Type: application/json');

// File penyimpanan
$file = 'todos.json';

// Ambil data dari file
if (file_exists($file)) {
    $todos = json_decode(file_get_contents($file), true);
    if (!$todos) $todos = [];
} else {
    $todos = [];
}

// Ambil metode HTTP
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Menampilkan semua TODO
        echo json_encode($todos);
        break;

    case 'POST':
        // Menambah TODO baru
        $data = json_decode(file_get_contents('php://input'), true);
        if (isset($data['task']) && !empty($data['task'])) {
            $id = count($todos) + 1;
            $todos[] = [
                'id' => $id,
                'task' => $data['task']
            ];
            file_put_contents($file, json_encode($todos, JSON_PRETTY_PRINT));
            echo json_encode(['message' => 'TODO added', 'todo' => end($todos)]);
        } else {
            echo json_encode(['error' => 'Task is required']);
        }
        break;

    case 'DELETE':
        // Menghapus TODO berdasarkan ID
        $data = json_decode(file_get_contents('php://input'), true);
        if (isset($data['id'])) {
            $id = $data['id'];
            $found = false;
            foreach ($todos as $key => $todo) {
                if ($todo['id'] == $id) {
                    unset($todos[$key]);
                    $todos = array_values($todos); // Reset index
                    file_put_contents($file, json_encode($todos, JSON_PRETTY_PRINT));
                    echo json_encode(['message' => 'TODO deleted']);
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                echo json_encode(['error' => 'TODO not found']);
            }
        } else {
            echo json_encode(['error' => 'ID is required']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>
