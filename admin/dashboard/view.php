<?php
require_once '../../config/db_connect.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: list.php");
    exit();
}

$applicant_id = (int)$_GET['id'];

// Get applicant details with joins to all related tables
$query = "SELECT 
    a.*,
    e.school_name, e.degree_obtained, e.relevant_courses_certifications,
    GROUP_CONCAT(DISTINCT eh.previous_employer, ' - ', eh.job_title SEPARATOR '||') as employment_history,
    GROUP_CONCAT(DISTINCT sq.skill SEPARATOR ', ') as skills,
    GROUP_CONCAT(DISTINCT sq.certification_license SEPARATOR ', ') as certifications,
    GROUP_CONCAT(DISTINCT r.reference_name, ' (', r.relationship, ')' SEPARATOR '||') as references_list
FROM applicants a
LEFT JOIN education e ON a.applicant_id = e.applicant_id
LEFT JOIN employment_history eh ON a.applicant_id = eh.applicant_id
LEFT JOIN skills_qualifications sq ON a.applicant_id = sq.applicant_id
LEFT JOIN `references` r ON a.applicant_id = r.applicant_id
WHERE a.applicant_id = ?
GROUP BY a.applicant_id";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $applicant_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: list.php");
    exit();
}

$applicant = $result->fetch_assoc();

// Set page title for header
$pageTitle = "View Application: " . htmlspecialchars($applicant['full_name']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Application - <?php echo htmlspecialchars($applicant['full_name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50">
    <?php require_once '../../includes/header.php'; ?>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Action Buttons -->
        <div class="mb-6 flex justify-end space-x-4">
            <a href="list.php"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                Back to List
            </a>
            <a href="edit.php?id=<?php echo $applicant_id; ?>"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                Edit Application
            </a>
            <button onclick="confirmDelete(<?php echo $applicant_id; ?>)"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                Delete Application
            </button>
        </div>

        <!-- Application Details -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <!-- Personal Information -->
            <div class="px-6 py-5 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Personal Information</h3>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($applicant['full_name']); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">IC Number</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($applicant['IC_number']); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($applicant['email']); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Phone Number</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($applicant['phone_number']); ?></dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Address</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?php echo nl2br(htmlspecialchars($applicant['address'])); ?></dd>
                    </div>
                </div>
            </div>

            <!-- Application Details -->
            <div class="px-6 py-5 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Application Details</h3>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Position Applied</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($applicant['job_position_applied']); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Availability</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php echo $applicant['availability'] === 'Full-time' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'; ?>">
                                <?php echo htmlspecialchars($applicant['availability']); ?>
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Asking Salary</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($applicant['asking_salary']); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Interview Availability</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?php echo nl2br(htmlspecialchars($applicant['interview_availability'])); ?></dd>
                    </div>
                </div>
            </div>

            <!-- Education -->
            <div class="px-6 py-5 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Education</h3>
                <div class="mt-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">School/University</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($applicant['school_name'] ?? 'N/A'); ?></dd>
                    </div>
                    <div class="mt-4">
                        <dt class="text-sm font-medium text-gray-500">Degree/Qualification</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($applicant['degree_obtained'] ?? 'N/A'); ?></dd>
                    </div>
                    <div class="mt-4">
                        <dt class="text-sm font-medium text-gray-500">Relevant Courses/Certifications</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?php echo nl2br(htmlspecialchars($applicant['relevant_courses_certifications'] ?? 'N/A')); ?></dd>
                    </div>
                </div>
            </div>

            <!-- Employment History -->
            <div class="px-6 py-5 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Employment History</h3>
                <div class="mt-4">
                    <?php
                    if (!empty($applicant['employment_history'])) {
                        $history = explode('||', $applicant['employment_history']);
                        foreach ($history as $entry) {
                            echo '<div class="mb-4 p-4 bg-gray-50 rounded-lg">';
                            echo '<p class="text-sm text-gray-900">' . htmlspecialchars($entry) . '</p>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p class="text-sm text-gray-500">No employment history provided</p>';
                    }
                    ?>
                </div>
            </div>

            <!-- Skills and Certifications -->
            <div class="px-6 py-5 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Skills and Certifications</h3>
                <div class="mt-4">
                    <dt class="text-sm font-medium text-gray-500">Skills</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <?php
                        if (!empty($applicant['skills'])) {
                            $skills = explode(', ', $applicant['skills']);
                            foreach ($skills as $skill) {
                                echo '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-2 mb-2">';
                                echo htmlspecialchars($skill);
                                echo '</span>';
                            }
                        } else {
                            echo 'No skills listed';
                        }
                        ?>
                    </dd>
                    <dt class="text-sm font-medium text-gray-500 mt-4">Certifications</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <?php echo !empty($applicant['certifications']) ? htmlspecialchars($applicant['certifications']) : 'No certifications listed'; ?>
                    </dd>
                </div>
            </div>

            <!-- References -->
            <div class="px-6 py-5">
                <h3 class="text-lg font-medium text-gray-900">References</h3>
                <div class="mt-4">
                    <?php
                    if (!empty($applicant['references_list'])) {
                        $references = explode('||', $applicant['references_list']);
                        foreach ($references as $reference) {
                            echo '<div class="mb-4 p-4 bg-gray-50 rounded-lg">';
                            echo '<p class="text-sm text-gray-900">' . htmlspecialchars($reference) . '</p>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p class="text-sm text-gray-500">No references provided</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </main>

    <?php require_once '../../includes/footer.php'; ?>

    <script>
        function confirmDelete(id) {
            if (confirm('Are you sure you want to delete this application? This action cannot be undone.')) {
                window.location.href = `delete.php?id=${id}`;
            }
        }
    </script>
</body>

</html>