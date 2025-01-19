<?php
require_once '../../config/db_connect.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Get total number of records
$total_query = "SELECT COUNT(*) as count FROM applicants";
$total_result = $conn->query($total_query);
$total_records = $total_result->fetch_assoc()['count'];
$total_pages = ceil($total_records / $records_per_page);

// Get applications with pagination
$query = "SELECT a.*, e.degree_obtained, e.school_name 
          FROM applicants a 
          LEFT JOIN education e ON a.applicant_id = e.applicant_id 
          ORDER BY a.created_at DESC 
          LIMIT $offset, $records_per_page";
$result = $conn->query($query);

// Set page title for header
$pageTitle = "All Applications";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Applications - Job Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50">
    <?php require_once '../../includes/header.php'; ?>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Search and Filter Section -->
        <div class="mb-6 bg-white shadow-lg rounded-lg p-4">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <form action="search.php" method="GET" class="w-full md:w-auto">
                    <div class="relative">
                        <input type="text" name="q"
                            class="w-full md:w-80 pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:border-blue-500"
                            placeholder="Search applications...">
                        <div class="absolute left-3 top-2.5">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </form>
                <a href="export_pdf.php"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                    Export to PDF
                </a>
            </div>
        </div>

        <!-- Applications Table -->
        <div class="bg-white shadow-lg rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Education</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Availability</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Applied</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($row['full_name']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars($row['email']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($row['job_position_applied']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?php echo htmlspecialchars($row['degree_obtained'] ?? 'N/A'); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars($row['school_name'] ?? ''); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php echo $row['availability'] === 'Full-time' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'; ?>">
                                        <?php echo htmlspecialchars($row['availability']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="view.php?id=<?php echo $row['applicant_id']; ?>"
                                        class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                    <a href="edit.php?id=<?php echo $row['applicant_id']; ?>"
                                        class="text-green-600 hover:text-green-900 mr-3">Edit</a>
                                    <button onclick="confirmDelete(<?php echo $row['applicant_id']; ?>)"
                                        class="text-red-600 hover:text-red-900">Delete</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="px-6 py-4 bg-gray-50">
                    <nav class="flex items-center justify-between">
                        <div class="flex-1 flex justify-between sm:justify-end">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo ($page - 1); ?>"
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Previous
                                </a>
                            <?php endif; ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo ($page + 1); ?>"
                                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Next
                                </a>
                            <?php endif; ?>
                        </div>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php require_once '../../includes/footer.php'; ?>

    <script>
        function confirmDelete(id) {
            if (confirm('Are you sure you want to delete this application?')) {
                window.location.href = `delete.php?id=${id}`;
            }
        }
    </script>
</body>

</html>