<?php
require_once '../../config/db_connect.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Get statistics for dashboard
$stats = [
    'total_applications' => 0,
    'recent_applications' => 0,
    'full_time_applications' => 0,
    'part_time_applications' => 0
];

// Get total applications
$result = $conn->query("SELECT COUNT(*) as total FROM applicants");
$stats['total_applications'] = $result->fetch_assoc()['total'];

// Get applications from last 7 days
$result = $conn->query("SELECT COUNT(*) as recent FROM applicants WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$stats['recent_applications'] = $result->fetch_assoc()['recent'];

// Get applications by availability
$result = $conn->query("SELECT availability, COUNT(*) as count FROM applicants GROUP BY availability");
while ($row = $result->fetch_assoc()) {
    if ($row['availability'] == 'Full-time') {
        $stats['full_time_applications'] = $row['count'];
    } elseif ($row['availability'] == 'Part-time') {
        $stats['part_time_applications'] = $row['count'];
    }
}

// Get recent applications
$recent_applications = [];
$query = "SELECT a.*, e.degree_obtained, e.school_name 
          FROM applicants a 
          LEFT JOIN education e ON a.applicant_id = e.applicant_id 
          ORDER BY a.created_at DESC LIMIT 5";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $recent_applications[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Job Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Heroicons -->
    <script src="https://unpkg.com/@heroicons/v2.0.18/24/outline/index.js"></script>
</head>

<body class="bg-gray-50">
    <!-- require from includes/header.php in base file -->
    <?php require_once '../../includes/header.php'; ?>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <!-- Total Applications -->
            <div class="bg-white overflow-hidden shadow-lg rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <div class="ml-5">
                            <p class="text-sm font-medium text-gray-500 truncate">Total Applications</p>
                            <p class="mt-1 text-xl font-semibold text-gray-900"><?php echo $stats['total_applications']; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Applications -->
            <div class="bg-white overflow-hidden shadow-lg rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5">
                            <p class="text-sm font-medium text-gray-500 truncate">Last 7 Days</p>
                            <p class="mt-1 text-xl font-semibold text-gray-900"><?php echo $stats['recent_applications']; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Full-time Applications -->
            <div class="bg-white overflow-hidden shadow-lg rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="ml-5">
                            <p class="text-sm font-medium text-gray-500 truncate">Full-time</p>
                            <p class="mt-1 text-xl font-semibold text-gray-900"><?php echo $stats['full_time_applications']; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Part-time Applications -->
            <div class="bg-white overflow-hidden shadow-lg rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5">
                            <p class="text-sm font-medium text-gray-500 truncate">Part-time</p>
                            <p class="mt-1 text-xl font-semibold text-gray-900"><?php echo $stats['part_time_applications']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mb-8">
            <div class="bg-white shadow-lg rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <a href="list.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                            View All Applications
                        </a>
                        <a href="search.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                            Search Applications
                        </a>
                        <a href="export_pdf.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700">
                            Export Report
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Applications Table -->
        <div class="bg-white shadow-lg rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Applications</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Education</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Applied</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($recent_applications as $application): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($application['full_name']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($application['job_position_applied']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($application['degree_obtained'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo date('M d, Y', strtotime($application['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="view.php?id=<?php echo $application['applicant_id']; ?>"
                                            class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                        <a href="edit.php?id=<?php echo $application['applicant_id']; ?>"
                                            class="text-green-600 hover:text-green-900">Edit</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- require from includes/footer.php in base file -->
    <?php require_once '../../includes/footer.php'; ?>
</body>

</html>