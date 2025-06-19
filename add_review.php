<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
$pageTitle = "添加评价";
include 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $restaurant_name = trim($_POST['restaurant_name'] ?? '');
    $rating = $_POST['rating'] ?? '';
    $content = trim($_POST['content'] ?? '');
    
    if (empty($restaurant_name)) {
        $error = '请输入商家名称';
    } elseif (empty($rating)) {
        $error = '请选择评价类型';
    } elseif (empty($content)) {
        $error = '请输入评价内容';
    } else {
        $result = addReview($restaurant_name, $rating, $content);
        if ($result) {
            $success = '评价添加成功！';
            // 清空表单
            $restaurant_name = $content = '';
            $rating = '';
        } else {
            $error = '评价添加失败，请稍后再试';
        }
    }
}
?>

<div class="container my-4">
    <div class="row">
        <div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">添加新评价</h4>
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
                            <div class="mt-2">
                                <a href="index.php" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-arrow-left me-1"></i> 返回首页
                                </a>
                                <a href="add_review.php" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-plus-circle me-1"></i> 继续添加
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="restaurant_name" class="form-label">商家名称</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-shop text-primary"></i>
                                </span>
                                <input type="text" class="form-control border-start-0" id="restaurant_name" name="restaurant_name" 
                                       placeholder="请输入商家名和外卖名..."
                                       value="<?php echo isset($restaurant_name) ? htmlspecialchars($restaurant_name) : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">评价类型</label>
                            <div class="d-flex">
                                <div class="form-check form-check-inline me-4">
                                    <input class="form-check-input" type="radio" name="rating" id="rating_good" value="好吃" 
                                           <?php echo (isset($rating) && $rating === '好吃') ? 'checked' : ''; ?> required>
                                    <label class="form-check-label" for="rating_good">
                                        <i class="bi bi-emoji-smile text-success me-1"></i> 好吃
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="rating" id="rating_bad" value="难吃" 
                                           <?php echo (isset($rating) && $rating === '难吃') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="rating_bad">
                                        <i class="bi bi-emoji-frown text-danger me-1"></i> 难吃
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="content" class="form-label">评价内容</label>
                            <textarea class="form-control" id="content" name="content" rows="5" 
                                      placeholder="锐评..."
                                      required><?php echo isset($content) ? htmlspecialchars($content) : ''; ?></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> 返回
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send me-1"></i> 提交评价
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 