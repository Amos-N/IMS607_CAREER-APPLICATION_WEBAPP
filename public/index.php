<?php
require_once '../config/db_connect.php';

// Initialize search variables
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$position = isset($_GET['position']) ? trim($_GET['position']) : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Base query for main results
$query = "SELECT job_position_applied, availability, COUNT(*) as position_count 
          FROM applicants 
          WHERE 1=1";

// Base query for counting total distinct positions
$count_query = "SELECT COUNT(DISTINCT job_position_applied) as total 
                FROM applicants 
                WHERE 1=1";

$params = array();
$types = "";

// Add search conditions
if ($search) {
    $search_condition = " AND job_position_applied LIKE ?";
    $query .= $search_condition;
    $count_query .= $search_condition;
    $params[] = "%$search%";
    $types .= "s";
}

if ($type) {
    $type_condition = " AND availability = ?";
    $query .= $type_condition;
    $count_query .= $type_condition;
    $params[] = $type;
    $types .= "s";
}

// Add group by to main query
$query .= " GROUP BY job_position_applied, availability";
// Add order by and limit to main query only
$query .= " ORDER BY COUNT(*) DESC LIMIT ?, ?";

// Add pagination parameters to main query only
$main_params = $params;
$main_params[] = $offset;
$main_params[] = $records_per_page;
$main_types = $types . "ii";

// Get total count for pagination
$count_stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_records = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Execute main query
$stmt = $conn->prepare($query);
if (!empty($main_params)) {
    $stmt->bind_param($main_types, ...$main_params);
}
$stmt->execute();
$results = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Positions - Job Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50">
    <!-- Header -->
    <?php require_once '../includes/public_header.php'; ?>
    <!-- Hero Section -->
    <div class="relative bg-blue-600">
        <div class="absolute inset-0">
            <img class="w-full h-full object-cover" src="https://images.unsplash.com/photo-1521737711867-e3b97375f902?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1974&q=80" alt="Office">
            <div class="absolute inset-0 bg-blue-600 mix-blend-multiply"></div>
        </div>
        <div class="relative max-w-7xl mx-auto py-24 px-4 sm:py-32 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl">Find Your Dream Job</h1>
            <p class="mt-6 text-xl text-blue-100 max-w-3xl">
                Explore exciting career opportunities and take the next step in your professional journey.
            </p>

            <!-- Search Form -->
            <form action="" method="GET" class="mt-8 sm:flex">
                <div class="flex-1 min-w-0 space-y-3 sm:space-y-0 sm:space-x-3 sm:flex">
                    <label for="search" class="sr-only">Search positions</label>
                    <input type="text"
                        name="search"
                        id="search"
                        value="<?php echo htmlspecialchars($search); ?>"
                        class="block w-full px-4 py-3 rounded-md border-0 text-base text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-400"
                        placeholder="Search positions...">

                    <select name="type"
                        class="block w-full px-4 py-3 rounded-md border-0 text-base text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="">All Types</option>
                        <option value="Full-time" <?php echo $type === 'Full-time' ? 'selected' : ''; ?>>Full Time</option>
                        <option value="Part-time" <?php echo $type === 'Part-time' ? 'selected' : ''; ?>>Part Time</option>
                        <option value="Shift preferences" <?php echo $type === 'Shift preferences' ? 'selected' : ''; ?>>Shift Based</option>
                    </select>
                </div>
                <div class="mt-3 sm:mt-0 sm:ml-3">
                    <button type="submit"
                        class="block w-full px-6 py-3 rounded-md text-base font-medium text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-600">
                        Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Job Listings -->
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <?php if ($results->num_rows > 0): ?>
            <div class="grid gap-6 lg:grid-cols-2">
                <?php while ($row = $results->fetch_assoc()): ?>
                    <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <h2 class="text-xl font-semibold text-gray-900">
                                    <?php echo htmlspecialchars($row['job_position_applied']); ?>
                                </h2>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                    <?php echo $row['availability'] === 'Full-time' ?
                                        'bg-green-100 text-green-800' : ($row['availability'] === 'Part-time' ?
                                            'bg-blue-100 text-blue-800' :
                                            'bg-yellow-100 text-yellow-800'); ?>">
                                    <?php echo htmlspecialchars($row['availability']); ?>
                                </span>
                            </div>

                            <div class="mt-4 flex items-center justify-between">
                                <div class="text-sm text-gray-500">
                                    <span class="font-medium"><?php echo $row['position_count']; ?></span> positions available
                                </div>

                                <a href="apply.php?position=<?php echo urlencode($row['job_position_applied']); ?>"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                                    Apply Now
                                    <svg class="ml-2 -mr-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="mt-8 flex justify-center">
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo ($page - 1); ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($type); ?>"
                                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                Previous
                            </a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($type); ?>"
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium <?php echo $i === $page ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:bg-gray-50'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo ($page + 1); ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($type); ?>"
                                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                Next
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No positions found</h3>
                <p class="mt-1 text-sm text-gray-500">Try adjusting your search filters or check back later.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <?php require_once '../includes/footer.php'; ?>

    <script>
        // Add smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>

</html>