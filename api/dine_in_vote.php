<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// 设置响应头
header('Content-Type: application/json');

// 获取用户UA和IP生成唯一标识符
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$user_ip = $_SERVER['REMOTE_ADDR'] ?? '';
$voter_id = md5($user_agent . $user_ip);

// 获取请求体
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// 检查必要参数
if (!isset($data['review_id']) || !isset($data['vote_type'])) {
    echo json_encode([
        'success' => false,
        'message' => '缺少必要参数'
    ]);
    exit;
}

$review_id = (int)$data['review_id'];
$vote_type = $data['vote_type'];

// 验证投票类型
if ($vote_type !== 'agree' && $vote_type !== 'disagree') {
    echo json_encode([
        'success' => false,
        'message' => '无效的投票类型'
    ]);
    exit;
}

// 检查评价是否存在
$review = getDineInReviewById($review_id);
if (!$review) {
    echo json_encode([
        'success' => false,
        'message' => '评价不存在'
    ]);
    exit;
}

// 检查用户是否已投票
$current_vote = getCurrentDineInVote($review_id, $voter_id);

// 处理投票逻辑
if (!$current_vote) {
    // 用户未投票，添加新投票
    $result = addDineInVote($review_id, $voter_id, $vote_type);
    if ($result) {
        echo json_encode([
            'success' => true,
            'status' => 'added',
            'count' => $review[$vote_type === 'agree' ? 'agree_count' : 'disagree_count'] + 1
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => '投票失败，请稍后再试'
        ]);
    }
} else if ($current_vote['vote_type'] === $vote_type) {
    // 用户已投相同类型的票，取消投票
    $result = removeDineInVote($review_id, $voter_id);
    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'status' => 'removed',
            'count' => $review[$vote_type === 'agree' ? 'agree_count' : 'disagree_count'] - 1
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => '取消投票失败，请稍后再试'
        ]);
    }
} else {
    // 用户已投不同类型的票，更新投票
    $result = updateDineInVote($review_id, $voter_id, $vote_type);
    if ($result['success']) {
        $other_type = $vote_type === 'agree' ? 'disagree' : 'agree';
        echo json_encode([
            'success' => true,
            'status' => 'updated',
            'count' => $review[$vote_type === 'agree' ? 'agree_count' : 'disagree_count'] + 1,
            'other_count' => $review[$other_type === 'agree' ? 'agree_count' : 'disagree_count'] - 1
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => '更新投票失败，请稍后再试'
        ]);
    }
}
