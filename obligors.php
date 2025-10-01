<?php 
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/header.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['player_submit'])) {
        $name = $conn->real_escape_string($_POST['name']);

        
        $sql = "INSERT INTO roster (name) VALUES ('$name')";
        
        if ($conn->query($sql)) {
            $_SESSION['success'] = "Player added successfully!";
        } else {
            $_SESSION['error'] = "Error adding player: " . $conn->error;
        }
        header("Location: obligors.php");
        exit();
    }
    
    if (isset($_POST['ower_submit'])) {
        $ower_name = $conn->real_escape_string($_POST['ower_name']);
        $amount = floatval($_POST['amount']);
        
        $sql = "INSERT INTO other_owers (name, amount) VALUES ('$ower_name', $amount)";
        
        if ($conn->query($sql)) {
            $_SESSION['success'] = "Other ower added successfully!";
        } else {
            $_SESSION['error'] = "Error adding other ower: " . $conn->error;
        }
        header("Location: obligors.php");
        exit();
    }
}
// Handle player deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_player'])) {
    $player_id = intval($_POST['delete_player']);
    
    try {
        $conn->begin_transaction();
        
        // First delete all debts for this player
        $conn->query("DELETE FROM roster_debts WHERE player_id = $player_id");
        
        // Then delete the player
        $conn->query("DELETE FROM roster WHERE player_id = $player_id");
        
        $conn->commit();
        $_SESSION['success'] = "Player deleted successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error deleting player: " . $e->getMessage();
    }
    
    header("Location: obligors.php");
    exit();
}

// Display messages from session
if (isset($_SESSION['success'])) {
    echo '<script>showFlashMessage("'.htmlspecialchars($_SESSION['success']).'", "success");</script>';
    unset($_SESSION['success']);
} elseif (isset($_SESSION['error'])) {
    echo '<script>showFlashMessage("'.htmlspecialchars($_SESSION['error']).'", "error");</script>';
    unset($_SESSION['error']);
}

