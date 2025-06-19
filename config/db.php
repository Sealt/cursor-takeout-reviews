<?php
// 数据库文件路径
$db_path = __DIR__ . '/../database/takeout_reviews.db';

// 确保数据库目录存在
$db_dir = dirname($db_path);
if (!is_dir($db_dir)) {
    mkdir($db_dir, 0755, true);
}

// 连接到SQLite数据库
function getDbConnection() {
    global $db_path;
    
    try {
        $db = new PDO('sqlite:' . $db_path);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 启用外键约束
        $db->exec('PRAGMA foreign_keys = ON');
        
        // 初始化数据库表
        initDatabase($db);
        
        return $db;
    } catch (PDOException $e) {
        die('数据库连接失败: ' . $e->getMessage());
    }
}

// 初始化数据库表
function initDatabase($db) {
    // 评价表
    $db->exec('CREATE TABLE IF NOT EXISTS reviews (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        restaurant_name TEXT NOT NULL,
        rating TEXT NOT NULL,
        content TEXT NOT NULL,
        agree_count INTEGER DEFAULT 0,
        disagree_count INTEGER DEFAULT 0,
        is_visible INTEGER DEFAULT 1,
        is_deleted INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )');
    
    // 评论表
    $db->exec('CREATE TABLE IF NOT EXISTS comments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        review_id INTEGER NOT NULL,
        content TEXT NOT NULL,
        is_deleted INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE
    )');
    
    // 投票表
    $db->exec('CREATE TABLE IF NOT EXISTS votes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        review_id INTEGER NOT NULL,
        voter_id TEXT NOT NULL,
        vote_type TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE,
        UNIQUE(review_id, voter_id)
    )');
    
    // 访问统计表
    $db->exec('CREATE TABLE IF NOT EXISTS visitors (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        visitor_id TEXT NOT NULL,
        ip_address TEXT,
        user_agent TEXT,
        first_visit_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        last_visit_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        visit_count INTEGER DEFAULT 1,
        UNIQUE(visitor_id)
    )');
    
    // 在线用户表
    $db->exec('CREATE TABLE IF NOT EXISTS online_users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        visitor_id TEXT NOT NULL,
        last_activity DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(visitor_id)
    )');
    
    // 网站统计表
    $db->exec('CREATE TABLE IF NOT EXISTS site_stats (
        id INTEGER PRIMARY KEY,
        total_visitors INTEGER DEFAULT 0,
        last_updated DATETIME DEFAULT CURRENT_TIMESTAMP
    )');
    
    // 确保网站统计表有一条记录
    $db->exec('INSERT OR IGNORE INTO site_stats (id, total_visitors) VALUES (1, 0)');
    
    // 检查comments表是否已存在is_deleted字段
    try {
        $result = $db->query("PRAGMA table_info(comments)");
        $columns = $result->fetchAll(PDO::FETCH_ASSOC);
        $hasIsDeletedColumn = false;
        
        foreach ($columns as $column) {
            if ($column['name'] === 'is_deleted') {
                $hasIsDeletedColumn = true;
                break;
            }
        }
        
        // 如果不存在is_deleted字段，则添加
        if (!$hasIsDeletedColumn) {
            $db->exec('ALTER TABLE comments ADD COLUMN is_deleted INTEGER DEFAULT 0');
        }
    } catch (PDOException $e) {
        // 忽略错误，继续执行
    }
    
    // 检查reviews表是否已存在is_deleted字段
    try {
        $result = $db->query("PRAGMA table_info(reviews)");
        $columns = $result->fetchAll(PDO::FETCH_ASSOC);
        $hasIsDeletedColumn = false;
        
        foreach ($columns as $column) {
            if ($column['name'] === 'is_deleted') {
                $hasIsDeletedColumn = true;
                break;
            }
        }
        
        // 如果不存在is_deleted字段，则添加
        if (!$hasIsDeletedColumn) {
            $db->exec('ALTER TABLE reviews ADD COLUMN is_deleted INTEGER DEFAULT 0');
        }
    } catch (PDOException $e) {
        // 忽略错误，继续执行
    }
} 