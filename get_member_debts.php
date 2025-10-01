<?php
require_once 'includes/config.php';

$player_id = intval($_GET['player_id']);
$debts = $conn->query("SELECT * FROM roster_debts 
                      WHERE player_id = $player_id AND is_paid = FALSE
                      ORDER BY date_incurred DESC");

$result = [];
while($debt = $debts->fetch_assoc()) {
    $result[] = [
        'debt_id' => $debt['debt_id'],
        'description' => $debt['description'],
        'amount' => formatCurrency($debt['amount']),
        'date_incurred' => $debt['date_incurred']
    ];
}

header('Content-Type: application/json');
echo json_encode($result);
?>