<?php 
require_once 'includes/config.php';
require_once 'includes/header.php';

// Get totals
$totalExpenses = $conn->query("SELECT SUM(amount) AS total FROM expenses")->fetch_assoc()['total'] ?? 0;
$totalIncome = $conn->query("SELECT SUM(amount) AS total FROM incomes")->fetch_assoc()['total'] ?? 0;

// Get player debts (unpaid only)
$playerDebts = $conn->query("SELECT SUM(amount) AS total FROM roster_debts WHERE is_paid = FALSE")->fetch_assoc()['total'] ?? 0;

// Get other owers debts
$otherOwerDebts = $conn->query("SELECT SUM(amount) AS total FROM other_owers")->fetch_assoc()['total'] ?? 0;

// Combine both types of debts
$totalDebts = $playerDebts + $otherOwerDebts;

$balance = $totalIncome - $totalExpenses;
?>

<h2>Club Financial Dashboard</h2>

<div class="summary-cards">
    <div class="card">
        <h3>Current Balance</h3>
        <p><?php echo formatCurrency($balance); ?></p>
    </div>
    <div class="card">
        <h3>Outstanding Debts</h3>
        <p><?php echo formatCurrency($totalDebts); ?></p>
    </div>
</div>

<h3>Recent Transactions</h3>
<div class="recent-transactions">
    <div class="recent-expenses">
        <h4>Recent Expenses</h4>
        <ul>
            <?php
            $expenses = $conn->query("SELECT * FROM expenses ORDER BY date_incurred DESC LIMIT 5");
            while($expense = $expenses->fetch_assoc()): ?>
            <li>
                <?php echo htmlspecialchars($expense['description']); ?> - 
                <?php echo formatCurrency($expense['amount']); ?>
                <small><?php echo $expense['date_incurred']; ?></small>
            </li>
            <?php endwhile; ?>
        </ul>
    </div>
    
    <div class="recent-incomes">
        <h4>Recent Income</h4>
        <ul>
            <?php
            $incomes = $conn->query("SELECT * FROM incomes ORDER BY date_received DESC LIMIT 5");
            while($income = $incomes->fetch_assoc()): ?>
            <li>
                <?php echo htmlspecialchars($income['description']); ?> - 
                <?php echo formatCurrency($income['amount']); ?>
                <small><?php echo $income['date_received']; ?></small>
            </li>
            <?php endwhile; ?>
        </ul>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>