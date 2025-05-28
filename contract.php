<?php
require('fpdf/fpdf.php');
include('db_connection.php');
include('phpqrcode/qrlib.php'); // QR code library

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

// Add logo (top-left)
$pdf->Image('images/logo.png',10,6,30);

$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Loan Contract Agreement',0,1,'C');
$pdf->Ln(10);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10,'Parties',0,1);
$pdf->SetFont('Arial','',12);
$pdf->MultiCell(0,8, "Lender: " . $loan['lender_name'] . "\nBorrower: " . $loan['borrower_name']);
$pdf->Ln(5);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10,'Loan Details',0,1);
$pdf->SetFont('Arial','',12);
$pdf->MultiCell(0,8, "Loan Amount: $" . number_format($loan['amount'], 2) . "\nDate Issued: " . $loan['issue_date']);
$pdf->Ln(10);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10,'Terms and Conditions',0,1);
$pdf->SetFont('Arial','',12);

$terms = "1. The borrower agrees to repay the loan amount as per the agreed schedule.\n";
$terms .= "2. The lender agrees to provide the loan under the agreed terms.\n";
$terms .= "3. Both parties agree to communicate promptly regarding any issues.\n";
$terms .= "4. Failure to comply may result in legal action.\n";

$pdf->MultiCell(0,8, $terms);

$pdf->Ln(20);

// Add scanned signatures (adjust paths & sizes)
$pdf->Image('images/lender_signature.png', 20, $pdf->GetY(), 50);
$pdf->Image('images/borrower_signature.png', 140, $pdf->GetY(), 50);

$pdf->Ln(30); // space below signatures

// Label signatures below images
$pdf->SetXY(20, $pdf->GetY());
$pdf->Cell(50,10,'Lender Signature',0,0,'C');

$pdf->SetXY(140, $pdf->GetY());
$pdf->Cell(50,10,'Borrower Signature',0,1,'C');

$pdf->Ln(15);

// Generate QR code for contract verification
$verify_url = "https://yourdomain.com/verify_contract.php?loan_id=$loan_id";
$tempDir = sys_get_temp_dir();
$qr_file = $tempDir . DIRECTORY_SEPARATOR . "qrcode_{$loan_id}.png";
QRcode::png($verify_url, $qr_file, 'L', 4, 2);

// Add QR code to PDF centered below signatures
$x_center = ($pdf->GetPageWidth() - 40) / 2; // center for 40mm width QR
$pdf->Image($qr_file, $x_center, $pdf->GetY(), 40);

$pdf->Ln(45); // space after QR code

// Optional: small text explaining QR code
$pdf->SetFont('Arial','I',10);
$pdf->Cell(0,10,'Scan the QR code to verify this contract online.',0,1,'C');

// Delete the temporary QR code file after output (optional)
// unlink($qr_file);

// Output PDF file for download
$pdf->Output("D", "Lendu_Contract_{$loan_id}.pdf");
exit;
?>
