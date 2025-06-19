<?php
// 获取评价列表（带分页）
function getReviews($search = '', $rating = '', $page = 1, $perPage = 20, $sort = 'date') {
    $db = getDbConnection();
    
    // 计算偏移量
    $offset = ($page - 1) * $perPage;
    
    // 构建查询条件
    $conditions = ['is_visible = 1', 'is_deleted = 0'];
    $params = [];
    
    if (!empty($search)) {
        $conditions[] = 'restaurant_name LIKE ?';
        $params[] = '%' . $search . '%';
    }
    
    if (!empty($rating)) {
        $conditions[] = 'rating = ?';
        $params[] = $rating;
    }
    
    $whereClause = implode(' AND ', $conditions);
    
    // 获取总记录数
    $countQuery = 'SELECT COUNT(*) as total FROM reviews WHERE ' . $whereClause;
    $stmt = $db->prepare($countQuery);
    $stmt->execute($params);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // 设置排序方式
    $orderBy = '';
    switch ($sort) {
        case 'likes':
            $orderBy = 'ORDER BY agree_count DESC, created_at DESC';
            break;
        case 'comments':
            $orderBy = 'ORDER BY comment_count DESC, created_at DESC';
            break;
        case 'date':
        default:
            $orderBy = 'ORDER BY created_at DESC';
            break;
    }
    
    // 获取当前页数据
    $query = 'SELECT r.*, 
              (SELECT COUNT(*) FROM comments WHERE review_id = r.id AND is_deleted = 0) AS comment_count 
              FROM reviews r 
              WHERE ' . $whereClause . '
              ' . $orderBy . '
              LIMIT ? OFFSET ?';
    
    $params[] = $perPage;
    $params[] = $offset;
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 计算总页数
    $totalPages = ceil($total / $perPage);
    
    return [
        'reviews' => $reviews,
        'total' => $total,
        'page' => $page,
        'perPage' => $perPage,
        'totalPages' => $totalPages
    ];
}

