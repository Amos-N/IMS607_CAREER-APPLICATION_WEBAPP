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
$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start transaction
        $conn->begin_transaction();

        // Update applicants table
        $update_applicant = $conn->prepare("
            UPDATE applicants SET 
                full_name = ?,
                address = ?,
                phone_number = ?,
                email = ?,
                IC_number = ?,
                job_position_applied = ?,
                availability = ?,
                asking_salary = ?,
                interview_availability = ?,
                additional_information = ?
            WHERE applicant_id = ?
        ");

        $update_applicant->bind_param(
            "ssssssssssi",
            $_POST['full_name'],
            $_POST['address'],
            $_POST['phone_number'],
            $_POST['email'],
            $_POST['IC_number'],
            $_POST['job_position_applied'],
            $_POST['availability'],
            $_POST['asking_salary'],
            $_POST['interview_availability'],
            $_POST['additional_information'],
            $applicant_id
        );
        $update_applicant->execute();

        // Update education table
        $update_education = $conn->prepare("
            UPDATE education SET 
                school_name = ?,
                degree_obtained = ?,
                relevant_courses_certifications = ?
            WHERE applicant_id = ?
        ");

        $update_education->bind_param(
            "sssi",
            $_POST['school_name'],
            $_POST['degree_obtained'],
            $_POST['relevant_courses_certifications'],
            $applicant_id
        );
        $update_education->execute();

        // Update skills (delete and insert new)
        $conn->query("DELETE FROM skills_qualifications WHERE applicant_id = $applicant_id");

        if (!empty($_POST['skills'])) {
            $skills = explode(',', $_POST['skills']);
            $insert_skill = $conn->prepare("
                INSERT INTO skills_qualifications (applicant_id, skill) 
                VALUES (?, ?)
            ");

            foreach ($skills as $skill) {
                $skill = trim($skill);
                if (!empty($skill)) {
                    $insert_skill->bind_param("is", $applicant_id, $skill);
                    $insert_skill->execute();
                }
            }
        }

        // Update certifications
        if (!empty($_POST['certifications'])) {
            $certs = explode(',', $_POST['certifications']);
            $insert_cert = $conn->prepare("
                INSERT INTO skills_qualifications (applicant_id, certification_license) 
                VALUES (?, ?)
            ");

            foreach ($certs as $cert) {
                $cert = trim($cert);
                if (!empty($cert)) {
                    $insert_cert->bind_param("is", $applicant_id, $cert);
                    $insert_cert->execute();
                }
            }
        }

        // Commit transaction
        $conn->commit();
        $success_message = "Application updated successfully!";
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $error_message = "Error updating application: " . $e->getMessage();
    }
}

// Get applicant details with joins to all related tables
$query = "SELECT 
    a.*,
    e.school_name, e.degree_obtained, e.relevant_courses_certifications,
    GROUP_CONCAT(DISTINCT sq.skill) as skills,
    GROUP_CONCAT(DISTINCT sq.certification_license) as certifications
FROM applicants a
LEFT JOIN education e ON a.applicant_id = e.applicant_id
LEFT JOIN skills_qualifications sq ON a.applicant_id = sq.applicant_id
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
$pageTitle = "Edit Application: " . htmlspecialchars($applicant['full_name']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Application - <?php echo htmlspecialchars($applicant['full_name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50">
    <?php require_once '../../includes/header.php'; ?>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <?php if ($error_message): ?>
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="bg-white shadow-lg rounded-lg overflow-hidden">
            <!-- Personal Information -->
            <div class="px-6 py-5 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Personal Information</h3>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" name="full_name"
                            value="<?php echo htmlspecialchars($applicant['full_name']); ?>"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">IC Number</label>
                        <input type="text" name="IC_number"
                            value="<?php echo htmlspecialchars($applicant['IC_number']); ?>"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email"
                            value="<?php echo htmlspecialchars($applicant['email']); ?>"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <input type="text" name="phone_number"
                            value="<?php echo htmlspecialchars($applicant['phone_number']); ?>"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Address</label>
                        <textarea name="address" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo htmlspecialchars($applicant['address']); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Application Details -->
            <div class="px-6 py-5 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Application Details</h3>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Position Applied</label>
                        <input type="text" name="job_position_applied"
                            value="<?php echo htmlspecialchars($applicant['job_position_applied']); ?>"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Availability</label>
                        <select name="availability"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="Full-time" <?php echo $applicant['availability'] === 'Full-time' ? 'selected' : ''; ?>>Full-time</option>
                            <option value="Part-time" <?php echo $applicant['availability'] === 'Part-time' ? 'selected' : ''; ?>>Part-time</option>
                            <option value="Shift preferences" <?php echo $applicant['availability'] === 'Shift preferences' ? 'selected' : ''; ?>>Shift preferences</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Asking Salary</label>
                        <input type="text" name="asking_salary"
                            value="<?php echo htmlspecialchars($applicant['asking_salary']); ?>"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Interview Availability</label>
                        <textarea name="interview_availability" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo htmlspecialchars($applicant['interview_availability']); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Education -->
            <div class="px-6 py-5 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Education</h3>
                <div class="mt-4 grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">School/University</label>
                        <input type="text" name="school_name"
                            value="<?php echo htmlspecialchars($applicant['school_name'] ?? ''); ?>"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Degree/Qualification</label>
                        <input type="text" name="degree_obtained"
                            value="<?php echo htmlspecialchars($applicant['degree_obtained'] ?? ''); ?>"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Relevant Courses/Certifications</label>
                        <textarea name="relevant_courses_certifications" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo htmlspecialchars($applicant['relevant_courses_certifications'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Skills and Certifications -->
            <div class="px-6 py-5 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Skills and Certifications</h3>
                <div class="mt-4 grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Skills (comma-separated)</label>
                        <input type="text" name="skills"
                            value="<?php echo htmlspecialchars($applicant['skills'] ?? ''); ?>"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Certifications (comma-separated)</label>
                        <input type="text" name="certifications"
                            value="<?php echo htmlspecialchars($applicant['certifications'] ?? ''); ?>"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="px-6 py-5 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Additional Information</h3>
                <div class="mt-4">
                    <textarea name="additional_information" rows="4"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo htmlspecialchars($applicant['additional_information']); ?></textarea>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-6 py-5 bg-gray-50 flex justify-end space-x-3">
                <a href="view.php?id=<?php echo $applicant_id; ?>"
                    class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancel
                </a>
                <button type="submit"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Save Changes
                </button>
            </div>
        </form>
    </main>

    <?php require_once '../../includes/footer.php'; ?>

    <script>
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const requiredFields = [
                'full_name',
                'email',
                'phone_number',
                'IC_number',
                'job_position_applied'
            ];

            let isValid = true;
            let firstInvalidField = null;

            requiredFields.forEach(field => {
                const input = this.querySelector(`[name="${field}"]`);
                if (!input.value.trim()) {
                    isValid = false;
                    input.classList.add('border-red-500');
                    if (!firstInvalidField) firstInvalidField = input;
                } else {
                    input.classList.remove('border-red-500');
                }
            });

            // Email validation
            const emailField = this.querySelector('[name="email"]');
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(emailField.value.trim())) {
                isValid = false;
                emailField.classList.add('border-red-500');
                if (!firstInvalidField) firstInvalidField = emailField;
            }

            // Phone number validation (basic)
            const phoneField = this.querySelector('[name="phone_number"]');
            const phonePattern = /^[0-9\-\+\(\)\s]{8,}$/;
            if (!phonePattern.test(phoneField.value.trim())) {
                isValid = false;
                phoneField.classList.add('border-red-500');
                if (!firstInvalidField) firstInvalidField = phoneField;
            }

            if (!isValid) {
                e.preventDefault();
                firstInvalidField.focus();
                alert('Please fill in all required fields correctly.');
            }
        });

        // Real-time validation feedback
        document.querySelectorAll('input, textarea').forEach(input => {
            input.addEventListener('blur', function() {
                if (this.hasAttribute('required') && !this.value.trim()) {
                    this.classList.add('border-red-500');
                } else {
                    this.classList.remove('border-red-500');
                }
            });

            input.addEventListener('input', function() {
                if (this.classList.contains('border-red-500')) {
                    if (this.value.trim()) {
                        this.classList.remove('border-red-500');
                    }
                }
            });
        });

        // Format skills and certifications as tags
        function initializeTagInput(inputElement) {
            const container = document.createElement('div');
            container.className = 'flex flex-wrap gap-2 mt-2';
            inputElement.parentNode.insertBefore(container, inputElement.nextSibling);

            function updateTags() {
                const values = inputElement.value.split(',').filter(tag => tag.trim());
                container.innerHTML = '';
                values.forEach(value => {
                    const tag = document.createElement('span');
                    tag.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800';
                    tag.textContent = value.trim();
                    container.appendChild(tag);
                });
            }

            inputElement.addEventListener('input', updateTags);
            updateTags();
        }

        // Initialize tag inputs
        initializeTagInput(document.querySelector('[name="skills"]'));
        initializeTagInput(document.querySelector('[name="certifications"]'));
    </script>
</body>

</html>