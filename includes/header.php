<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : '今天吃什么'; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 图标 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- 自定义样式 -->
    <style>
        body {
            padding-bottom: 2rem;
            background-color: #f8f9fa;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .review-card {
            transition: transform 0.2s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .review-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        @media (max-width: 767.98px) {
            .container {
                padding-left: 10px;
                padding-right: 10px;
            }
        }
        /* 投票按钮样式 */
        .vote-btn.active[data-vote-type="agree"] {
            background-color: #28a745;
            color: white;
            border-color: #28a745;
        }
        .vote-btn.active[data-vote-type="disagree"] {
            background-color: #dc3545;
            color: white;
            border-color: #dc3545;
        }
        /* 网站统计信息样式 */
        .site-stats {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.85);
            padding: 0.25rem 0.5rem;
            background-color: rgba(0, 0, 0, 0.1);
            border-radius: 4px;
            margin-right: 1rem;
        }
        @media (max-width: 991.98px) {
            .site-stats {
                display: none;
            }
        }
    </style>
</head>
<body>
<?php
// 记录访问者并获取统计数据
recordVisitor();
$siteStats = getSiteStats();
?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?php echo getBaseUrl(); ?>index.php">外卖红黑榜</a>
            <div class="site-stats d-none d-lg-block">
                <i class="fas fa-users"></i> 总访问: <?php echo number_format($siteStats['total_visitors']); ?> | 
                <i class="fas fa-user-clock"></i> 在线: <?php echo number_format($siteStats['online_count']); ?>
            </div>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo getBaseUrl(); ?>index.php">首页</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo getBaseUrl(); ?>add_review.php">添加评价</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo getBaseUrl(); ?>admin/index.php">管理后台</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

<?php
// 获取基础URL
function getBaseUrl() {
    $current_path = $_SERVER['PHP_SELF'];
    $path_parts = explode('/', $current_path);
    
    // 如果在admin目录下，返回上一级
    if (in_array('admin', $path_parts)) {
        return '../';
    }
    
    return '';
}
?> 