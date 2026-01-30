<?php
require_once 'config/init.php';
require_once 'includes/auth.php';
require_once 'includes/logger.php';

requireAuth();

if (!isAdmin()) {
    header('Location: /dashboard.php');
    exit;
}

$currentUser = getCurrentUser();

// Get system statistics
$conn = getConnection();

// Database size
$stmt = $conn->query("
    SELECT 
        ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
    FROM information_schema.TABLES 
    WHERE table_schema = '" . DB_NAME . "'
");
$dbSize = $stmt->fetch()['size_mb'] ?? 0;

// Total records
$stmt = $conn->query("SELECT COUNT(*) FROM users");
$totalUsers = $stmt->fetchColumn();

$stmt = $conn->query("SELECT COUNT(*) FROM data_logs");
$totalLogs = $stmt->fetchColumn();

// Recent activity
$stmt = $conn->query("
    SELECT COUNT(*) FROM data_logs 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
");
$logsLast24h = $stmt->fetchColumn();

$stmt = $conn->query("
    SELECT COUNT(*) FROM data_logs 
    WHERE action = 'LOGIN_SUCCESS' 
    AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
");
$loginsLast7days = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعدادات النوظام - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div>
                <h1><?= APP_NAME ?></h1>
                <p>إعدادات النظام</p>
            </div>
            <div style="display: flex; gap: 1rem;">
                <a href="/dashboard.php" class="btn btn-secondary">← العودة للوحة</a>
                <a href="/logout.php" class="btn btn-secondary">تسجيل الخروج</a>
            </div>
        </div>
    </header>

    <main class="container">
        <!-- System Info -->
        <div class="card">
            <h3 class="card-title">📊 معلومات النظام</h3>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">💾</div>
                    <div>
                        <p class="stat-label">حجم قاعدة البيانات</p>
                        <h2 class="stat-value"><?= $dbSize ?> MB</h2>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div>
                        <p class="stat-label">إجمالي المستخدمين</p>
                        <h2 class="stat-value"><?= $totalUsers ?></h2>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">📝</div>
                    <div>
                        <p class="stat-label">إجمالي السجلات</p>
                        <h2 class="stat-value"><?= $totalLogs ?></h2>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">🔥</div>
                    <div>
                        <p class="stat-label">نشاط آخر 24 ساعة</p>
                        <h2 class="stat-value"><?= $logsLast24h ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Application Info -->
        <div class="card">
            <h3 class="card-title">⚙️ معلومات التطبيق</h3>
            
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 2rem;">
                <div>
                    <label style="color: var(--text-secondary); font-size: 0.85rem; display: block; margin-bottom: 0.5rem;">اسم التطبيق</label>
                    <p style="font-size: 1.1rem; font-weight: 600;"><?= APP_NAME ?></p>
                </div>
                
                <div>
                    <label style="color: var(--text-secondary); font-size: 0.85rem; display: block; margin-bottom: 0.5rem;">الإصدار</label>
                    <p style="font-size: 1.1rem; font-weight: 600;"><?= APP_VERSION ?></p>
                </div>
                
                <div>
                    <label style="color: var(--text-secondary); font-size: 0.85rem; display: block; margin-bottom: 0.5rem;">إصدار PHP</label>
                    <p style="font-size: 1.1rem; font-weight: 600;"><?= phpversion() ?></p>
                </div>
                
                <div>
                    <label style="color: var(--text-secondary); font-size: 0.85rem; display: block; margin-bottom: 0.5rem;">قاعدة البيانات</label>
                    <p style="font-size: 1.1rem; font-weight: 600;">MySQL</p>
                </div>
                
                <div>
                    <label style="color: var(--text-secondary); font-size: 0.85rem; display: block; margin-bottom: 0.5rem;">تسجيلات الدخول (آخر 7 أيام)</label>
                    <p style="font-size: 1.1rem; font-weight: 600;"><?= $loginsLast7days ?></p>
                </div>
                
                <div>
                    <label style="color: var(--text-secondary); font-size: 0.85rem; display: block; margin-bottom: 0.5rem;">المنطقة الزمنية</label>
                    <p style="font-size: 1.1rem; font-weight: 600;"><?= date_default_timezone_get() ?></p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <h3 class="card-title">⚡ إجراءات سريعة</h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <a href="/users.php" class="btn btn-secondary btn-block">👥 إدارة المستخدمين</a>
                <a href="/user-add.php" class="btn btn-primary btn-block">➕ إضافة مستخدم</a>
                <a href="/dashboard.php" class="btn btn-secondary btn-block">📊 عرض الإحصائيات</a>
                <a href="/profile.php" class="btn btn-secondary btn-block">👤 الملف الشخصي</a>
            </div>
        </div>

        <!-- Database Info -->
        <div class="card">
            <h3 class="card-title">🗃️ معلومات قاعدة البيانات</h3>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>اسم الجدول</th>
                            <th>عدد السجلات</th>
                            <th>الحجم</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $conn->query("
                            SELECT 
                                table_name,
                                table_rows,
                                ROUND((data_length + index_length) / 1024, 2) AS size_kb
                            FROM information_schema.TABLES
                            WHERE table_schema = '" . DB_NAME . "'
                            ORDER BY (data_length + index_length) DESC
                        ");
                        $tables = $stmt->fetchAll();
                        
                        foreach ($tables as $table):
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($table['table_name']) ?></strong></td>
                            <td><?= number_format($table['table_rows']) ?></td>
                            <td><?= $table['size_kb'] ?> KB</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
