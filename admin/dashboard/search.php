<?php
require_once '../../config/db_connect.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Initialize variables
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$availability_filter = isset($_GET['availability']) ? $_GET['availability'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Base query
$base_query = "
    SELECT 
        a.*,
        e.school_name,
        e.degree_obtained,
        GROUP_CONCAT(DISTINCT sq.skill SEPARATOR ', ') as skills
    FROM applicants a
    LEFT JOIN education e ON a.applicant_id = e.applicant_id
    LEFT JOIN skills_qualifications sq ON a.applicant_id = sq.applicant_id
    WHERE 1=1
";

$count_query = "SELECT COUNT(DISTINCT a.applicant_id) as total FROM applicants a 
                LEFT JOIN education e ON a.applicant_id = e.applicant_id
                LEFT JOIN skills_qualifications sq ON a.applicant_id = sq.applicant_id
                WHERE 1=1";

$params = array();
$param_types = "";

// Add search conditions
if ($search_query) {
    $search_condition = " AND (
        a.full_name LIKE ? OR 
        a.email LIKE ? OR 
        a.job_position_applied LIKE ? OR 
        e.school_name LIKE ? OR 
        sq.skill LIKE ?
    )";
    $base_query .= $search_condition;
    $count_query .= $search_condition;
    $search_param = "%$search_query%";
    $params = array_merge($params, array($search_param, $search_param, $search_param, $search_param, $search_param));
    $param_types .= "sssss";
}

// Add availability filter
if ($availability_filter) {
    $base_query .= " AND a.availability = ?";
    $count_query .= " AND a.availability = ?";
    $params[] = $availability_filter;
    $param_types .= "s";
}

// Add date range filter
if ($date_from) {
    $base_query .= " AND DATE(a.created_at) >= ?";
    $count_query .= " AND DATE(a.created_at) >= ?";
    $params[] = $date_from;
    $param_types .= "s";
}

if ($date_to) {
    $base_query .= " AND DATE(a.created_at) <= ?";
    $count_query .= " AND DATE(a.created_at) <= ?";
    $params[] = $date_to;
    $param_types .= "s";
}

