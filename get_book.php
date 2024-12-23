<?php
session_start();
require_once 'koneksi.php';
require_once 'class/Book.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (isset($_GET['id'])) {
    $conn = connectDB();
    $book = new Book($conn);
    $book->setId($_GET['id']);
    
    $result = $book->readOne();
    
    if ($result) {
        echo json_encode($result);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Book not found']);
    }
    
    $conn->close();
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Book ID is required']);
}
?>