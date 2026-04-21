<?php
include 'db.php';

// 1. Kuhaon ang ID gikan sa URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM category_list WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $category = $result->fetch_assoc();
}

// 2. I-save ang kausaban inig click sa Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $rate = $_POST['rate'];
    $status = $_POST['status'];

    $updateSql = "UPDATE category_list SET name = ?, rate = ?, status = ?, date_updated = NOW() WHERE id = ?";
    $stmtUpdate = $conn->prepare($updateSql);
    $stmtUpdate->bind_param("sdii", $name, $rate, $status, $id);

    if ($stmtUpdate->execute()) {
        header("Location: category_list.php?msg=updated");
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Category Rate</title>
    <style>
        body { font-family: Arial, sans-serif; background-image: url('water2.jpg'); background-size: cover; padding: 50px; }
        .form-container { max-width: 400px; margin: 0 auto; background: rgba(255,255,255,0.9); padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
        input, select { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; }
        .btn-update { background: #4FCFC8; color: white; border: none; padding: 12px; width: 100%; cursor: pointer; font-size: 16px; border-radius: 5px; }
        .btn-update:hover { background: #007770; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Edit Category & Rate</h2>
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
            
            <label>Category Name:</label>
            <input type="text" name="name" value="<?php echo $category['name']; ?>" required>
            
            <label>Rate (per m³):</label>
            <input type="number" step="0.01" name="rate" value="<?php echo $category['rate']; ?>" required>
            
            <label>Status:</label>
            <select name="status">
                <option value="1" <?php echo ($category['status'] == 1) ? 'selected' : ''; ?>>Active</option>
                <option value="0" <?php echo ($category['status'] == 0) ? 'selected' : ''; ?>>Inactive</option>
            </select>
            
            <button type="submit" class="btn-update">Update Rate</button>
            <br><br>
            <a href="category_list.php" style="text-decoration:none; color:#666;">← Cancel</a>
        </form>
    </div>
</body>
</html>