// Get player list with unpaid debt flag
$roster = $conn->query("
    SELECT r.*, 
           MAX(CASE WHEN rd.is_paid = FALSE AND rd.amount > 0 THEN 1 ELSE 0 END) AS has_unpaid_debt
    FROM roster r
    LEFT JOIN roster_debts rd ON r.player_id = rd.player_id
    GROUP BY r.player_id
    ORDER BY r.name
");

if (!$roster) {
    die("Database error: " . $conn->error);
}
?>

<h2>Manage Debts</h2>

<div class="action-buttons">
    <button id="toggle-player-form" class="btn">Add New Player</button>
    <button id="toggle-ower-form" class="btn">Add Other Ower</button>
</div>

<!-- Player Form (initially hidden) -->
<div id="player-form" class="collapsible-form" style="display: none;">
    <h3>Add New Player</h3>
    <form method="post" action="obligors.php">
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required autocomplete="off">
        </div>
        <button type="submit" name="player_submit" class="btn">Add Player</button>
    </form>
</div>

<!-- Ower Form (initially hidden) -->
<div id="ower-form" class="collapsible-form" style="display: none;">
    <h3>Add Other Ower</h3>
    <form method="post" action="obligors.php">
        <div class="form-group">
            <label for="ower_name">Name:</label>
            <input type="text" id="ower_name" name="ower_name" required autocomplete="off">
        </div>
        <div class="form-group">
            <label for="amount">Amount Owed:</label>
            <input type="number" id="amount" name="amount" step="0.01" min="0" required autocomplete="off">
        </div>
        <button type="submit" name="ower_submit" class="btn">Add Ower</button>
    </form>
</div>

<h3>Player List</h3>
<table class="player-table">
    <thead>
        <tr>
            <th style="text-align: left;">Name</th>
            <th style="text-align: right;">Actions</th>
            <th style="width: 30px;"></th> <!-- For indicator -->
        </tr>
    </thead>
    <tbody>
        <?php while($player = $roster->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($player['name']); ?></td>
            <td style="text-align: right;">
                <div style="display: inline-block;">
                    <a href="roster_debts.php?player_id=<?php echo $player['player_id']; ?>" 
                       class="btn-view-debts">View Debts</a>
                    <form method="post" action="obligors.php" style="display: inline-block; margin-left: 8px;">
                        <input type="hidden" name="delete_player" value="<?php echo $player['player_id']; ?>">
                        <button type="submit" class="btn-delete-player">Delete</button>
                    </form>
                </div>
            </td>
            <td>
                <?php if ($player['has_unpaid_debt']): ?>
                    <span class="debt-indicator" title="Unpaid debt">!</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<h3>Other Owers List</h3>
<table class="data-table" id="owers-table">
    <thead>
        <tr>
            <th>Name</th>
            <th style="width: 200px;"></th> 
            <th style="text-align: left;" >Amount Owed</th>
        
            <th style="text-align: right;">Actions</th>
            <th style="width: 20px;"></th> 
        </tr>
    </thead>
    <tbody>
        <?php
        $owers = $conn->query("SELECT * FROM other_owers ORDER BY name");
        while($ower = $owers->fetch_assoc()):
        ?>
        <tr id="ower-row-<?php echo $ower['id']; ?>">
            <td><?php echo htmlspecialchars($ower['name']); ?></td>
            <td><?php "width: 40px;" ?></td>
            <td><?php echo number_format($ower['amount'], 2); ?> TL</td>
            <td><?php "width: 0.1px;" ?></td>
            
            <td>
                <button class="btn-delete btn-sm-delete btn-danger-delete delete-ower-btn" 
                        data-ower-id="<?php echo $ower['id']; ?>">   
                    Delete
                </button>
            </td>
            
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('owers-table').addEventListener('click', async function(e) {
        if (e.target.classList.contains('delete-ower-btn')) {
            const button = e.target;
            const owerId = button.getAttribute('data-ower-id');
            const row = document.getElementById(`ower-row-${owerId}`);
            
            if (!confirm('Are you sure you want to delete this ower?')) {
                return;
            }
            
            button.disabled = true;
            const originalText = button.textContent;
            button.textContent = 'Deleting...';
            
            try {
                const response = await fetch('ajax_delete.php', {  // Changed to new file
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `ower_id=${owerId}`  // Simplified since we only need this
                });
                
                // Check if response is OK
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                // Parse JSON
                const data = await response.json();
                
                if (data.success) {
                    row.remove();
                    showAlert('Ower deleted successfully', 'success');
                } else {
                    throw new Error(data.error || 'Unknown error');
                }
            } catch (error) {
                console.error('Delete error:', error);
                showAlert('Error: ' + error.message, 'error');
            } finally {
                button.disabled = false;
                button.textContent = originalText;
            }
        }
    });
    
    function showAlert(message, type) {
        const existingAlerts = document.querySelectorAll('.alert');
        existingAlerts.forEach(alert => alert.remove());
        
        const alert = document.createElement('div');
        alert.className = `alert ${type}`;
        alert.textContent = message;
        document.body.appendChild(alert);
        
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 3000);
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const playerForm = document.getElementById('player-form');
    const owerForm = document.getElementById('ower-form');
    const togglePlayerBtn = document.getElementById('toggle-player-form');
    const toggleOwerBtn = document.getElementById('toggle-ower-form');
    
    // Toggle player form
    togglePlayerBtn.addEventListener('click', function() {
        if (playerForm.style.display === 'none') {
            playerForm.style.display = 'block';
            owerForm.style.display = 'none'; // Hide ower form if open
        } else {
            playerForm.style.display = 'none';
        }
    });
    
    // Toggle ower form
    toggleOwerBtn.addEventListener('click', function() {
        if (owerForm.style.display === 'none') {
            owerForm.style.display = 'block';
            playerForm.style.display = 'none'; // Hide player form if open
        } else {
            owerForm.style.display = 'none';
        }
    });
    
    // Close forms when clicking outside
    document.addEventListener('click', function(e) {
        if (!playerForm.contains(e.target) && e.target !== togglePlayerBtn) {
            playerForm.style.display = 'none';
        }
        if (!owerForm.contains(e.target) && e.target !== toggleOwerBtn) {
            owerForm.style.display = 'none';
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    // Player deletion
    document.querySelector('.data-table').addEventListener('click', async function(e) {
        if (e.target.classList.contains('delete-player-btn')) {
            const button = e.target;
            const playerId = button.getAttribute('data-player-id');
            const row = document.getElementById(`player-row-${playerId}`);
            
            if (!confirm('Are you sure you want to delete this player? This will also delete all their associated debts.')) {
                return;
            }
            
            button.disabled = true;
            const originalText = button.textContent;
            button.textContent = 'Deleting...';
            
            try {
                const response = await fetch('ajax_delete.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `player_id=${playerId}&type=player`
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    row.remove();
                    showAlert('Player deleted successfully', 'success');
                } else {
                    throw new Error(data.error || 'Unknown error');
                }
            } catch (error) {
                console.error('Delete error:', error);
                showAlert('Error: ' + error.message, 'error');
            } finally {
                button.disabled = false;
                button.textContent = originalText;
            }
        }
    });
});    
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>