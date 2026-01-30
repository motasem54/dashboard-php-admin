<?php
require_once 'config/init.php';
require_once 'includes/auth.php';
require_once 'includes/csv-logs.php';
require_once 'includes/logger.php';

requireAuth();

if (!isAdmin()) {
    header('Location: /dashboard.php');
    exit;
}

$currentUser = getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if ($fileType !== 'csv') {
            $error = 'يجب أن يكون الملف من نوع CSV';
        } else {
            $result = importCsvFile($file['tmp_name'], $currentUser['id']);
            
            if ($result['success']) {
                $success = "تم استيراد {$result['imported']} سجل بنجاح";
                logActivity($currentUser['id'], 'CSV_IMPORT', "تم استيراد {$result['imported']} سجل من ملف CSV");
            } else {
                $error = $result['error'] ?? 'حدث خطأ أثناء الاستيراد';
            }
        }
    } else {
        $error = 'حدث خطأ في رفع الملف';
    }
}

$stats = getCsvLogsStats();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>استيراد سجلات CSV - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div>
                <h1><?= APP_NAME ?></h1>
                <p>استيراد سجلات CSV</p>
            </div>
            <div style="display: flex; gap: 1rem;">
                <a href="/csv-logs.php" class="btn btn-secondary">📊 عرض السجلات</a>
                <a href="/dashboard.php" class="btn btn-secondary">← العودة</a>
                <a href="/logout.php" class="btn btn-secondary">تسجيل الخروج</a>
            </div>
        </div>
    </header>

    <main class="container">
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div>
                    <p class="stat-label">إجمالي السجلات</p>
                    <h2 class="stat-value"><?= number_format($stats['total'] ?? 0) ?></h2>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div>
                    <p class="stat-label">عدد المستخدمين</p>
                    <h2 class="stat-value"><?= number_format($stats['unique_users'] ?? 0) ?></h2>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div>
                    <p class="stat-label">تسجيلات دخول</p>
                    <h2 class="stat-value"><?= number_format($stats['total_logins'] ?? 0) ?></h2>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">❌</div>
                <div>
                    <p class="stat-label">فشل المصادقة</p>
                    <h2 class="stat-value"><?= number_format($stats['failed_auths'] ?? 0) ?></h2>
                </div>
            </div>
        </div>

        <!-- Upload Form -->
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <h3 class="card-title">📄 رفع ملف CSV</h3>

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

            <form method="POST" enctype="multipart/form-data" action="">
                <div class="form-group">
                    <label for="csv_file">اختر ملف CSV</label>
                    <input 
                        type="file" 
                        id="csv_file" 
                        name="csv_file" 
                        class="form-control" 
                        accept=".csv"
                        required
                    >
                    <small style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 0.5rem; display: block;">
                        الملف يجب أن يحتوي على الأعمدة: timestamp, source, message
                    </small>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    ⬆️ رفع واستيراد الملف
                </button>
            </form>

            <div style="margin-top: 2rem; padding: 1rem; background-color: var(--bg-tertiary); border-radius: 6px;">
                <h4 style="margin-bottom: 0.5rem; font-size: 1rem;">💡 ملاحظات:</h4>
                <ul style="margin: 0; padding-right: 1.5rem; color: var(--text-secondary); font-size: 0.9rem;">
                    <li>الملف يجب أن يكون بصيغة CSV</li>
                    <li>الصف الأول يجب أن يحتوي على عناوين الأعمدة</li>
                    <li>سيتم استخراج المعلومات مثل أسماء المستخدمين والعناوين تلقائياً</li>
                </ul>
            </div>
        </div>
    </main>
</body>
</html>
