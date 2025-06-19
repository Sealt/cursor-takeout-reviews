<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

session_start();

// 检查是否已登录
$isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// 处理登录请求
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isLoggedIn) {
    $password = $_POST['password'] ?? '';
    
    // 验证密码 (在实际应用中应使用更安全的方式存储和验证密码)
    $adminPassword = 'admin123'; // 在实际应用中，这应该存储在配置文件或数据库中，并使用哈希方式存储
    
    if ($password === $adminPassword) {
        $_SESSION['admin_logged_in'] = true;
        $isLoggedIn = true;
    } else {
        $error = '密码错误，请重试';
    }
}

// 处理退出登录
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}

// 处理隐藏/显示评价和评论操作
if ($isLoggedIn && isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'hide') {
        updateReviewVisibility($id, 0);
    } elseif ($action === 'show') {
        updateReviewVisibility($id, 1);
    } elseif ($action === 'delete_review') {
        softDeleteReview($id);
        // 重定向以避免刷新页面时重复删除
        header('Location: index.php' . (isset($_GET['show_deleted']) ? '?show_deleted=1' : ''));
        exit;
    } elseif ($action === 'restore_review') {
        restoreReview($id);
        // 重定向以避免刷新页面时重复操作
        header('Location: index.php' . (isset($_GET['show_deleted']) ? '?show_deleted=1' : ''));
        exit;
    } elseif ($action === 'hard_delete_review') {
        hardDeleteReview($id);
        // 重定向以避免刷新页面时重复删除
        header('Location: index.php' . (isset($_GET['show_deleted']) ? '?show_deleted=1' : ''));
        exit;
    } elseif ($action === 'delete_comment' && isset($_GET['comment_id'])) {
        $comment_id = (int)$_GET['comment_id'];
        softDeleteComment($comment_id);
        // 重定向以避免刷新页面时重复删除
        header('Location: index.php?view_comments=' . $id . (isset($_GET['show_deleted']) ? '&show_deleted=1' : ''));
        exit;
    } elseif ($action === 'restore_comment' && isset($_GET['comment_id'])) {
        $comment_id = (int)$_GET['comment_id'];
        restoreComment($comment_id);
        // 重定向以避免刷新页面时重复操作
        header('Location: index.php?view_comments=' . $id . (isset($_GET['show_deleted']) ? '&show_deleted=1' : ''));
        exit;
    } elseif ($action === 'hard_delete_comment' && isset($_GET['comment_id'])) {
        $comment_id = (int)$_GET['comment_id'];
        hardDeleteComment($comment_id);
        // 重定向以避免刷新页面时重复删除
        header('Location: index.php?view_comments=' . $id . (isset($_GET['show_deleted']) ? '&show_deleted=1' : ''));
        exit;
    }
}

$pageTitle = "后台管理";
include '../includes/header.php';

// 如果已登录，获取所有评价（包括隐藏的）
$reviews = [];
$comments = [];
$viewingComments = false;
$currentReviewId = 0;
$showDeleted = false;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$perPage = 20;

