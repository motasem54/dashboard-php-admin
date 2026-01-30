<?php
require_once 'config/init.php';
require_once 'includes/auth.php';
require_once 'includes/users.php';
require_once 'includes/logger.php';

requireAuth();

// Handle user deletion
if (isset($_GET['delete']) && isAdmin()) {
    $userId = (int)$_GET['delete'];
    $currentUser = getCurrentUser();
    
    if ($userId !== $currentUser['id']) {
        $conn = getConnection();
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        
        logActivity($currentUser['id'], 'USER_DELETE', "تم حذف المستخدم رقم: {$userId}");
        header('Location: /users.php?success=deleted');
        exit;
    }
}

$currentUser = getCurrentUser();
$users = getAllUsers();
$successMessage = '';

if (isset($_GET['success'])) {
    if ($_GET['success'] === 'created') {
        $successMessage = 'تم إضافة المستخدم بنجاح';
    } elseif ($_GET['success'] === 'updated') {
        $successMessage = 'تم تحديث المستخدم بنجاح';
    } elseif ($_GET['success'] === 'deleted') {
        $successMessage = 'تم حذف المستخدم بنجاح';
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المستخدمين - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div>
                <h1><?= APP_NAME ?></h1>
                <p>مرحباً <strong><?= htmlspecialchars($currentUser['username']) ?></strong></p>
            </div>
            <div style="display: flex; gap: 1rem;">
                <a href="/dashboard.php" class="btn btn-secondary">← العودة للوحة</a>
                <a href="/logout.php" class="btn btn-secondary">تسجيل الخروج</a>
            </div>
        </div>
    </header>

    <main class="container">
        <?php if ($successMessage): ?>
        <div class="alert alert-success">
            <?= $successMessage ?>
        </div>
        <?php endif; ?>

        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 class="card-title" style="margin-bottom: 0;">إدارة المستخدمين</h3>
                <?php if (isAdmin()): ?>
                <a href="/user-add.php" class="btn btn-primary">➕ إضافة مستخدم جديد</a>
                <?php endif; ?>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>اسم المستخدم</th>
                            <th>البريد الإلكتروني</th>
                            <th>الدور</th>
                            <th>تاريخ الإنشاء</th>
                            <?php if (isAdmin()): ?>
                            <th>الإجراءات</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><strong><?= htmlspecialchars($user['username']) ?></strong></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <span class="badge <?= $user['role'] === 'admin' ? 'badge-danger' : 'badge-info' ?>">
                                    <?= $user['role'] === 'admin' ? 'مدير' : 'مستخدم' ?>
                                </span>
                            </td>
                            <td><?= date('Y-m-d H:i', strtotime($user['created_at'])) ?></td>
                            <?php if (isAdmin()): ?>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="/user-edit.php?id=<?= $user['id'] ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem;">✏️ تعديل</a>
                                    <?php if ($user['id'] !== $currentUser['id']): ?>
                                    <a href="/users.php?delete=<?= $user['id'] ?>" 
                                       class="btn btn-danger" 
                                       style="padding: 0.5rem 1rem;"
                                       onclick="return confirm('هل أنت متأكد من حذف هذا المستخدم؟');">🗑️ حذف</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
