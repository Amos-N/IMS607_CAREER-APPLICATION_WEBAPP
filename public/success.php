<?php
require_once '../config/db_connect.php';

// Check if we have an application ID
$application_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$application_id) {
    header("Location: index.php");
    exit();
}

// Get application details
$query = "SELECT a.*, e.school_name, e.degree_obtained 
          FROM applicants a 
          LEFT JOIN education e ON a.applicant_id = e.applicant_id 
          WHERE a.applicant_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $application_id);
$stmt->execute();
$result = $stmt->get_result();
$application = $result->fetch_assoc();

// If no application found, redirect
if (!$application) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Submitted Successfully - Job Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Confetti effect
        function createConfetti() {
            const confettiCount = 200;
            const container = document.querySelector('#confetti-container');

            for (let i = 0; i < confettiCount; i++) {
                const confetti = document.createElement('div');
                confetti.classList.add('confetti');

                // Random position, color, and animation delay
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.animationDelay = Math.random() * 3 + 's';
                confetti.style.backgroundColor = `hsl(${Math.random() * 360}, 100%, 50%)`;

                container.appendChild(confetti);
            }
        }
    </script>
    <style>
        @keyframes confettiFall {
            0% {
                transform: translateY(-100vh) rotate(0deg);
                opacity: 1;
            }

            100% {
                transform: translateY(100vh) rotate(360deg);
                opacity: 0;
            }
        }

        .confetti {
            position: fixed;
            width: 10px;
            height: 10px;
            pointer-events: none;
            animation: confettiFall 3s linear forwards;
        }

        #confetti-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1000;
        }
    </style>
</head>

<body class="bg-gray-50" onload="createConfetti()">
    <?php require_once '../includes/public_header.php'; ?>
    <div id="confetti-container"></div>

    <div class="min-h-screen flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <!-- Success Message -->
            <div class="text-center mb-8">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100">
                    <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h2 class="mt-4 text-3xl font-extrabold text-gray-900">Application Submitted Successfully!</h2>
                <p class="mt-2 text-lg text-gray-600">
                    Thank you for applying. We've received your application and will review it shortly.
                </p>
            </div>

            <!-- Application Details -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Application Details
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        Please save this information for your reference.
                    </p>
                </div>
                <div class="border-t border-gray-200">
                    <dl>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Application ID</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <?php echo str_pad($application['applicant_id'], 6, '0', STR_PAD_LEFT); ?>
                            </dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <?php echo htmlspecialchars($application['full_name']); ?>
                            </dd>
                        </div>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Position Applied</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <?php echo htmlspecialchars($application['job_position_applied']); ?>
                            </dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Email Address</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <?php echo htmlspecialchars($application['email']); ?>
                            </dd>
                        </div>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Date Submitted</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <?php echo date('F j, Y g:i A', strtotime($application['created_at'])); ?>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Next Steps -->
            <div class="mt-8 bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Next Steps
                    </h3>
                    <div class="mt-4 text-sm text-gray-500">
                        <ul class="list-disc pl-5 space-y-2">
                            <li>You will receive a confirmation email shortly.</li>
                            <li>Our HR team will review your application within 5-7 business days.</li>
                            <li>If your profile matches our requirements, we will contact you for an interview.</li>
                            <li>Please ensure to check your email regularly, including spam folder.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 flex justify-center space-x-4">
                <a href="index.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                    Back to Job Listings
                </a>
                <button onclick="window.print()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Print Details
                </button>
            </div>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>
</body>

</html>