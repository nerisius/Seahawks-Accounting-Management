<?php 
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/header.php';

$player_id = intval($_GET['player_id']);
$player = $conn->query("SELECT * FROM roster WHERE player_id = $player_id")->fetch_assoc();

// Handle debt deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_debt'])) {
    $debt_id = intval($_POST['delete_debt']);
    $player_id = intval($_GET['player_id']);
    
    try {
        $sql = "DELETE FROM roster_debts WHERE debt_id = $debt_id";
        if ($conn->query($sql)) {
            $_SESSION['success'] = "Debt deleted successfully!";
            
            // REDIRECT after successful DELETE
            header("Location: roster_debts.php?player_id=$player_id");
            exit();
        } else {
            throw new Exception("Database error: " . $conn->error);
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error deleting debt: " . $e->getMessage();
    }
}

// Handle new debt submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_debt'])) {
    $amount = floatval($_POST['amount']);
    $description = $conn->real_escape_string($_POST['description']);
    $player_id = intval($_GET['player_id']);
    
    try {
        $sql = "INSERT INTO roster_debts (player_id, amount, description) 
                VALUES ($player_id, $amount, '$description')";
        
        if ($conn->query($sql)) {
            $_SESSION['success'] = "Debt added successfully!";
            
            // REDIRECT after successful POST
            header("Location: roster_debts.php?player_id=$player_id");
            exit();
        } else {
            throw new Exception("Database error: " . $conn->error);
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error adding debt: " . $e->getMessage();
    }
}

// Handle marking debt as paid
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_paid'])) {
   
    $debt_id = intval($_POST['mark_paid']);
    $player_id = intval($_GET['player_id']);
    
    try {
        $sql = "UPDATE roster_debts 
                SET is_paid = TRUE, date_paid = NOW() 
                WHERE debt_id = $debt_id";
        
        if ($conn->query($sql)) {
            $_SESSION['success'] = "Debt marked as paid!";
        } else {
            throw new Exception("Database error: " . $conn->error);
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error updating debt: " . $e->getMessage();
    }
    
    header("Location: roster_debts.php?player_id=$player_id");
    exit();
}

if (!$player) {
    die("player not found");
}

// Handle debt payment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_paid'])) {
    $debt_id = intval($_POST['debt_id']);
    $date_paid = $conn->real_escape_string($_POST['date_paid']);
    
    $sql = "UPDATE roster_debts SET is_paid = TRUE, date_paid = '$date_paid' 
            WHERE debt_id = $debt_id";
    
    if ($conn->query($sql)) {
        $success = "Debt marked as paid successfully!";
    } else {
        $error = "Error updating debt: " . $conn->error;
    }
}

// Handle new debt
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_debt'])) {
    $amount = floatval($_POST['amount']);
    $description = $conn->real_escape_string($_POST['description']);
    
    $sql = "INSERT INTO roster_debts (player_id, amount, description)
            VALUES ($player_id, $amount, '$description')";
    
    if ($conn->query($sql)) {
        $success = "Debt added successfully!";
    } else {
        $error = "Error adding debt: " . $conn->error;
    }
}

// Get player's debts
$debts = $conn->query("SELECT * FROM roster_debts WHERE player_id = $player_id ORDER BY is_paid");
?>

<h2>Debt Management for <?php echo htmlspecialchars($player['name']); ?></h2>

<?php if (isset($success)): ?>
<div class="alert success"><?php echo $success; ?></div>
<?php elseif (isset($error)): ?>
<div class="alert error"><?php echo $error; ?></div>
<?php endif; ?>

<!-- In your roster_debts.php file -->
<h3>Current Debts</h3>
<table class="data-table">
    <thead>
        <tr>
            <th>Description</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Date Paid</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($debts as $debt): ?>
            <tr>
                <td><?php echo htmlspecialchars($debt['description']); ?></td>

                <td><?php echo number_format($debt['amount'], 2); ?> ₺</td>
                <td>
                    <?php if ($debt['is_paid']): ?>
                        <span class="badge badge-success">Paid</span>
                    <?php else: ?>
                        <span class="badge badge-warning">Unpaid</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php echo $debt['date_paid'] ? date('Y-m-d', strtotime($debt['date_paid'])) : 'Not paid'; ?>
                </td>
                <td>
                    <?php if (!$debt['is_paid']): ?>
                        <form method="post" action="roster_debts.php?player_id=<?php echo $player_id; ?>" style="display:inline;">
                            <input type="hidden" name="mark_paid" value="<?php echo $debt['debt_id']; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <button type="submit" class="btn-paid btn-sm">Mark Paid</button>
                        </form>
                    <?php endif; ?>
                    
                    <form method="post" action="roster_debts.php?player_id=<?php echo $player_id; ?>" style="display:inline;">
                        <input type="hidden" name="delete_debt" value="<?php echo $debt['debt_id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <button type="submit" class="btn-delete btn-sm" 
                                onclick="return confirm('Are you sure?')">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h3>Add New Debt</h3>
<form method="post" action="roster_debts.php?player_id=<?php echo $player_id; ?>">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    
    <!-- Your form fields here -->
    <div class="form-group">
        <label for="amount">Amount:</label>
        <input type="number" id="amount" name="amount" step="0.01" min="0" required autocomplete="off">
    </div>
    
    <div class="form-group">
        <label for="description">Description:</label>
        <input type="text" id="description" name="description" required autocomplete="off">
    </div>
    
    <button type="submit" name="add_debt" class="btn">Add Debt</button>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>