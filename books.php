<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

function logActivity($conn, $action, $entity_id, $desc) {
    $desc = $conn->real_escape_string($desc);
    $conn->query("INSERT INTO activity_log (action_type, entity_type, entity_id, description) VALUES ('$action', 'book', $entity_id, '$desc')");
}

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $result = $conn->query("SELECT * FROM books WHERE id = $id");
            echo json_encode($result->fetch_assoc());
        } elseif (isset($_GET['search'])) {
            $search = $conn->real_escape_string($_GET['search']);
            $result = $conn->query("SELECT * FROM books WHERE title LIKE '%$search%' OR author LIKE '%$search%' OR genre LIKE '%$search%' ORDER BY title");
            $books = [];
            while ($row = $result->fetch_assoc()) $books[] = $row;
            echo json_encode($books);
        } else {
            $result = $conn->query("SELECT * FROM books ORDER BY title");
            $books = [];
            while ($row = $result->fetch_assoc()) $books[] = $row;
            echo json_encode($books);
        }
        break;

    case 'POST':
        $title = $conn->real_escape_string($input['title']);
        $author = $conn->real_escape_string($input['author']);
        $isbn = $conn->real_escape_string($input['isbn'] ?? '');
        $genre = $conn->real_escape_string($input['genre'] ?? '');
        $copies = intval($input['total_copies'] ?? 1);
        $year = intval($input['published_year'] ?? 0);

        $sql = "INSERT INTO books (title, author, isbn, genre, total_copies, available_copies, published_year) 
                VALUES ('$title', '$author', '$isbn', '$genre', $copies, $copies, " . ($year ?: 'NULL') . ")";
        
        if ($conn->query($sql)) {
            $newId = $conn->insert_id;
            logActivity($conn, 'add_book', $newId, "Added book: \"$title\" by $author ($copies copies)");
            echo json_encode(['success' => true, 'id' => $newId]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => $conn->error]);
        }
        break;

    case 'PUT':
        $id = intval($input['id']);
        $title = $conn->real_escape_string($input['title']);
        $author = $conn->real_escape_string($input['author']);
        $isbn = $conn->real_escape_string($input['isbn'] ?? '');
        $genre = $conn->real_escape_string($input['genre'] ?? '');
        $copies = intval($input['total_copies'] ?? 1);
        $year = intval($input['published_year'] ?? 0);

        $old = $conn->query("SELECT total_copies, available_copies FROM books WHERE id=$id")->fetch_assoc();
        $diff = $copies - $old['total_copies'];
        $newAvailable = max(0, $old['available_copies'] + $diff);

        $sql = "UPDATE books SET title='$title', author='$author', isbn='$isbn', genre='$genre', 
                total_copies=$copies, available_copies=$newAvailable, 
                published_year=" . ($year ?: 'NULL') . " WHERE id=$id";
        
        if ($conn->query($sql)) {
            logActivity($conn, 'edit_book', $id, "Updated book: \"$title\" by $author");
            echo json_encode(['success' => true]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => $conn->error]);
        }
        break;

    case 'DELETE':
        $id = intval($_GET['id']);
        $book = $conn->query("SELECT title, author FROM books WHERE id=$id")->fetch_assoc();
        if ($conn->query("DELETE FROM books WHERE id=$id")) {
            logActivity($conn, 'delete_book', $id, "Deleted book: \"{$book['title']}\" by {$book['author']}");
            echo json_encode(['success' => true]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => $conn->error]);
        }
        break;
}

$conn->close();
?>
