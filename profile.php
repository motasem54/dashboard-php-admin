<?php
require_once 'config/init.php';
require_once 'includes/auth.php';
require_once 'includes/logger.php';

requireAuth();

$currentUser = getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = 'جميع الحقول مطلوبة';
    } elseif (strlen($newPassword) < 6) {
        $error = 'كلمة المرور الجديدة يجب أن تكون 6 أحرف على الأقل';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'كلمة المرور الجديدة وتاكيدها غير متطابقتين';
    } else {
        // Verify current password
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$currentUser['id']]);
        $user = $stmt->fetch();
        
        if (password_verify($currentPassword, $user['password'])) {
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $currentUser['id']]);
            
            logActivity($currentUser['id'], 'PASSWORD_CHANGE', 'تم تغيير كلمة المرور');
            $success = 'تم تغيير كلمة المرور بنجاح';
        } else {
            $error = 'كلمة المرور الحالية غير صحيحة';
        }
    }
}

// Get user info
$conn = getConnection();
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$currentUser['id']]);
$userInfo = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الملف الشخصي - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div>
                <h1><?= APP_NAME ?></h1>
                <p>الملف الشخصي</p>
            </div>
            <div style="display: flex; gap: 1rem;">
                <a href="/dashboard.php" class="btn btn-secondary">← العودة للوحة</a>
                <a href="/logout.php" class="btn btn-secondary">تسجيل الخروج</a>
            </div>
        </div>
    </header>

    <main class="container">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; max-width: 1200px; margin: 0 auto;">
            <!-- User Info Card -->
            <div class="card">
                <h3 class="card-title">👤 معلومات الحساب</h3>
                
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div>
                        <label style="color: var(--text-secondary); font-size: 0.85rem;">اسم المستخدم</label>
                        <p style="font-size: 1.1rem; font-weight: 600; margin-top: 0.25rem;"><?= htmlspecialchars($userInfo['username']) ?></p>
                    </div>
                    
                    <div>
                        <label style="color: var(--text-secondary); font-size: 0.85rem;">البريد الإلكتروني</label>
                        <p style="font-size: 1.1rem; font-weight: 600; margin-top: 0.25rem;"><?= htmlspecialchars($userInfo['email']) ?></p>
                    </div>
                    
                    <div>
                        <label style="color: var(--text-secondary); font-size: 0.85rem;">الدور</label>
                        <p style="margin-top: 0.25rem;">
                            <span class="badge <?= $userInfo['role'] === 'admin' ? 'badge-danger' : 'badge-info' ?>">
                                <?= $userInfo['role'] === 'admin' ? 'مدير' : 'مستخدم' ?>
                            </span>
                        </p>
                    </div>
                    
                    <div>
                        <label style="color: var(--text-secondary); font-size: 0.85rem;">تاريخ الإنشاء</label>
                        <p style="font-size: 1rem; margin-top: 0.25rem;"><?= date('Y-m-d H:i', strtotime($userInfo['created_at'])) ?></p>
                    </div>
                    
                    <div>
                        <label style="color: var(--text-secondary); font-size: 0.85rem;">آخر تحديث</label>
                        <p style="font-size: 1rem; margin-top: 0.25rem;"><?= date('Y-m-d H:i', strtotime($userInfo['updated_at'])) ?></p>
                    </div>
                </div>
            </div>

            <!-- Change Password Card -->
            <div class="card">
                <h3 class="card-title">🔐 تغيير كلمة المرور</h3>
                
                <?php if ($error): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="current_password">كلمة المرور الحالية *</label>
                        <input 
                            type="password" 
                            id="current_password" 
                            name="current_password" 
                            class="form-control" 
                            placeholder="أدخل كلمة المرور الحالية"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="new_password">كلمة المرور الجديدة *</label>
                        <input 
                            type="password" 
                            id="new_password" 
                            name="new_password" 
                            class="form-control" 
                            placeholder="أدخل كلمة المرور الجديدة (6 أحرف على الأقل)"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">تأكيد كلمة المرور الجديدة *</label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            class="form-control" 
                            placeholder="أعد إدخال كلمة المرور الجديدة"
                            required
                        >
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        💾 حفظ كلمة المرور الجديدة
                    </button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
