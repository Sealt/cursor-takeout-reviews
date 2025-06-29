<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : '西电杭美食助手'; ?></title>
    <!-- Bootstrap CSS -->
    <link href="<?php echo getBaseUrl(); ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="<?php echo getBaseUrl(); ?>assets/css/bootstrap-icons.css">
    <!-- 自定义样式 -->
    <style>
        :root {
            --primary-color: #3b82f6;
            --primary-dark: #2563eb;
            --secondary-color: #6366f1;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
            --light-color: #f3f4f6;
            --dark-color: #1f2937;
            --border-radius: 0.5rem;
            --box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        body {
            padding-bottom: 2rem;
            background-color: #f9fafb;
            color: #374151;
        }
        .navbar {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            letter-spacing: -0.5px;
        }
        
        .nav-link {
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            border-radius: 0.375rem;
            transition: all 0.2s;
        }
        
        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.15);
        }
        
        .review-card {
            transition: all 0.3s ease;
            border-radius: var(--border-radius);
            border: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }
        
        .review-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--box-shadow);
        }
        
        .card {
            border-radius: var(--border-radius);
            border: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1rem 1rem;
        }
        
        .card-body {
            padding: 1rem;
        }
        
        .card-footer {
            background-color: #fff;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            padding: 0.5rem 1rem;
        }
        
        .btn {
            border-radius: 0.375rem;
            padding: 0.5rem 0.5rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }
        
        .btn-info {
            background-color: var(--info-color);
            border-color: var(--info-color);
            color: white;
        }
        
        .btn-info:hover {
            color: white;
        }
        
        .btn-outline-secondary {
            border-color: #d1d5db;
            color: #4b5563;
        }
        
        .btn-outline-secondary:hover {
            background-color: #f3f4f6;
            color: #1f2937;
            border-color: #d1d5db;
        }
        
        .bg-success {
            background-color: var(--success-color) !important;
        }
        
        .bg-danger {
            background-color: var(--danger-color) !important;
        }
        
        .badge {
            padding: 0.5em 0.75em;
            font-weight: 500;
            border-radius: 0.375rem;
        }
        
        .pagination {
            margin-top: 2rem;
        }
        
        .page-link {
            color: var(--primary-color);
            border-color: #e5e7eb;
            margin: 0 0.25rem;
            border-radius: 0.375rem !important;
        }
        
        .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .form-control {
            border-radius: 0.375rem;
            border-color: #d1d5db;
            padding: 0.625rem 1rem;
        }
        
        .form-control:focus {
            border-color: #d1d5db;
            box-shadow: 0 0 0 0rem;
        }
        h5 {
            font-size: 1rem;
        }
        .alert {
            border-radius: var(--border-radius);
            border: none;
        }
        
        /* 网站统计信息样式 */
        .site-stats {
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.9);
            padding: 0.375rem 0.75rem;
            background-color: rgba(0, 0, 0, 0.1);
            border-radius: 0.375rem;
            margin-right: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .site-stats i {
            font-size: 1rem;
            margin-right: 0.25rem;
        }
        
        .site-stats-item {
            display: flex;
            align-items: center;
        }
        
        @media (max-width: 991.98px) {
            .container {
                padding-left: 15px;
                padding-right: 15px;
            }
        }
        
        /* 投票按钮样式 */
        .vote-btn.active[data-vote-type="agree"] {
            background-color: var(--success-color);
            color: white;
            border-color: var(--success-color);
        }
        
        .vote-btn.active[data-vote-type="disagree"] {
            background-color: var(--danger-color);
            color: white;
            border-color: var(--danger-color);
        }
        
        /* 选项卡样式 */
        .nav-tabs {
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 1.5rem;
        }
        
        .nav-tabs .nav-link {
            border: none;
            color: #6b7280;
            font-weight: 500;
            padding: 0.75rem 1.25rem;
            margin-right: 0.5rem;
            border-radius: 0.375rem 0.375rem 0 0;
        }
        
        .nav-tabs .nav-link:hover {
            border-color: transparent;
            background-color: #f3f4f6;
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            background-color: #fff;
            border-bottom: 2px solid var(--primary-color);
        }
        
        /* 排序按钮组样式 */
        .btn-group .btn {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
<?php
// 记录访问者并获取统计数据
recordVisitor();
$siteStats = getSiteStats();
?>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo getBaseUrl(); ?>index.php">西电杭美食助手</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo getBaseUrl(); ?>index.php">
                            <i class="bi bi-house-door me-1"></i> 首页
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo getBaseUrl(); ?>add_review.php">
                            <i class="bi bi-plus-circle me-1"></i> 添加评价
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo getBaseUrl(); ?>admin/index.php">
                            <i class="bi bi-gear me-1"></i> 管理后台
                        </a>
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