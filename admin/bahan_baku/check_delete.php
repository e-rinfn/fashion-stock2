<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

// Set proper headers first
header('Content-Type: application/json');

// Check for errors in database connection
if ($conn->connect_error) {
    echo json_encode([
        'can_delete' => false,
        'message' => 'Database connection error'
    ]);
    exit;
}

// Validate input
$id_bahan = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_bahan <= 0) {
    echo json_encode([
        'can_delete' => false,
        'message' => 'Invalid material ID'
    ]);
    exit;
}

try {
    // Check relations with other tables
    $relations = [
        'pengiriman pemotong' => "SELECT 1 FROM pengiriman_pemotong WHERE id_bahan = ? LIMIT 1",
    ];

    $reasons = [];

    // Prepare statements for better security
    foreach ($relations as $name => $sql) {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("i", $id_bahan);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $reasons[] = $name;
        }
        $stmt->close();
    }

    if (!empty($reasons)) {
        echo json_encode([
            'can_delete' => false,
            'message' => 'Material cannot be deleted because it is used in: ' . implode(', ', $reasons)
        ]);
    } else {
        echo json_encode([
            'can_delete' => true,
            'message' => ''
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'can_delete' => false,
        'message' => 'Error checking material relations: ' . $e->getMessage()
    ]);
}