if ($isLoggedIn) {
    if (isset($_GET['view_comments'])) {
        $currentReviewId = (int)$_GET['view_comments'];
        $showDeleted = isset($_GET['show_deleted']) && $_GET['show_deleted'] == 1;
        $comments = getAllCommentsByReviewId($currentReviewId, $showDeleted);
        $viewingComments = true;
    } else {
        $showDeleted = isset($_GET['show_deleted']) && $_GET['show_deleted'] == 1;
        $result = getAllReviews(true, $showDeleted, $page, $perPage);
        $reviews = $result['reviews'];
        $totalPages = $result['totalPages'];
        $total = $result['total'];
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">后台管理</h4>
                </div>
                <div class="card-body">
                    <?php if (!$isLoggedIn): ?>
                        <!-- 登录表单 -->
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="password" class="form-label">管理员密码</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary">登录</button>
                        </form>
                    <?php else: ?>
                        <!-- 管理员已登录 -->
                        <div class="d-flex justify-content-between mb-4">
                            <?php if ($viewingComments): ?>
                                <h5>评论管理 - 评价 #<?php echo $currentReviewId; ?></h5>
                                <div>
                                    <?php if ($showDeleted): ?>
                                        <a href="?view_comments=<?php echo $currentReviewId; ?>" class="btn btn-sm btn-info">隐藏已删除评论</a>
                                    <?php else: ?>
                                        <a href="?view_comments=<?php echo $currentReviewId; ?>&show_deleted=1" class="btn btn-sm btn-info">显示已删除评论</a>
                                    <?php endif; ?>
                                    <a href="index.php<?php echo $showDeleted ? '?show_deleted=1' : ''; ?>" class="btn btn-sm btn-secondary">返回评价列表</a>
                                    <a href="?action=logout" class="btn btn-sm btn-danger">退出登录</a>
                                </div>
                            <?php else: ?>
                                <h5>评价管理</h5>
                                <div>
                                    <?php if ($showDeleted): ?>
                                        <a href="index.php" class="btn btn-sm btn-info">隐藏已删除评价</a>
                                    <?php else: ?>
                                        <a href="?show_deleted=1" class="btn btn-sm btn-info">显示已删除评价</a>
                                    <?php endif; ?>
                                    <a href="?action=logout" class="btn btn-sm btn-danger">退出登录</a>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($viewingComments): ?>
                            <!-- 评论列表 -->
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>内容</th>
                                        <th>创建时间</th>
                                        <th>状态</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($comments as $comment): ?>
                                        <tr class="<?php echo $comment['is_deleted'] ? 'table-secondary' : ''; ?>">
                                            <td><?php echo $comment['id']; ?></td>
                                            <td><?php echo htmlspecialchars(substr($comment['content'], 0, 100)) . (strlen($comment['content']) > 100 ? '...' : ''); ?></td>
                                            <td><?php echo formatDateTime($comment['created_at']); ?></td>
                                            <td><?php echo $comment['is_deleted'] ? '已删除' : '正常'; ?></td>
                                            <td>
                                                <?php if ($comment['is_deleted']): ?>
                                                    <a href="?action=restore_comment&id=<?php echo $currentReviewId; ?>&comment_id=<?php echo $comment['id']; ?>&show_deleted=<?php echo $showDeleted ? '1' : '0'; ?>" 
                                                       class="btn btn-sm btn-success"
                                                       onclick="return confirm('确定要恢复这条评论吗？')">恢复</a>
                                                    <a href="?action=hard_delete_comment&id=<?php echo $currentReviewId; ?>&comment_id=<?php echo $comment['id']; ?>&show_deleted=<?php echo $showDeleted ? '1' : '0'; ?>" 
                                                       class="btn btn-sm btn-danger"
                                                       onclick="return confirm('确定要永久删除这条评论吗？此操作不可恢复！')">永久删除</a>
                                                <?php else: ?>
                                                    <a href="?action=delete_comment&id=<?php echo $currentReviewId; ?>&comment_id=<?php echo $comment['id']; ?>&show_deleted=<?php echo $showDeleted ? '1' : '0'; ?>" 
                                                       class="btn btn-sm btn-warning"
                                                       onclick="return confirm('确定要删除这条评论吗？')">删除</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($comments)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center">暂无评论数据</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <!-- 评价列表 -->
                            <?php if (!empty($reviews)): ?>
                                <div class="mb-3">
                                    <p class="text-muted">共 <?php echo $total; ?> 条评价，当前第 <?php echo $page; ?>/<?php echo $totalPages; ?> 页</p>
                                </div>
                            <?php endif; ?>
                            
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>商家名称</th>
                                        <th>评价</th>
                                        <th>内容预览</th>
                                        <th>赞同/反对</th>
                                        <th>评论数</th>
                                        <th>创建时间</th>
                                        <th>状态</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reviews as $review): ?>
                                        <tr class="<?php echo !$review['is_visible'] ? 'table-secondary' : ($review['is_deleted'] ? 'table-danger' : ''); ?>">
                                            <td><?php echo $review['id']; ?></td>
                                            <td><?php echo htmlspecialchars($review['restaurant_name']); ?></td>
                                            <td>
                                                <span class="badge <?php echo $review['rating'] == '好吃' ? 'bg-success' : 'bg-danger'; ?>">
                                                    <?php echo htmlspecialchars($review['rating']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars(substr($review['content'], 0, 30)) . (strlen($review['content']) > 30 ? '...' : ''); ?></td>
                                            <td><?php echo $review['agree_count']; ?> / <?php echo $review['disagree_count']; ?></td>
                                            <td>
                                                <?php if ($review['comment_count'] > 0): ?>
                                                    <a href="?view_comments=<?php echo $review['id']; ?><?php echo $showDeleted ? '&show_deleted=1' : ''; ?>"><?php echo $review['comment_count']; ?></a>
                                                <?php else: ?>
                                                    <?php echo $review['comment_count']; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo formatDateTime($review['created_at']); ?></td>
                                            <td>
                                                <?php 
                                                if ($review['is_deleted']) {
                                                    echo '已删除';
                                                } else {
                                                    echo $review['is_visible'] ? '显示' : '隐藏';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <?php if (!$review['is_deleted']): ?>
                                                        <a href="../view_review.php?id=<?php echo $review['id']; ?>" class="btn btn-sm btn-info" target="_blank">查看</a>
                                                        <?php if ($review['is_visible']): ?>
                                                            <a href="?action=hide&id=<?php echo $review['id']; ?><?php echo $showDeleted ? '&show_deleted=1' : ''; ?><?php echo $page > 1 ? '&page=' . $page : ''; ?>" class="btn btn-sm btn-warning" 
                                                               onclick="return confirm('确定要隐藏这条评价吗？')">隐藏</a>
                                                        <?php else: ?>
                                                            <a href="?action=show&id=<?php echo $review['id']; ?><?php echo $showDeleted ? '&show_deleted=1' : ''; ?><?php echo $page > 1 ? '&page=' . $page : ''; ?>" class="btn btn-sm btn-success">显示</a>
                                                        <?php endif; ?>
                                                        <a href="?action=delete_review&id=<?php echo $review['id']; ?><?php echo $showDeleted ? '&show_deleted=1' : ''; ?><?php echo $page > 1 ? '&page=' . $page : ''; ?>" class="btn btn-sm btn-danger" 
                                                           onclick="return confirm('确定要删除这条评价吗？')">删除</a>
                                                    <?php else: ?>
                                                        <a href="?action=restore_review&id=<?php echo $review['id']; ?><?php echo $showDeleted ? '&show_deleted=1' : ''; ?><?php echo $page > 1 ? '&page=' . $page : ''; ?>" class="btn btn-sm btn-success" 
                                                           onclick="return confirm('确定要恢复这条评价吗？')">恢复</a>
                                                        <a href="?action=hard_delete_review&id=<?php echo $review['id']; ?><?php echo $showDeleted ? '&show_deleted=1' : ''; ?><?php echo $page > 1 ? '&page=' . $page : ''; ?>" class="btn btn-sm btn-danger" 
                                                           onclick="return confirm('确定要永久删除这条评价吗？此操作不可恢复！')">永久删除</a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($reviews)): ?>
                                        <tr>
                                            <td colspan="9" class="text-center">暂无评价数据</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            
                            <!-- 分页导航 -->
                            <?php if (!empty($reviews) && $totalPages > 1): ?>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=1<?php echo $showDeleted ? '&show_deleted=1' : ''; ?>">首页</a>
                                            </li>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $showDeleted ? '&show_deleted=1' : ''; ?>">上一页</a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php
                                        // 显示页码，最多显示5个
                                        $startPage = max(1, $page - 2);
                                        $endPage = min($totalPages, $startPage + 4);
                                        if ($endPage - $startPage < 4) {
                                            $startPage = max(1, $endPage - 4);
                                        }
                                        
                                        for ($i = $startPage; $i <= $endPage; $i++):
                                        ?>
                                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo $showDeleted ? '&show_deleted=1' : ''; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $showDeleted ? '&show_deleted=1' : ''; ?>">下一页</a>
                                            </li>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $totalPages; ?><?php echo $showDeleted ? '&show_deleted=1' : ''; ?>">末页</a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 