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

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">添加新评价</h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="restaurant_name" class="form-label">商家名称</label>
                            <input type="text" class="form-control" id="restaurant_name" name="restaurant_name" 
                                   value="<?php echo isset($restaurant_name) ? htmlspecialchars($restaurant_name) : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">评价</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="rating" id="rating_good" value="好吃" 
                                       <?php echo (isset($rating) && $rating === '好吃') ? 'checked' : ''; ?> required>
                                <label class="form-check-label" for="rating_good">
                                    好吃 👍
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="rating" id="rating_bad" value="难吃" 
                                       <?php echo (isset($rating) && $rating === '难吃') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="rating_bad">
                                    难吃 👎
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="content" class="form-label">评价内容</label>
                            <textarea class="form-control" id="content" name="content" rows="5" required><?php echo isset($content) ? htmlspecialchars($content) : ''; ?></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="index.php" class="btn btn-secondary">返回</a>
                            <button type="submit" class="btn btn-primary">提交评价</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 