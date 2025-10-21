<?php
require_once('../dompdf/autoload.inc.php');
include '../includes/config.php';

if (!isset($_GET['id'])) {
    die('Order ID is missing.');
}
$purchaseOrderId = $_GET['id'];

// Fetch order
$sqlOrder = "SELECT po.*, s.name as supplier_name, s.address FROM purchase_orders po
             JOIN suppliers s ON po.supplier_id = s.supplier_id
             WHERE po.id = ?";
$stmt = $conn->prepare($sqlOrder);
$stmt->bind_param('i', $purchaseOrderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

// Fetch items
$sqlItems = "SELECT p.productname, poi.quantity, poi.unit_price FROM purchase_order_items poi
             JOIN products p ON poi.product_id = p.id
             WHERE poi.purchase_order_id = ?";
$stmtItems = $conn->prepare($sqlItems);
$stmtItems->bind_param('i', $purchaseOrderId);
$stmtItems->execute();
$items = $stmtItems->get_result();

// Create new PDF
$pdf = new DOMPDF();
$pdf->AddPage();

// Company logo and details
$pdf->Image('../assets/images/TheTouch2.png', 10, 10, 40); // Adjust logo path and size
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 15, 'The Touch Haven', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 5, 'P.O. Box 1710, 30200, KITALE', 0, 1, 'C');
$pdf->Ln(10);

// Supplier Info
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Purchase Order', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 5, 'Supplier: ' . $order['supplier_name'], 0, 1);
$pdf->Cell(0, 5, 'Address: ' . $order['address'], 0, 1);
$pdf->Cell(0, 5, 'Order Date: ' . $order['order_date'], 0, 1);
$pdf->Ln(5);

// Table
$html = '
<table border="1" cellpadding="4">
    <thead>
        <tr>
            <th><b>No</b></th>
            <th><b>Product</b></th>
            <th><b>Unit Price</b></th>
            <th><b>Quantity</b></th>
            <th><b>Total</b></th>
        </tr>
    </thead>
    <tbody>';
$no = 1;
$grandTotal = 0;
while ($item = $items->fetch_assoc()) {
    $total = $item['quantity'] * $item['unit_price'];
    $grandTotal += $total;
    $html .= '<tr>
        <td>' . $no++ . '</td>
        <td>' . $item['name'] . '</td>
        <td>' . number_format($item['unit_price'], 2) . '</td>
        <td>' . $item['quantity'] . '</td>
        <td>' . number_format($total, 2) . '</td>
    </tr>';
}
$html .= '</tbody></table>';

$pdf->writeHTML($html, true, false, true, false, '');

// Grand Total
$pdf->Ln(5);
$pdf->Cell(0, 10, 'Grand Total: KES ' . number_format($grandTotal, 2), 0, 1, 'R');

$pdf->Output('purchase_order.pdf', 'I');
?>
