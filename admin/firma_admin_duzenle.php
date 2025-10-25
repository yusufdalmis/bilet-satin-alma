<?php
require_once 'auth_check.php';

// Formdaki dropdown için tüm firmaları çek
$companies = $pdo->query("SELECT * FROM Bus_Company")->fetchAll();
$admin_user = null;

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM User WHERE id = ? AND role = 'company_admin'");
    $stmt->execute([$_GET['id']]);
    $admin_user = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $company_id = $_POST['company_id'];

    if ($id) { // Güncelleme
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE User SET full_name=?, email=?, password=?, company_id=? WHERE id=?");
            $stmt->execute([$full_name, $email, $hashed_password, $company_id, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE User SET full_name=?, email=?, company_id=? WHERE id=?");
            $stmt->execute([$full_name, $email, $company_id, $id]);
        }
    } else { // Ekleme
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO User (full_name, email, password, role, company_id) VALUES (?, ?, ?, 'company_admin', ?)");
        $stmt->execute([$full_name, $email, $hashed_password, $company_id]);
    }
    header("Location: firma_adminleri.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<body>
    <h1><?php echo $admin_user ? 'Firma Admini Düzenle' : 'Yeni Firma Admini Ekle'; ?></h1>
    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $admin_user['id'] ?? ''; ?>">
        <p>Ad Soyad: <input type="text" name="full_name" value="<?php echo htmlspecialchars($admin_user['full_name'] ?? ''); ?>" required></p>
        <p>Email: <input type="email" name="email" value="<?php echo htmlspecialchars($admin_user['email'] ?? ''); ?>" required></p>
        <p>Şifre: <input type="password" name="password" <?php if (!$admin_user) echo 'required'; ?>> (Değiştirmek istemiyorsanız boş bırakın)</p>
        <p>
            Firma Ata:
            <select name="company_id" required>
                <?php foreach ($companies as $company): ?>
                <option value="<?php echo $company['id']; ?>" <?php if (isset($admin_user['company_id']) && $admin_user['company_id'] == $company['id']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($company['name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </p>
        <button type="submit"><?php echo $admin_user ? 'Güncelle' : 'Ekle'; ?></button>
    </form>
</body>
</html>