<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
$pageTitle = "西电杭美食助手";
include 'includes/header.php';

// 获取用户UA和IP生成唯一标识符
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$user_ip = $_SERVER['REMOTE_ADDR'] ?? '';
$voter_id = md5($user_agent . $user_ip);

// 获取当前页码
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// 获取搜索关键词
$search = isset($_GET['search']) ? $_GET['search'] : '';

// 获取评价类型过滤
$rating = isset($_GET['rating']) ? $_GET['rating'] : '';
if ($rating !== '好吃' && $rating !== '难吃') {
    $rating = '';
}

// 获取排序方式
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'date';
if (!in_array($sort, ['date', 'likes', 'comments'])) {
    $sort = 'date';
}

// 每页显示数量
$perPage = 20;

// 获取评价列表
$result = getDineInReviews($search, $rating, $page, $perPage, $sort);
$reviews = $result['reviews'];
$totalPages = $result['totalPages'];
$total = $result['total'];

// 处理"今天吃什么"功能
$randomRestaurant = null;
if (isset($_GET['random'])) {
    $randomRestaurant = getRandomGoodDineInRestaurant();
}

// 生成排序URL参数
function getSortUrl($sort_type) {
    global $search, $rating, $page;
    $params = [];
    
    if (!empty($search)) {
        $params[] = 'search=' . urlencode($search);
    }
    
    if (!empty($rating)) {
        $params[] = 'rating=' . urlencode($rating);
    }
    
    if ($page > 1) {
        $params[] = 'page=' . $page;
    }
    
    $params[] = 'sort=' . $sort_type;
    
    return 'dine_in.php?' . implode('&', $params);
}

// 生成分页URL参数
function getPaginationUrl($page_num) {
    global $search, $rating, $sort;
    $params = ['page=' . $page_num];
    
    if (!empty($search)) {
        $params[] = 'search=' . urlencode($search);
    }
    
    if (!empty($rating)) {
        $params[] = 'rating=' . urlencode($rating);
    }
    
    if (!empty($sort) && $sort !== 'date') {
        $params[] = 'sort=' . $sort;
    }
    
    return 'dine_in.php?' . implode('&', $params);
}

// 生成分类选项卡URL
function getTabUrl($tab_rating) {
    global $search, $sort;
    $params = [];
    
    if (!empty($tab_rating)) {
        $params[] = 'rating=' . urlencode($tab_rating);
    }
    
    if (!empty($search)) {
        $params[] = 'search=' . urlencode($search);
    }
    
    if (!empty($sort) && $sort !== 'date') {
        $params[] = 'sort=' . $sort;
    }
    
    return 'dine_in.php' . (empty($params) ? '' : '?' . implode('&', $params));
}

// 生成评价详情URL
function getReviewDetailUrl($review_id) {
    global $search, $rating, $sort, $page;
    $params = ['id=' . $review_id];
    
    if (!empty($search)) {
        $params[] = 'search=' . urlencode($search);
    }
    
    if (!empty($rating)) {
        $params[] = 'rating=' . urlencode($rating);
    }
    
    if (!empty($sort) && $sort !== 'date') {
        $params[] = 'sort=' . $sort;
    }
    
    if ($page > 1) {
        $params[] = 'page=' . $page;
    }
    
    return 'view_dine_in_review.php?' . implode('&', $params);
}
?>

