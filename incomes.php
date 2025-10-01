<?php 
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/header.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if this is a resubmission
    if (!isset($_SESSION['last_income_submission'])) {
        $_SESSION['last_income_submission'] = [];
    }
    
    $current_submission = [
        'amount' => $_POST['amount'],
        'description' => $_POST['description'],
        'date_received' => $_POST['date_received']
    ];
    
    // Compare with last submission
    if ($_SESSION['last_income_submission'] != $current_submission) {
        $amount = floatval($_POST['amount']);
        $description = $conn->real_escape_string($_POST['description']);
        $date_received = $conn->real_escape_string($_POST['date_received']);
        
        $sql = "INSERT INTO incomes (amount, description, date_received)
                VALUES ($amount, '$description', '$date_received')";
        
        if ($conn->query($sql)) {
            $_SESSION['last_income_submission'] = $current_submission;
            header("Location: incomes.php?success=1");
            exit();
        } else {
            $error = "Error recording income: " . $conn->error;
        }
    } else {
        $error = "This income was just recorded. Please add a new income record.";
    }
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $income_id = intval($_GET['delete_id']);
    $sql = "DELETE FROM incomes WHERE income_id = $income_id";
    if ($conn->query($sql)) {
        header("Location: incomes.php?success=Income deleted successfully");
        exit();
    } else {
        $error = "Error deleting income: " . $conn->error;
    }
}

?>

<h2>Record New Income</h2>

<?php if (isset($success)): ?>
<div class="alert success"><?php echo $success; ?></div>
<?php elseif (isset($error)): ?>
<div class="alert error"><?php echo $error; ?></div>
<?php endif; ?>

<form method="post" action="incomes.php">
    <div class="form-group">
        <label for="amount">Amount:</label>
        <input type="number" id="amount" name="amount" step="0.01" min="0" required autocomplete="off">
    </div>
    
    <div class="form-group">
        <label for="description">Description:</label>
        <input type="text" id="description" name="description" required autocomplete="off">
    </div>
    
    <div class="form-group">
        <label for="date_received">Date Received:</label>
        <input type="date" id="date_received" name="date_received" required value="<?php echo date('Y-m-d'); ?>">
    </div>
    
    <button type="submit" class="btn">Record Income</button>
</form>

<h3>Income History</h3>
<table class="data-table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Description</th>
            <th>Amount</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $incomes = $conn->query("SELECT * FROM incomes ORDER BY date_received DESC");
        while($income = $incomes->fetch_assoc()):
        ?>
        <tr>
            <td><?php echo $income['date_received']; ?></td>
            <td><?php echo htmlspecialchars($income['description']); ?></td>
            <td><?php echo formatCurrency($income['amount']); ?></td>
            <td>
                <a href="incomes.php?delete_id=<?php echo $income['income_id']; ?>" 
                   class="btn-delete" 
                   onclick="return confirm('Are you sure you want to delete this income record?')">
                    Delete
                </a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/includes/footer.php'; ?>