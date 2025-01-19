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

// Verify the applicant exists before deletion
$check_query = "SELECT full_name FROM applicants WHERE applicant_id = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("i", $applicant_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: list.php");
    exit();
}

$applicant = $result->fetch_assoc();

try {
    // Start transaction
    $conn->begin_transaction();

    // Delete records from all related tables in correct order
    // (respecting foreign key constraints)

    // 1. Delete references
    $delete_references = $conn->prepare("DELETE FROM `references` WHERE applicant_id = ?");
    $delete_references->bind_param("i", $applicant_id);
    $delete_references->execute();

    // 2. Delete skills and qualifications
    $delete_skills = $conn->prepare("DELETE FROM skills_qualifications WHERE applicant_id = ?");
    $delete_skills->bind_param("i", $applicant_id);
    $delete_skills->execute();

    // 3. Delete employment history
    $delete_employment = $conn->prepare("DELETE FROM employment_history WHERE applicant_id = ?");
    $delete_employment->bind_param("i", $applicant_id);
    $delete_employment->execute();

    // 4. Delete education records
    $delete_education = $conn->prepare("DELETE FROM education WHERE applicant_id = ?");
    $delete_education->bind_param("i", $applicant_id);
    $delete_education->execute();

    // 5. Finally, delete the applicant record
    $delete_applicant = $conn->prepare("DELETE FROM applicants WHERE applicant_id = ?");
    $delete_applicant->bind_param("i", $applicant_id);
    $delete_applicant->execute();

    // Commit transaction
    $conn->commit();

    // Set success message
    $success_message = "Application successfully deleted.";
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    $error_message = "Error deleting application: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Application - Job Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50">
    <?php require_once '../../includes/header.php'; ?>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="px-6 py-5">
                <?php if ($error_message): ?>
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                        <strong class="font-bold">Error!</strong>
                        <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
                        <div class="mt-4">
                            <a href="list.php"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                                Return to List
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($success_message): ?>
                    <div class="text-center">
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                            <strong class="font-bold">Success!</strong>
                            <span class="block sm:inline"><?php echo htmlspecialchars($success_message); ?></span>
                        </div>
                        <div class="mt-4">
                            <p class="text-gray-600 mb-4">You will be redirected to the applications list in <span id="countdown">5</span> seconds.</p>
                            <a href="list.php"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                Return to List Now
                            </a>
                        </div>
                    </div>

                    <script>
                        // Countdown and redirect
                        let seconds = 5;
                        const countdownElement = document.getElementById('countdown');

                        const countdown = setInterval(function() {
                            seconds--;
                            countdownElement.textContent = seconds;

                            if (seconds <= 0) {
                                clearInterval(countdown);
                                window.location.href = 'list.php';
                            }
                        }, 1000);
                    </script>
                <?php else: ?>
                    <div class="text-center">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Delete Application</h2>
                        <p class="text-gray-600 mb-4">
                            Are you sure you want to delete the application for
                            <strong><?php echo htmlspecialchars($applicant['full_name']); ?></strong>?
                        </p>
                        <p class="text-red-600 mb-6">This action cannot be undone.</p>

                        <div class="flex justify-center space-x-4">
                            <a href="view.php?id=<?php echo $applicant_id; ?>"
                                class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Cancel
                            </a>
                            <form method="POST" class="inline-block">
                                <input type="hidden" name="confirm_delete" value="1">
                                <button type="submit"
                                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                                    Confirm Delete
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php require_once '../../includes/footer.php'; ?>
</body>

</html>