<?php

class RatingController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        // Require authenticated user for rating actions
        require_once __DIR__ . '/../../core/Auth.php';
        $user = \Auth::user();
        if (empty($user['id'])) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['ok' => false, 'message' => 'Unauthorized']);
            exit;
        }
    }

    public function store()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'message' => 'Method Not Allowed']);
            exit;
        }

        // Read JSON body
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => 'Invalid JSON']);
            exit;
        }

        require_once __DIR__ . '/../../core/Auth.php';
        $user = \Auth::user();
        $raterId = (int)($user['id'] ?? 0);

        $ratedUserId = isset($data['user_id']) ? (int)$data['user_id'] : 0;
        $rating = isset($data['rating']) ? (int)$data['rating'] : 0;

        if ($ratedUserId <= 0 || $rating <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => 'Missing parameters']);
            exit;
        }

        if ($raterId === $ratedUserId) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'message' => 'Cannot rate yourself']);
            exit;
        }

        if ($rating < 1 || $rating > 5) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => 'Rating must be between 1 and 5']);
            exit;
        }

        $db = \Database::connect();

        // Prevent duplicate ratings by same rater: if a rating exists, deny re-rating
        $check = $db->prepare('SELECT id FROM user_ratings WHERE rater_id = ? AND rated_user_id = ? LIMIT 1');
        $check->execute([$raterId, $ratedUserId]);
        $row = $check->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'message' => 'Bạn đã đánh giá người này trước đó']);
            exit;
        }

        // Insert new rating
        $stmt = $db->prepare('INSERT INTO user_ratings (rater_id, rated_user_id, rating, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())');
        $stmt->execute([$raterId, $ratedUserId, $rating]);

        // Recompute aggregate
        $agg = $db->prepare('SELECT AVG(rating) AS avg_rating, COUNT(*) AS cnt FROM user_ratings WHERE rated_user_id = ?');
        $agg->execute([$ratedUserId]);
        $res = $agg->fetch(PDO::FETCH_ASSOC);

        $avg = isset($res['avg_rating']) ? round((float)$res['avg_rating'], 1) : 0;
        $count = isset($res['cnt']) ? (int)$res['cnt'] : 0;

        // Persist aggregates on users table so subsequent page loads reflect new rating
        try {
            $update = $db->prepare('UPDATE users SET rating = ?, rating_count = ?, updated_at = NOW() WHERE id = ?');
            $update->execute([$avg, $count, $ratedUserId]);
        } catch (Exception $e) {
            // Do not fail the request if updating users fails; just log if possible
            error_log('RatingController: failed to update users aggregates: ' . $e->getMessage());
        }

        echo json_encode(['ok' => true, 'new_rating' => $avg, 'count' => $count]);
        exit;
    }
}
