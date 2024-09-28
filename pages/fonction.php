<?php
function enregistrerHistorique($conn, $id_user, $action, $details)
{
    $stmt = $conn->prepare("INSERT INTO historique (id_user, action, details, date_action) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iss", $id_user, $action, $details);
    $stmt->execute();
    $stmt->close();
}