<div class="container mt-4">
    <?php if ($randomRestaurant): ?>
        <div class="row mb-4">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"></i>今天去哪聚餐？就去这家！</h4>
                    </div>
                    <div class="card-body">
                        <h3 class="text-center"><?php echo htmlspecialchars($randomRestaurant['restaurant_name']); ?></h3>
                        <p class="text-center"><?php echo nl2br(htmlspecialchars($randomRestaurant['content'])); ?></p>
                        <div class="text-center mt-4">
                            <a href="<?php echo getReviewDetailUrl($randomRestaurant['id']); ?>" class="btn btn-sm btn-primary">
                                <i class="bi bi-eye me-1"></i> 查看详情
                            </a>
                            <a href="<?php echo $search || $rating || $sort !== 'date' ? getTabUrl($rating) : 'dine_in.php'; ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> 返回列表
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row mb-4">
            <div class="col-lg-6 offset-lg-3 col-md-8 offset-md-2">
                <form action="dine_in.php" method="GET" class="search-form">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-primary"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" name="search" placeholder="搜索餐厅..." value="<?php echo htmlspecialchars($search); ?>">
                        <?php if (!empty($rating)): ?>
                            <input type="hidden" name="rating" value="<?php echo htmlspecialchars($rating); ?>">
                        <?php endif; ?>
                        <?php if ($sort !== 'date'): ?>
                            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                        <?php endif; ?>
                        <button class="btn btn-primary" type="submit">搜索</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-12 text-center">
                <div class="d-flex flex-column flex-md-row justify-content-center">
                    <div class="d-flex justify-content-center">
                        <a href="add_dine_in_review.php" class="btn btn-success">
                            <i class="bi bi-plus-circle me-1"></i> 添加聚餐评价
                        </a>
                        <a href="?random=1" class="btn btn-info ms-2">
                            <i class="bi bi-shuffle me-1"></i> 今天去哪聚餐？
                        </a>
                    </div>
                    <div class="mt-2 mt-md-0">
                        <a href="index.php" class="btn btn-outline-primary ms-md-2">
                            <i class="bi bi-box-arrow-right me-1"></i> 切换到外卖评价
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 分类选项卡 -->
        <div class="row">
            <div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
                <div class="d-flex justify-content-between align-items-center">
                    <ul class="nav nav-tabs flex-grow-1">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $rating === '' ? 'active' : ''; ?>" href="<?php echo getTabUrl(''); ?>">全部</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $rating === '好吃' ? 'active' : ''; ?>" href="<?php echo getTabUrl('好吃'); ?>">
                                <i class="bi bi-emoji-smile text-success me-1"></i> 好吃
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $rating === '难吃' ? 'active' : ''; ?>" href="<?php echo getTabUrl('难吃'); ?>">
                                <i class="bi bi-emoji-frown text-danger me-1"></i> 难吃
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- 排序选项 -->
        <div class="row mb-4">
            <div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
                <div class="d-flex justify-content-end">
                    <div class="btn-group">
                        <a href="<?php echo getSortUrl('date'); ?>" class="btn btn-sm <?php echo $sort === 'date' ? 'btn-secondary' : 'btn-outline-secondary'; ?>">
                            <i class="bi bi-clock me-1"></i> 最新
                        </a>
                        <a href="<?php echo getSortUrl('likes'); ?>" class="btn btn-sm <?php echo $sort === 'likes' ? 'btn-secondary' : 'btn-outline-secondary'; ?>">
                            <i class="bi bi-hand-thumbs-up me-1"></i> 点赞最多
                        </a>
                        <a href="<?php echo getSortUrl('comments'); ?>" class="btn btn-sm <?php echo $sort === 'comments' ? 'btn-secondary' : 'btn-outline-secondary'; ?>">
                            <i class="bi bi-chat-dots me-1"></i> 评论最多
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
                <?php if (count($reviews) > 0): ?>
                    <div class="mb-3">
                        <p class="text-muted small">共 <?php echo $total; ?> 条评价，当前第 <?php echo $page; ?>/<?php echo $totalPages; ?> 页</p>
                    </div>
                    <?php foreach ($reviews as $review): ?>
                        <?php 
                        // 检查用户是否已投票
                        $current_vote = getCurrentDineInVote($review['id'], $voter_id);
                        $user_vote_type = $current_vote ? $current_vote['vote_type'] : null;
                        ?>
                        <div class="card mb-3 review-card">
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
                                <p class="card-text"><?php echo nl2br(htmlspecialchars(mb_substr($review['content'], 0, 100) . (mb_strlen($review['content']) > 100 ? '...' : ''))); ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="btn-group">
                                        <a href="<?php echo getReviewDetailUrl($review['id']); ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i> 查看详情
                                        </a>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="bi bi-hand-thumbs-up me-1 <?php echo $user_vote_type === 'agree' ? 'text-primary' : 'text-muted'; ?>"></i>
                                            <span class="text-muted"><?php echo $review['agree_count']; ?></span>
                                        </div>
                                        <div class="me-3">
                                            <i class="bi bi-hand-thumbs-down me-1 <?php echo $user_vote_type === 'disagree' ? 'text-danger' : 'text-muted'; ?>"></i>
                                            <span class="text-muted"><?php echo $review['disagree_count']; ?></span>
                                        </div>
                                        <div>
                                            <i class="bi bi-chat-dots me-1 text-muted"></i>
                                            <span class="text-muted"><?php echo $review['comment_count']; ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-muted small">
                                <i class="bi bi-clock me-1"></i> <?php echo formatDateTime($review['created_at']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <!-- 分页 -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo getPaginationUrl($page - 1); ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php
                                $startPage = max(1, $page - 2);
                                $endPage = min($totalPages, $startPage + 4);
                                if ($endPage - $startPage < 4) {
                                    $startPage = max(1, $endPage - 4);
                                }
                                ?>
                                
                                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="<?php echo getPaginationUrl($i); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo getPaginationUrl($page + 1); ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle me-2"></i> 暂无聚餐评价
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
