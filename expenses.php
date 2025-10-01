<?php 
require_once 'includes/config.php';
require_once 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = floatval($_POST['amount']);
    $recover = $conn->real_escape_string($_POST['recover']);
    $description = $conn->real_escape_string($_POST['description']);
    
    // Removed date_incurred from SQL
    $sql = "INSERT INTO expenses (amount, recover, description) 
            VALUES ($amount, '$recover', '$description')";
    
    if ($conn->query($sql)) {
        $_SESSION['success'] = "Expense recorded successfully!";
        header("Location: expenses.php");
        exit();
    } else {
        $error = "Error: " . $conn->error;
    }
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $sql = "DELETE FROM expenses WHERE expense_id = $id";
    
    if ($conn->query($sql)) {
        $_SESSION['success'] = "Expense deleted successfully!";
        header("Location: expenses.php");
        exit();
    } else {
        $error = "Delete error: " . $conn->error;
    }
}
?>

<h2>Record New Expense</h2>

<?php if (isset($_SESSION['success'])): ?>
<div class="alert success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php elseif (isset($error)): ?>
<div class="alert error"><?php echo $error; ?></div>
<?php endif; ?>

<form method="post">
    <div class="form-group">
        <label for="amount">Amount (₺):</label>
        <input type="number" id="amount" name="amount" step="0.01" min="0" required autocomplete="off">
    </div>

        <div class="form-group">
            <label for="recover">Recover From (Name/Entity):</label>
            <input type="text" id="recover" name="recover" required autocomplete="off">
        </div>
    
    <div class="form-group">
        <label for="description">Description:</label>
        <input type="text" id="description" name="description" required autocomplete="off">
    </div>
    
    <div class="form-group">
        <label for="date_incurred">Date:</label>
        <input type="date" id="date_incurred" name="date_incurred" required value="<?php echo date('Y-m-d'); ?>">
    </div>
    
    <button type="submit" class="btn">Record Expense</button>
</form>

<h3>Expense History</h3>
<table class="data-table">
    <thead>
        <tr>
            <!-- Removed Date column -->
            <th>Description</th>
            <th>Amount</th>
            <th>Recover From</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $result = $conn->query("SELECT * FROM expenses ORDER BY expense_id DESC"); // Simple ordering by ID
        while ($row = $result->fetch_assoc()): 
        ?>
        <tr>
            <!-- Removed date_incurred cell -->
            <td><?php echo htmlspecialchars($row['description']); ?></td>
            <td><?php echo formatCurrency($row['amount']); ?></td>
            <td><?php echo htmlspecialchars($row['recover']); ?></td>
            <td>
                <a href="expenses.php?delete=<?php echo $row['expense_id']; ?>" 
                   class="delete-btn"
                   onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php require_once 'includes/footer.php'; ?>