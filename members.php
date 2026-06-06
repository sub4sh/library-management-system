<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

function logActivity($conn, $action, $entity_id, $desc) {
    $desc = $conn->real_escape_string($desc);
    $conn->query("INSERT INTO activity_log (action_type, entity_type, entity_id, description) 
                  VALUES ('$action', 'member', $entity_id, '$desc')");
}

// validation functions
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validateNepalPhone($phone) {

    return preg_match('/^(?:9\d{9}|01\d{7})$/', $phone);
}

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $result = $conn->query("SELECT * FROM members WHERE id = $id");
            echo json_encode($result->fetch_assoc());
        } elseif (isset($_GET['search'])) {
            $search = $conn->real_escape_string($_GET['search']);
            $result = $conn->query("SELECT * FROM members 
                                    WHERE name LIKE '%$search%' OR email LIKE '%$search%' 
                                    ORDER BY name");
            $members = [];
            while ($row = $result->fetch_assoc()) $members[] = $row;
            echo json_encode($members);
        } else {
            $result = $conn->query("SELECT * FROM members ORDER BY name");
            $members = [];
            while ($row = $result->fetch_assoc()) $members[] = $row;
            echo json_encode($members);
        }
        break;

    case 'POST':
        $name = $conn->real_escape_string($input['name'] ?? '');
        $email = $conn->real_escape_string($input['email'] ?? '');
        $phone = $conn->real_escape_string($input['phone'] ?? '');
        $address = $conn->real_escape_string($input['address'] ?? '');

        // Required fields check
        if (empty($name) || empty($email) || empty($phone) || empty($address)) {
            http_response_code(400);
            echo json_encode(['error' => 'All fields are required']);
            exit;
        }

        // Validate email
        if (!validateEmail($email)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid email format']);
            exit;
        }

        // Validate Nepali phone number
        if (!validateNepalPhone($phone)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid Nepali phone number']);
            exit;
        }

        $sql = "INSERT INTO members (name, email, phone, address) 
                VALUES ('$name', '$email', '$phone', '$address')";
        
        if ($conn->query($sql)) {
            $newId = $conn->insert_id;
            logActivity($conn, 'add_member', $newId, "Added member: $name ($email)");
            echo json_encode(['success' => true, 'id' => $newId]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => $conn->error]);
        }
        break;

    case 'PUT':
        $id = intval($input['id']);
        $name = $conn->real_escape_string($input['name'] ?? '');
        $email = $conn->real_escape_string($input['email'] ?? '');
        $phone = $conn->real_escape_string($input['phone'] ?? '');
        $address = $conn->real_escape_string($input['address'] ?? '');
        $status = $conn->real_escape_string($input['status'] ?? 'active');

        // Required fields check
        if (empty($name) || empty($email) || empty($phone) || empty($address)) {
            http_response_code(400);
            echo json_encode(['error' => 'All fields are required']);
            exit;
        }

        // Validate email
        if (!validateEmail($email)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid email format']);
            exit;
        }

        // Validate Nepali phone number
        if (!validateNepalPhone($phone)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid Nepali phone number']);
            exit;
        }

        $sql = "UPDATE members 
                SET name='$name', email='$email', phone='$phone', address='$address', status='$status' 
                WHERE id=$id";
        
        if ($conn->query($sql)) {
            logActivity($conn, 'edit_member', $id, "Updated member: $name (status: $status)");
            echo json_encode(['success' => true]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => $conn->error]);
        }
        break;

    case 'DELETE':
        $id = intval($_GET['id']);
        $member = $conn->query("SELECT name, email FROM members WHERE id=$id")->fetch_assoc();
        if ($conn->query("DELETE FROM members WHERE id=$id")) {
            logActivity($conn, 'delete_member', $id, "Deleted member: {$member['name']} ({$member['email']})");
            echo json_encode(['success' => true]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => $conn->error]);
        }
        break;
}

$conn->close();
?>

