<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

function logActivity($conn, $action, $entity_id, $desc) {
    $desc = $conn->real_escape_string($desc);
    $conn->query("INSERT INTO activity_log (action_type, entity_type, entity_id, description) VALUES ('$action', 'borrowing', $entity_id, '$desc')");
}

function saveDashboardSnapshot($conn) {
    $totalBooks   = $conn->query("SELECT COUNT(*) as c FROM books")->fetch_assoc()['c'];
    $totalMembers = $conn->query("SELECT COUNT(*) as c FROM members WHERE status='active'")->fetch_assoc()['c'];
    $borrowed     = $conn->query("SELECT COUNT(*) as c FROM borrowings WHERE status IN ('borrowed','overdue')")->fetch_assoc()['c'];
    $overdue      = $conn->query("SELECT COUNT(*) as c FROM borrowings WHERE status='overdue'")->fetch_assoc()['c'];
    // INSERT OR REPLACE so only one snapshot per day is kept
    $conn->query("INSERT INTO dashboard_snapshots (snapshot_date, total_books, total_members, books_borrowed, overdue_count)
                  VALUES (CURDATE(), $totalBooks, $totalMembers, $borrowed, $overdue)
                  ON DUPLICATE KEY UPDATE
                      total_books=$totalBooks, total_members=$totalMembers,
                      books_borrowed=$borrowed, overdue_count=$overdue,
                      recorded_at=NOW()");
}

// Auto-mark overdue borrowings
$conn->query("UPDATE borrowings SET status='overdue' WHERE due_date < CURDATE() AND status='borrowed'");

switch ($method) {
    case 'GET':
        if (isset($_GET['stats'])) {
            $totalBooks   = $conn->query("SELECT COUNT(*) as c FROM books")->fetch_assoc()['c'];
            $totalMembers = $conn->query("SELECT COUNT(*) as c FROM members WHERE status='active'")->fetch_assoc()['c'];
            $borrowed     = $conn->query("SELECT COUNT(*) as c FROM borrowings WHERE status IN ('borrowed','overdue')")->fetch_assoc()['c'];
            $overdue      = $conn->query("SELECT COUNT(*) as c FROM borrowings WHERE status='overdue'")->fetch_assoc()['c'];

            // Save snapshot every time dashboard stats are loaded
            saveDashboardSnapshot($conn);

            echo json_encode(compact('totalBooks', 'totalMembers', 'borrowed', 'overdue'));
            break;
        }

        if (isset($_GET['activity'])) {
            $limit = intval($_GET['limit'] ?? 20);
            $result = $conn->query("SELECT * FROM activity_log ORDER BY performed_at DESC LIMIT $limit");
            $logs = [];
            while ($row = $result->fetch_assoc()) $logs[] = $row;
            echo json_encode($logs);
            break;
        }

        if (isset($_GET['snapshots'])) {
            $result = $conn->query("SELECT * FROM dashboard_snapshots ORDER BY snapshot_date DESC LIMIT 30");
            $snaps = [];
            while ($row = $result->fetch_assoc()) $snaps[] = $row;
            echo json_encode($snaps);
            break;
        }

        $where = '';
        if (isset($_GET['status']) && $_GET['status'] !== 'all') {
            $status = $conn->real_escape_string($_GET['status']);
            $where = "WHERE b.status='$status'";
        }

        $sql = "SELECT b.*, bk.title as book_title, bk.author, m.name as member_name, m.email
                FROM borrowings b
                JOIN books bk ON b.book_id = bk.id
                JOIN members m ON b.member_id = m.id
                $where
                ORDER BY b.borrow_date DESC";

        $result = $conn->query($sql);
        $borrowings = [];
        while ($row = $result->fetch_assoc()) $borrowings[] = $row;
        echo json_encode($borrowings);
        break;

    case 'POST':
        $bookId   = intval($input['book_id']);
        $memberId = intval($input['member_id']);
        $dueDate  = $conn->real_escape_string($input['due_date']);

        $book = $conn->query("SELECT title, available_copies FROM books WHERE id=$bookId")->fetch_assoc();
        if (!$book || $book['available_copies'] < 1) {
            http_response_code(400);
            echo json_encode(['error' => 'Book not available']);
            break;
        }

        $member = $conn->query("SELECT name, status FROM members WHERE id=$memberId")->fetch_assoc();
        if (!$member || $member['status'] !== 'active') {
            http_response_code(400);
            echo json_encode(['error' => 'Member not active']);
            break;
        }

        $conn->begin_transaction();
        try {
            $conn->query("INSERT INTO borrowings (book_id, member_id, due_date) VALUES ($bookId, $memberId, '$dueDate')");
            $newId = $conn->insert_id;
            $conn->query("UPDATE books SET available_copies = available_copies - 1 WHERE id=$bookId");
            $conn->commit();

            logActivity($conn, 'issue_book', $newId,
                "Issued \"{$book['title']}\" to {$member['name']} — due $dueDate");
            saveDashboardSnapshot($conn);

            echo json_encode(['success' => true, 'id' => $newId]);
        } catch (Exception $e) {
            $conn->rollback();
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'PUT':
        $id = intval($input['id']);
        $borrowing = $conn->query(
            "SELECT b.*, bk.title as book_title, m.name as member_name
             FROM borrowings b
             JOIN books bk ON b.book_id = bk.id
             JOIN members m ON b.member_id = m.id
             WHERE b.id=$id AND b.status != 'returned'"
        )->fetch_assoc();

        if (!$borrowing) {
            http_response_code(400);
            echo json_encode(['error' => 'Borrowing not found or already returned']);
            break;
        }

        $conn->begin_transaction();
        try {
            $conn->query("UPDATE borrowings SET return_date=CURDATE(), status='returned' WHERE id=$id");
            $conn->query("UPDATE books SET available_copies = available_copies + 1 WHERE id={$borrowing['book_id']}");
            $conn->commit();

            logActivity($conn, 'return_book', $id,
                "Returned \"{$borrowing['book_title']}\" by {$borrowing['member_name']}");
            saveDashboardSnapshot($conn);

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $conn->rollback();
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;
}

$conn->close();
?>
