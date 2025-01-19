<?php
require_once '../../config/db_connect.php';
require_once '../../vendor/autoload.php'; // Make sure you've installed dompdf via composer
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

use Dompdf\Dompdf;
use Dompdf\Options;

// Get filter parameters
$availability_filter = isset($_GET['availability']) ? $_GET['availability'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build query with filters
$query = "
    SELECT 
        a.*,
        e.school_name, 
        e.degree_obtained,
        GROUP_CONCAT(DISTINCT sq.skill SEPARATOR ', ') as skills,
        GROUP_CONCAT(DISTINCT eh.previous_employer SEPARATOR ', ') as previous_employers
    FROM applicants a
    LEFT JOIN education e ON a.applicant_id = e.applicant_id
    LEFT JOIN skills_qualifications sq ON a.applicant_id = sq.applicant_id
    LEFT JOIN employment_history eh ON a.applicant_id = eh.applicant_id
    WHERE 1=1
";

$params = array();
$param_types = "";

if ($availability_filter) {
    $query .= " AND a.availability = ?";
    $params[] = $availability_filter;
    $param_types .= "s";
}

if ($date_from) {
    $query .= " AND DATE(a.created_at) >= ?";
    $params[] = $date_from;
    $param_types .= "s";
}

if ($date_to) {
    $query .= " AND DATE(a.created_at) <= ?";
    $params[] = $date_to;
    $param_types .= "s";
}

$query .= " GROUP BY a.applicant_id ORDER BY a.created_at DESC";

// Prepare and execute query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Start building HTML content
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Job Applications Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .filters { margin-bottom: 20px; }
        .applicant-details { margin-bottom: 15px; padding: 10px; background-color: #f9f9f9; }
        .page-break { page-break-after: always; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Job Applications Report</h1>
        <p>Generated on: ' . date('Y-m-d H:i:s') . '</p>
    </div>
';

// Add filter information if any
if ($availability_filter || $date_from || $date_to) {
    $html .= '<div class="filters">';
    $html .= '<h3>Applied Filters:</h3>';
    if ($availability_filter) {
        $html .= '<p>Availability: ' . htmlspecialchars($availability_filter) . '</p>';
    }
    if ($date_from) {
        $html .= '<p>From Date: ' . htmlspecialchars($date_from) . '</p>';
    }
    if ($date_to) {
        $html .= '<p>To Date: ' . htmlspecialchars($date_to) . '</p>';
    }
    $html .= '</div>';
}

// Add table of applications
$html .= '
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Position</th>
            <th>Education</th>
            <th>Availability</th>
            <th>Applied Date</th>
        </tr>
    </thead>
    <tbody>
';

while ($row = $result->fetch_assoc()) {
    $html .= '<tr>';
    $html .= '<td>' . htmlspecialchars($row['full_name']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['job_position_applied']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['degree_obtained'] ?? 'N/A') . '</td>';
    $html .= '<td>' . htmlspecialchars($row['availability']) . '</td>';
    $html .= '<td>' . date('Y-m-d', strtotime($row['created_at'])) . '</td>';
    $html .= '</tr>';

    // Add detailed information
    $html .= '<tr><td colspan="5" class="applicant-details">';
    $html .= '<strong>Email:</strong> ' . htmlspecialchars($row['email']) . '<br>';
    $html .= '<strong>Phone:</strong> ' . htmlspecialchars($row['phone_number']) . '<br>';
    $html .= '<strong>Skills:</strong> ' . htmlspecialchars($row['skills'] ?: 'None listed') . '<br>';
    $html .= '<strong>Previous Employers:</strong> ' . htmlspecialchars($row['previous_employers'] ?: 'None listed');
    $html .= '</td></tr>';
}

$html .= '
    </tbody>
</table>
<div class="footer">
    Page {PAGE_NUM} of {PAGE_COUNT}
</div>
</body>
</html>';

// Initialize dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);

// Set paper size and orientation
$dompdf->setPaper('A4', 'portrait');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF
$dompdf->stream('job_applications_report_' . date('Y-m-d') . '.pdf', array('Attachment' => true));

// Close database connection
$conn->close();
