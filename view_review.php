<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

// 获取评价ID
$review_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 获取来源页面的筛选参数
$referer_params = [];
if (isset($_GET['search'])) {
    $referer_params['search'] = $_GET['search'];
}
if (isset($_GET['rating'])) {
    $referer_params['rating'] = $_GET['rating'];
}
if (isset($_GET['sort'])) {
    $referer_params['sort'] = $_GET['sort'];
}
if (isset($_GET['page'])) {
    $referer_params['page'] = $_GET['page'];
}

// 构建返回URL
$return_url = 'index.php';
if (!empty($referer_params)) {
    $return_url .= '?' . http_build_query($referer_params);
}

// 如果ID无效，重定向到首页
if ($review_id <= 0) {
    header('Location: ' . $return_url);
    exit;
}

// 获取评价详情
$review = getReviewById($review_id);
if (!$review) {
    header('Location: ' . $return_url);
    exit;
}

// 获取评论列表（不包括已删除的）
$comments = getCommentsByReviewId($review_id);

$pageTitle = "评价详情 - " . htmlspecialchars($review['restaurant_name']);
include 'includes/header.php';

// 获取用户UA和IP生成唯一标识符
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$user_ip = $_SERVER['REMOTE_ADDR'] ?? '';
$voter_id = md5($user_agent . $user_ip);

// 检查用户是否已投票
$current_vote = getCurrentVote($review_id, $voter_id);
$user_vote_type = $current_vote ? $current_vote['vote_type'] : null;

// 处理添加评论
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content'] ?? '');
    
    if (empty($content)) {
        $error = '请输入评论内容';
    } else {
        $result = addComment($review_id, $content);
        if ($result) {
            $success = '评论添加成功！';
            // 重新获取评论列表
            $comments = getCommentsByReviewId($review_id);
            // 清空表单
            $content = '';
        } else {
            $error = '评论添加失败，请稍后再试';
        }
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
            <!-- 评价详情 -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><?php echo htmlspecialchars($review['restaurant_name']); ?></h5>
                    <span class="badge <?php echo $review['rating'] == '好吃' ? 'bg-success' : 'bg-danger'; ?>">
                        <?php if ($review['rating'] == '好吃'): ?>
                            <i class="bi bi-emoji-smile me-1"></i>
                        <?php else: ?>
                            <i class="bi bi-emoji-frown me-1"></i>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($review['rating']); ?>
                    </span>
                </div>
                <div class="card-body">
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($review['content'])); ?></p>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-secondary vote-btn <?php echo $user_vote_type === 'agree' ? 'active' : ''; ?>" 
                                    data-review-id="<?php echo $review['id']; ?>" 
                                    data-vote-type="agree">
                                <i class="bi bi-hand-thumbs-up me-1"></i> <span class="agree-count"><?php echo $review['agree_count']; ?></span>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary vote-btn <?php echo $user_vote_type === 'disagree' ? 'active' : ''; ?>" 
                                    data-review-id="<?php echo $review['id']; ?>" 
                                    data-vote-type="disagree">
                                <i class="bi bi-hand-thumbs-down me-1"></i> <span class="disagree-count"><?php echo $review['disagree_count']; ?></span>
                            </button>
                        </div>
                        <small class="text-muted"><i class="bi bi-clock me-1"></i> <?php echo formatDateTime($review['created_at']); ?></small>
                    </div>
                </div>
            </div>
            
            <!-- 评论表单 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-chat-right-text me-2"></i>添加评论</h5>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
                        <div class="mb-3">
                            <textarea class="form-control" id="content" name="content" rows="3" placeholder="请输入您的评论..." required><?php echo isset($content) ? htmlspecialchars($content) : ''; ?></textarea>
                        </div>
                        <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-send me-1"></i> 提交评论
                        </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- 评论列表 -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-chat-square-dots me-2"></i>评论</h5>
                    <span class="badge bg-primary"><?php echo count($comments); ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if (count($comments) > 0): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($comments as $comment): ?>
                                <li class="list-group-item p-3">
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                                    <div class="d-flex justify-content-end mb-1">
                                        <small class="text-muted"><i class="bi bi-clock me-1"></i> <?php echo formatDateTime($comment['created_at']); ?></small>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-chat-square me-2 text-muted"></i>
                            <span class="text-muted">暂无评论</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="mt-4 d-flex justify-content-end">
                <a href="<?php echo $return_url; ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> 返回首页
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 点赞和踩的功能
    const voteButtons = document.querySelectorAll('.vote-btn');
    voteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const reviewId = this.getAttribute('data-review-id');
            const voteType = this.getAttribute('data-vote-type');
            const isActive = this.classList.contains('active');
            
            fetch('api/vote.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    review_id: reviewId,
                    vote_type: voteType
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 更新当前按钮计数
                    const countElement = this.querySelector(voteType === 'agree' ? '.agree-count' : '.disagree-count');
                    countElement.textContent = data.count;
                    
                    // 处理按钮状态
                    if (data.status === 'removed') {
                        // 取消投票
                        this.classList.remove('active');
                    } else if (data.status === 'added') {
                        // 新增投票
                        this.classList.add('active');
                    } else if (data.status === 'updated') {
                        // 更新投票（从一种类型切换到另一种类型）
                        this.classList.add('active');
                        
                        // 找到另一个按钮并移除active状态
                        const otherType = voteType === 'agree' ? 'disagree' : 'agree';
                        const otherButton = document.querySelector(`.vote-btn[data-review-id="${reviewId}"][data-vote-type="${otherType}"]`);
                        if (otherButton) {
                            otherButton.classList.remove('active');
                            // 更新另一个按钮的计数
                            const otherCountElement = otherButton.querySelector(`.${otherType}-count`);
                            otherCountElement.textContent = data.other_count;
                        }
                    }
                } else {
                    alert(data.message || '操作失败，请稍后再试');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('发生错误，请稍后再试');
            });
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?> 