<?php
require_once 'auth_check.php';

$company = null;
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM Bus_Company WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $company = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $id = $_POST['id'] ?? null;
    if ($id) {
        $stmt = $pdo->prepare("UPDATE Bus_Company SET name = ? WHERE id = ?");
        $stmt->execute([$name, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO Bus_Company (name) VALUES (?)");
        $stmt->execute([$name]);
    }
    header("Location: firmalar.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<body>
    <h1><?php echo $company ? 'Firma Düzenle' : 'Yeni Firma Ekle'; ?></h1>
    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $company['id'] ?? ''; ?>">
        Firma Adı: <input type="text" name="name" value="<?php echo htmlspecialchars($company['name'] ?? ''); ?>" required>
        <button type="submit"><?php echo $company ? 'Güncelle' : 'Ekle'; ?></button>
    </form>
</body>
</html>