// Group by and order
$base_query .= " GROUP BY a.applicant_id ORDER BY a.created_at DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $records_per_page;
$param_types .= "ii";
// Get total records for pagination
$count_stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $temp_params = array_slice($params, 0, -2); // Remove LIMIT parameters
    $temp_types = substr($param_types, 0, -2);
    if (!empty($temp_params) && !empty($temp_types)) {  // Only bind if we have parameters
        $count_stmt->bind_param($temp_types, ...$temp_params);
    }
}
$count_stmt->execute();
$total_result = $count_stmt->get_result();
$total_records = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get search results
$stmt = $conn->prepare($base_query);
if (!empty($params) && !empty($param_types)) {  // Only bind if we have parameters
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$results = $stmt->get_result();

// Set page title for header
$pageTitle = "Search Applications";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Applications - Job Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Include datepicker CSS and JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>

<body class="bg-gray-50">
    <?php require_once '../../includes/header.php'; ?>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Search Form -->
        <div class="bg-white shadow-lg rounded-lg mb-6">
            <div class="px-6 py-5">
                <form action="" method="GET" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Search Input -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Search</label>
                            <input type="text" name="q" value="<?php echo htmlspecialchars($search_query); ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Name, email, position...">
                        </div>

                        <!-- Availability Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Availability</label>
                            <select name="availability"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">All</option>
                                <option value="Full-time" <?php echo $availability_filter === 'Full-time' ? 'selected' : ''; ?>>Full-time</option>
                                <option value="Part-time" <?php echo $availability_filter === 'Part-time' ? 'selected' : ''; ?>>Part-time</option>
                                <option value="Shift preferences" <?php echo $availability_filter === 'Shift preferences' ? 'selected' : ''; ?>>Shift preferences</option>
                            </select>
                        </div>

                        <!-- Date Range -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">From Date</label>
                            <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 datepicker">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">To Date</label>
                            <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 datepicker">
                        </div>
                    </div>

                    <div class="flex justify-between items-center">
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                            Search
                        </button>
                        <?php if ($search_query || $availability_filter || $date_from || $date_to): ?>
                            <a href="search.php"
                                class="text-sm text-gray-500 hover:text-gray-700">
                                Clear Filters
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Search Results -->
        <?php if ($total_records > 0): ?>
            <div class="bg-white shadow-lg rounded-lg">
                <div class="px-6 py-5 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        Search Results (<?php echo $total_records; ?> found)
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Education</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Skills</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Applied</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($row = $results->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($row['full_name']); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars($row['email']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <?php echo htmlspecialchars($row['job_position_applied']); ?>
                                        </div>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo $row['availability'] === 'Full-time' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'; ?>">
                                            <?php echo htmlspecialchars($row['availability']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <?php echo htmlspecialchars($row['degree_obtained'] ?? 'N/A'); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars($row['school_name'] ?? ''); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            <?php
                                            if (!empty($row['skills'])) {
                                                $skills = explode(', ', $row['skills']);
                                                foreach ($skills as $skill) {
                                                    echo '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 mr-1 mb-1">';
                                                    echo htmlspecialchars($skill);
                                                    echo '</span>';
                                                }
                                            } else {
                                                echo 'No skills listed';
                                            }
                                            ?>
                                        </div>
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
                                <?php
                                $query_params = http_build_query(array_filter([
                                    'q' => $search_query,
                                    'availability' => $availability_filter,
                                    'date_from' => $date_from,
                                    'date_to' => $date_to
                                ]));
                                ?>

                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo ($page - 1); ?>&<?php echo $query_params; ?>"
                                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        Previous
                                    </a>
                                <?php endif; ?>

                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo ($page + 1); ?>&<?php echo $query_params; ?>"
                                        class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        Next
                                    </a>
                                <?php endif; ?>
                            </div>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="bg-white shadow-lg rounded-lg">
                <div class="px-6 py-12 text-center">
                    <div class="text-gray-500">
                        <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <h3 class="mt-2 text-lg font-medium text-gray-900">No results found</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Try adjusting your search or filter criteria to find what you're looking for.
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <?php require_once '../../includes/footer.php'; ?>

    <script>
        // Initialize datepicker
        flatpickr(".datepicker", {
            dateFormat: "Y-m-d",
            allowInput: true
        });

        // Confirm delete
        function confirmDelete(id) {
            if (confirm('Are you sure you want to delete this application?')) {
                window.location.href = `delete.php?id=${id}`;
            }
        }

        // Handle form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const searchInput = this.querySelector('input[name="q"]');
            const dateFrom = this.querySelector('input[name="date_from"]');
            const dateTo = this.querySelector('input[name="date_to"]');

            // Validate date range
            if (dateFrom.value && dateTo.value) {
                if (new Date(dateFrom.value) > new Date(dateTo.value)) {
                    e.preventDefault();
                    alert('From date cannot be later than To date');
                    return;
                }
            }

            // Trim whitespace from search input
            if (searchInput.value) {
                searchInput.value = searchInput.value.trim();
            }
        });

        // Highlight search terms in results
        function highlightSearchTerm() {
            const searchTerm = '<?php echo addslashes($search_query); ?>';
            if (!searchTerm) return;

            const textNodes = document.evaluate(
                "//td//text()",
                document,
                null,
                XPathResult.UNORDERED_NODE_SNAPSHOT_TYPE,
                null
            );

            for (let i = 0; i < textNodes.snapshotLength; i++) {
                const node = textNodes.snapshotItem(i);
                if (node.parentElement.tagName !== 'SCRIPT') {
                    const text = node.textContent;
                    const regex = new RegExp(`(${searchTerm})`, 'gi');
                    if (regex.test(text)) {
                        const span = document.createElement('span');
                        span.innerHTML = text.replace(regex, '<mark class="bg-yellow-200">$1</mark>');
                        node.parentNode.replaceChild(span, node);
                    }
                }
            }
        }

        // Call highlight function when page loads
        if (window.location.search.includes('q=')) {
            highlightSearchTerm();
        }
    </script>
</body>

</html>