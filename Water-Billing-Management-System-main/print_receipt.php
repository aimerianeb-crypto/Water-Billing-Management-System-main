<?php
include 'db.php'; // Include your database connection file

// Validate and sanitize the incoming ID parameter
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid billing ID.");
}

$billingId = $_GET['id'];

// Gi-update ang SQL: Gidugang ang b.meter_code
$sql = "SELECT b.id, b.client_id, b.meter_code, b.reading_date, b.due_date, b.reading, b.previous, b.rate, b.total, b.status, c.firstname, c.lastname, c.address
        FROM billing_list b
        INNER JOIN client_list c ON b.client_id = c.id
        WHERE b.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $billingId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    // Meter Display Logic
    $m_code = (!empty($row['meter_code'])) ? $row['meter_code'] : "N/A";

    // Generate a printable receipt
    $receiptContent = "
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Receipt #{$row['id']}</title>
            <style>
                body {
                    font-family: 'Segoe UI', Arial, sans-serif;
                    padding: 20px;
                    background-color: #f4f4f4;
                }
                .receipt-container {
                    width: 400px;
                    margin: 0 auto;
                    background-color: #fff;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                    padding: 25px;
                    border-top: 5px solid #3182bd;
                }
                h1 {
                    text-align: center;
                    font-size: 22px;
                    margin-bottom: 5px;
                    color: #2c3e50;
                }
                .sub-header {
                    text-align: center;
                    font-size: 12px;
                    color: #7f8c8d;
                    margin-bottom: 20px;
                }
                p {
                    margin-bottom: 5px;
                    font-size: 14px;
                }
                .info-section {
                    margin-bottom: 15px;
                    border-bottom: 1px dashed #ddd;
                    padding-bottom: 10px;
                }
                .billing-details {
                    border-collapse: collapse;
                    width: 100%;
                    margin-top: 10px;
                }
                .billing-details th, .billing-details td {
                    padding: 8px 0;
                    text-align: left;
                    font-size: 14px;
                }
                .billing-details th {
                    color: #7f8c8d;
                    font-weight: normal;
                }
                .billing-details td {
                    text-align: right;
                    font-weight: bold;
                }
                .total-row {
                    border-top: 2px solid #2c3e50;
                    margin-top: 10px;
                }
                .total-amount {
                    font-size: 18px;
                    color: #3182bd;
                }
                .print-button {
                    text-align: center;
                    margin-top: 30px;
                }
                .print-button button {
                    padding: 10px 25px;
                    background-color: #2c3e50;
                    color: #fff;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                }
                @media print {
                    body { background-color: #fff; padding: 0; }
                    .receipt-container { box-shadow: none; border: 1px solid #eee; width: 100%; }
                    .print-button, .back-link { display: none; }
                }
            </style>
        </head>
        <body>
            <div class='receipt-container'>
                <h1>WATER BILL RECEIPT</h1>
                <div class='sub-header'>Official Billing Statement</div>

                <div class='info-section'>
                    <p><strong>Client:</strong> {$row['firstname']} {$row['lastname']}</p>
                    <p><strong>Address:</strong> {$row['address']}</p>
                    <p><strong>Meter No:</strong> <span style='color: #e67e22;'>{$m_code}</span></p>
                </div>

                <table class='billing-details'>
                    <tr><th>Billing ID</th><td>#{$row['id']}</td></tr>
                    <tr><th>Reading Date</th><td>" . date('M d, Y', strtotime($row['reading_date'])) . "</td></tr>
                    <tr><th>Due Date</th><td>" . date('M d, Y', strtotime($row['due_date'])) . "</td></tr>
                    <tr><td colspan='2'><hr style='border: 0; border-top: 1px solid #eee;'></td></tr>
                    <tr><th>Previous Reading</th><td>" . number_format($row['previous'], 2) . " m³</td></tr>
                    <tr><th>Current Reading</th><td>" . number_format($row['reading'], 2) . " m³</td></tr>
                    <tr><th>Consumption</th><td>" . number_format($row['reading'] - $row['previous'], 2) . " m³</td></tr>
                    <tr><th>Rate</th><td>₱" . number_format($row['rate'], 2) . "</td></tr>
                    <tr class='total-row'>
                        <th>Grand Total</th>
                        <td class='total-amount'>₱" . number_format($row['total'], 2) . "</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td style='color: ".($row['status'] == 1 ? '#27ae60' : '#e74c3c')."'>". ($row['status'] == 1 ? 'PAID' : 'PENDING') ."</td>
                    </tr>
                </table>

                <div class='print-button'>
                    <button onclick='window.print()'>Print Official Receipt</button>
                </div>
                <div style='text-align:center; margin-top: 10px;' class='back-link'>
                    <a href='view_billings.php' style='font-size: 12px; color: #7f8c8d; text-decoration: none;'>&larr; Back to Billings</a>
                </div>
            </div>
        </body>
        </html>
    ";

    echo $receiptContent;
} else {
    echo "Billing record not found.";
}

$stmt->close();
$conn->close();
?>