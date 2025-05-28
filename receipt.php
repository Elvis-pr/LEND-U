<?php
require('fpdf/fpdf.php');
include('db_connection.php');

$loan_id = $_GET['loan_id'] ?? null;
if (!$loan_id) {
    die("Loan ID is required.");
}

// Fetch loan and user info
$query = $conn->prepare("SELECT l.amount, l.issue_date, b.name AS borrower_name, r.name AS lender_name FROM loans l 
    JOIN users b ON l.borrower_id = b.id 
    JOIN users r ON l.lender_id = r.id
    WHERE l.id = ?");
$query->bind_param("i", $loan_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows == 0) {
    die("No loan found with the given ID.");
}

$loan = $result->fetch_assoc();

$pdf = new FPDF();
$pdf->AddPage();

// Add logo (adjust path and size)
$pdf->Image('images/logo.png',10,6,30);

$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Payment Receipt',0,1,'C');

$pdf->SetFont('Arial','',12);
$pdf->Ln(10);

$pdf->Cell(50,10,"Receipt No:",0,0);
$pdf->Cell(0,10,$loan_id,0,1);

$pdf->Cell(50,10,"Date:",0,0);
$pdf->Cell(0,10,date("Y-m-d"),0,1);

$pdf->Cell(50,10,"Borrower:",0,0);
$pdf->Cell(0,10,$loan['borrower_name'],0,1);

$pdf->Cell(50,10,"Lender:",0,0);
$pdf->Cell(0,10,$loan['lender_name'],0,1);

$pdf->Cell(50,10,"Amount Paid:",0,0);
$pdf->Cell(0,10,"$" . number_format($loan['amount'], 2),0,1);

$pdf->Ln(15);
$pdf->MultiCell(0,10,"Thank you for your payment. This receipt confirms that the above amount has been paid for loan ID $loan_id.");

// Output PDF file for download
$pdf->Output("D", "Lendu_Receipt_{$loan_id}.pdf");
?>