// 随机获取一个好评的餐厅
function getRandomGoodRestaurant() {
    $db = getDbConnection();
    
    $query = 'SELECT * FROM reviews 
              WHERE rating = ? AND is_visible = 1 AND is_deleted = 0 
              ORDER BY RANDOM() LIMIT 1';
    
    $stmt = $db->prepare($query);
    $stmt->execute(['好吃']);
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// 获取所有评价（包括隐藏和已删除的，用于管理后台）
function getAllReviews($includeHidden = false, $includeDeleted = false, $page = 1, $perPage = 20) {
    $db = getDbConnection();
    
    // 计算偏移量
    $offset = ($page - 1) * $perPage;
    
    // 构建查询条件
    $conditions = ['1=1'];
    
    if (!$includeHidden) {
        $conditions[] = 'is_visible = 1';
    }
    
    if (!$includeDeleted) {
        $conditions[] = 'is_deleted = 0';
    }
    
    $whereClause = implode(' AND ', $conditions);
    
    // 获取总记录数
    $countQuery = 'SELECT COUNT(*) as total FROM reviews WHERE ' . $whereClause;
    $stmt = $db->prepare($countQuery);
    $stmt->execute();
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // 获取当前页数据
    $query = 'SELECT r.*, 
              (SELECT COUNT(*) FROM comments WHERE review_id = r.id AND is_deleted = 0) AS comment_count 
              FROM reviews r 
              WHERE ' . $whereClause . '
              ORDER BY created_at DESC
              LIMIT ? OFFSET ?';
    
    $stmt = $db->prepare($query);
    $stmt->execute([$perPage, $offset]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 计算总页数
    $totalPages = ceil($total / $perPage);
    
    return [
        'reviews' => $reviews,
        'total' => $total,
        'page' => $page,
        'perPage' => $perPage,
        'totalPages' => $totalPages
    ];
}

// 根据ID获取评价
function getReviewById($id) {
    $db = getDbConnection();
    
    $query = 'SELECT r.*, 
              (SELECT COUNT(*) FROM comments WHERE review_id = r.id AND is_deleted = 0) AS comment_count 
              FROM reviews r 
              WHERE id = ? AND is_visible = 1 AND is_deleted = 0';
    
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// 添加评价
function addReview($restaurant_name, $rating, $content) {
    $db = getDbConnection();
    
    $query = 'INSERT INTO reviews (restaurant_name, rating, content) VALUES (?, ?, ?)';
    
    $stmt = $db->prepare($query);
    return $stmt->execute([$restaurant_name, $rating, $content]);
}

// 更新评价可见性
function updateReviewVisibility($id, $isVisible) {
    $db = getDbConnection();
    
    $query = 'UPDATE reviews SET is_visible = ? WHERE id = ?';
    
    $stmt = $db->prepare($query);
    return $stmt->execute([$isVisible, $id]);
}

// 软删除评价
function softDeleteReview($id) {
    $db = getDbConnection();
    
    $query = 'UPDATE reviews SET is_deleted = 1 WHERE id = ?';
    
    $stmt = $db->prepare($query);
    return $stmt->execute([$id]);
}

// 恢复已删除的评价
function restoreReview($id) {
    $db = getDbConnection();
    
    $query = 'UPDATE reviews SET is_deleted = 0 WHERE id = ?';
    
    $stmt = $db->prepare($query);
    return $stmt->execute([$id]);
}

// 硬删除评价（真正从数据库中删除）
function hardDeleteReview($id) {
    $db = getDbConnection();
    
    $query = 'DELETE FROM reviews WHERE id = ?';
    
    $stmt = $db->prepare($query);
    return $stmt->execute([$id]);
}

// 获取评论列表
function getCommentsByReviewId($review_id) {
    $db = getDbConnection();
    
    $query = 'SELECT * FROM comments WHERE review_id = ? AND is_deleted = 0 ORDER BY created_at ASC';
    
    $stmt = $db->prepare($query);
    $stmt->execute([$review_id]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 获取所有评论（包括已删除的，用于管理后台）
function getAllCommentsByReviewId($review_id, $includeDeleted = false) {
    $db = getDbConnection();
    
    $query = 'SELECT * FROM comments WHERE review_id = ?';
    
    if (!$includeDeleted) {
        $query .= ' AND is_deleted = 0';
    }
    
    $query .= ' ORDER BY created_at ASC';
    
    $stmt = $db->prepare($query);
    $stmt->execute([$review_id]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 添加评论
function addComment($review_id, $content) {
    $db = getDbConnection();
    
    $query = 'INSERT INTO comments (review_id, content) VALUES (?, ?)';
    
    $stmt = $db->prepare($query);
    return $stmt->execute([$review_id, $content]);
}

// 软删除评论
function softDeleteComment($comment_id) {
    $db = getDbConnection();
    
    $query = 'UPDATE comments SET is_deleted = 1 WHERE id = ?';
    
    $stmt = $db->prepare($query);
    return $stmt->execute([$comment_id]);
}

// 恢复已删除的评论
function restoreComment($comment_id) {
    $db = getDbConnection();
    
    $query = 'UPDATE comments SET is_deleted = 0 WHERE id = ?';
    
    $stmt = $db->prepare($query);
    return $stmt->execute([$comment_id]);
}

// 硬删除评论（真正从数据库中删除）
function hardDeleteComment($comment_id) {
    $db = getDbConnection();
    
    $query = 'DELETE FROM comments WHERE id = ?';
    
    $stmt = $db->prepare($query);
    return $stmt->execute([$comment_id]);
}

// 检查用户是否已经投票
function hasVoted($review_id, $voter_id) {
    $db = getDbConnection();
    
    $query = 'SELECT id FROM votes WHERE review_id = ? AND voter_id = ?';
    
    $stmt = $db->prepare($query);
    $stmt->execute([$review_id, $voter_id]);
    
    return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
}

// 获取当前用户的投票状态
function getCurrentVote($review_id, $voter_id) {
    $db = getDbConnection();
    
    $query = 'SELECT * FROM votes WHERE review_id = ? AND voter_id = ?';
    
    $stmt = $db->prepare($query);
    $stmt->execute([$review_id, $voter_id]);
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// 添加投票
function addVote($review_id, $voter_id, $vote_type) {
    $db = getDbConnection();
    
    try {
        $db->beginTransaction();
        
        // 添加投票记录
        $query = 'INSERT INTO votes (review_id, voter_id, vote_type) VALUES (?, ?, ?)';
        $stmt = $db->prepare($query);
        $stmt->execute([$review_id, $voter_id, $vote_type]);
        
        // 更新评价的投票计数
        $column = $vote_type === 'agree' ? 'agree_count' : 'disagree_count';
        $query = "UPDATE reviews SET {$column} = {$column} + 1 WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$review_id]);
        
        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollBack();
        return false;
    }
}

// 更新投票
function updateVote($review_id, $voter_id, $vote_type) {
    $db = getDbConnection();
    
    try {
        $db->beginTransaction();
        
        // 获取当前投票类型
        $query = 'SELECT vote_type FROM votes WHERE review_id = ? AND voter_id = ?';
        $stmt = $db->prepare($query);
        $stmt->execute([$review_id, $voter_id]);
        $current_vote = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // 如果投票类型没变，则不做任何操作
        if ($current_vote && $current_vote['vote_type'] === $vote_type) {
            $db->commit();
            return true;
        }
        
        // 更新投票记录
        $query = 'UPDATE votes SET vote_type = ? WHERE review_id = ? AND voter_id = ?';
        $stmt = $db->prepare($query);
        $stmt->execute([$vote_type, $review_id, $voter_id]);
        
        // 更新评价的投票计数
        if ($current_vote) {
            // 减少原来类型的计数
            $old_column = $current_vote['vote_type'] === 'agree' ? 'agree_count' : 'disagree_count';
            $query = "UPDATE reviews SET {$old_column} = {$old_column} - 1 WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$review_id]);
        }
        
        // 增加新类型的计数
        $new_column = $vote_type === 'agree' ? 'agree_count' : 'disagree_count';
        $query = "UPDATE reviews SET {$new_column} = {$new_column} + 1 WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$review_id]);
        
        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollBack();
        return false;
    }
}

// 移除投票
function removeVote($review_id, $voter_id) {
    $db = getDbConnection();
    
    try {
        $db->beginTransaction();
        
        // 获取当前投票类型
        $query = 'SELECT vote_type FROM votes WHERE review_id = ? AND voter_id = ?';
        $stmt = $db->prepare($query);
        $stmt->execute([$review_id, $voter_id]);
        $current_vote = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$current_vote) {
            $db->commit();
            return true;
        }
        
        // 减少对应类型的计数
        $column = $current_vote['vote_type'] === 'agree' ? 'agree_count' : 'disagree_count';
        $query = "UPDATE reviews SET {$column} = CASE WHEN {$column} > 0 THEN {$column} - 1 ELSE 0 END WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$review_id]);
        
        // 删除投票记录
        $query = 'DELETE FROM votes WHERE review_id = ? AND voter_id = ?';
        $stmt = $db->prepare($query);
        $stmt->execute([$review_id, $voter_id]);
        
        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollBack();
        return false;
    }
}

// 获取投票计数
function getVoteCount($review_id, $vote_type) {
    $db = getDbConnection();
    
    $column = $vote_type === 'agree' ? 'agree_count' : 'disagree_count';
    $query = "SELECT {$column} FROM reviews WHERE id = ?";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$review_id]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result[$column] : 0;
}

// 将UTC时间转换为北京时间并格式化
function formatDateTime($dateTime, $format = 'Y-m-d H:i') {
    $utcDateTime = new DateTime($dateTime, new DateTimeZone('UTC'));
    $utcDateTime->setTimezone(new DateTimeZone('Asia/Shanghai'));
    return $utcDateTime->format($format);
}

// 记录访问者
function recordVisitor() {
    $db = getDbConnection();
    
    // 获取访问者信息
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // 生成访问者ID (基于IP和UA)
    $visitor_id = md5($ip . $user_agent);
    
    try {
        $db->beginTransaction();
        
        // 检查是否已存在该访问者
        $query = 'SELECT id, visit_count FROM visitors WHERE visitor_id = ?';
        $stmt = $db->prepare($query);
        $stmt->execute([$visitor_id]);
        $visitor = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($visitor) {
            // 更新现有访问者的最后访问时间和访问次数
            $query = 'UPDATE visitors SET last_visit_at = CURRENT_TIMESTAMP, visit_count = visit_count + 1 WHERE visitor_id = ?';
            $stmt = $db->prepare($query);
            $stmt->execute([$visitor_id]);
        } else {
            // 添加新访问者
            $query = 'INSERT INTO visitors (visitor_id, ip_address, user_agent) VALUES (?, ?, ?)';
            $stmt = $db->prepare($query);
            $stmt->execute([$visitor_id, $ip, $user_agent]);
            
            // 更新总访问人数
            $query = 'UPDATE site_stats SET total_visitors = total_visitors + 1, last_updated = CURRENT_TIMESTAMP WHERE id = 1';
            $stmt = $db->prepare($query);
            $stmt->execute();
        }
        
        // 更新在线用户
        $query = 'INSERT OR REPLACE INTO online_users (visitor_id, last_activity) VALUES (?, CURRENT_TIMESTAMP)';
        $stmt = $db->prepare($query);
        $stmt->execute([$visitor_id]);
        
        $db->commit();
        return $visitor_id;
    } catch (Exception $e) {
        $db->rollBack();
        return false;
    }
}

// 清理过期的在线用户（15分钟无活动视为离线）
function cleanupOnlineUsers() {
    $db = getDbConnection();
    
    $query = 'DELETE FROM online_users WHERE datetime(last_activity) < datetime("now", "-15 minutes")';
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    return $stmt->rowCount(); // 返回清理的用户数量
}

// 获取网站统计数据
function getSiteStats() {
    $db = getDbConnection();
    
    // 清理过期在线用户
    cleanupOnlineUsers();
    
    // 获取总访问人数
    $query = 'SELECT total_visitors FROM site_stats WHERE id = 1';
    $stmt = $db->prepare($query);
    $stmt->execute();
    $total_visitors = $stmt->fetch(PDO::FETCH_ASSOC)['total_visitors'];
    
    // 获取当前在线人数
    $query = 'SELECT COUNT(*) as online_count FROM online_users';
    $stmt = $db->prepare($query);
    $stmt->execute();
    $online_count = $stmt->fetch(PDO::FETCH_ASSOC)['online_count'];
    
    return [
        'total_visitors' => $total_visitors,
        'online_count' => $online_count
    ];
} 