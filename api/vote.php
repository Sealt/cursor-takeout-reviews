<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// 设置响应头
header('Content-Type: application/json');

// 获取请求体
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// 初始化响应
$response = [
    'success' => false,
    'message' => '',
    'count' => 0
];

// 验证请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = '无效的请求方法';
    echo json_encode($response);
    exit;
}

if (!$data || !isset($data['review_id']) || !isset($data['vote_type'])) {
    $response['message'] = '缺少必要参数';
    echo json_encode($response);
    exit;
}

$review_id = (int)$data['review_id'];
$vote_type = $data['vote_type'];

// 验证投票类型
if ($vote_type !== 'agree' && $vote_type !== 'disagree') {
    $response['message'] = '无效的投票类型';
    echo json_encode($response);
    exit;
}

// 获取用户UA
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$user_ip = $_SERVER['REMOTE_ADDR'] ?? '';

// 生成唯一标识符
$voter_id = md5($user_agent . $user_ip);

// 检查是否已经投过票
$current_vote = getCurrentVote($review_id, $voter_id);

if ($current_vote) {
    // 如果已经投过票
    if ($current_vote['vote_type'] === $vote_type) {
        // 如果点击的是相同类型，则取消投票
        $result = removeVote($review_id, $voter_id);
        if ($result) {
            $count = getVoteCount($review_id, $vote_type);
            $response['success'] = true;
            $response['count'] = $count;
            $response['message'] = '已取消投票';
            $response['status'] = 'removed';
        } else {
            $response['message'] = '操作失败，请稍后再试';
        }
    } else {
        // 如果点击的是不同类型，则更新投票
        $result = updateVote($review_id, $voter_id, $vote_type);
        if ($result) {
            $count = getVoteCount($review_id, $vote_type);
            $response['success'] = true;
            $response['count'] = $count;
            $response['message'] = '投票已更新';
            $response['status'] = 'updated';
            // 返回另一个类型的最新计数
            $other_type = $vote_type === 'agree' ? 'disagree' : 'agree';
            $response['other_count'] = getVoteCount($review_id, $other_type);
            $response['other_type'] = $other_type;
        } else {
            $response['message'] = '操作失败，请稍后再试';
        }
    }
} else {
    // 如果没有投过票，则添加新投票
    $result = addVote($review_id, $voter_id, $vote_type);
    if ($result) {
        $count = getVoteCount($review_id, $vote_type);
        $response['success'] = true;
        $response['count'] = $count;
        $response['message'] = '投票成功';
        $response['status'] = 'added';
    } else {
        $response['message'] = '投票失败，请稍后再试';
    }
}

echo json_encode($response); 