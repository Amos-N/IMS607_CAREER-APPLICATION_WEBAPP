<?php
require_once '../config/db_connect.php';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form data
    $required_fields = ['full_name', 'email', 'phone_number', 'job_position_applied'];
    $missing_fields = [];

    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }

    if (!empty($missing_fields)) {
        $error = 'Please fill in all required fields: ' . implode(', ', $missing_fields);
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        try {
            // Start transaction
            $conn->begin_transaction();

            // Insert into applicants table
            $stmt = $conn->prepare("INSERT INTO applicants (full_name, address, phone_number, email, IC_number, 
                job_position_applied, availability, asking_salary, interview_availability, additional_information) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->bind_param(
                "ssssssssss",
                $_POST['full_name'],
                $_POST['address'],
                $_POST['phone_number'],
                $_POST['email'],
                $_POST['IC_number'],
                $_POST['job_position_applied'],
                $_POST['availability'],
                $_POST['asking_salary'],
                $_POST['interview_availability'],
                $_POST['additional_information']
            );

            $stmt->execute();
            $applicant_id = $conn->insert_id;

            // Insert education details
            if (!empty($_POST['school_name'])) {
                $stmt = $conn->prepare("INSERT INTO education (applicant_id, school_name, degree_obtained, 
                    relevant_courses_certifications) VALUES (?, ?, ?, ?)");

                $stmt->bind_param(
                    "isss",
                    $applicant_id,
                    $_POST['school_name'],
                    $_POST['degree_obtained'],
                    $_POST['relevant_courses']
                );

                $stmt->execute();
            }

            // Insert employment history
            if (!empty($_POST['previous_employer'])) {
                $stmt = $conn->prepare("INSERT INTO employment_history (applicant_id, previous_employer, 
                    job_title, employment_duration, reason_for_leaving) VALUES (?, ?, ?, ?, ?)");

                $stmt->bind_param(
                    "issss",
                    $applicant_id,
                    $_POST['previous_employer'],
                    $_POST['job_title'],
                    $_POST['employment_duration'],
                    $_POST['reason_for_leaving']
                );

                $stmt->execute();
            }

            // Insert skills
            if (!empty($_POST['skills'])) {
                $stmt = $conn->prepare("INSERT INTO skills_qualifications (applicant_id, skill, 
                    certification_license) VALUES (?, ?, ?)");

                $stmt->bind_param(
                    "iss",
                    $applicant_id,
                    $_POST['skills'],
                    $_POST['certifications']
                );

                $stmt->execute();
            }

            // Insert references
            if (!empty($_POST['reference_name'])) {
                $stmt = $conn->prepare("INSERT INTO references (applicant_id, reference_name, 
                    reference_contact, relationship, duration_of_relationship) VALUES (?, ?, ?, ?, ?)");

                $stmt->bind_param(
                    "issss",
                    $applicant_id,
                    $_POST['reference_name'],
                    $_POST['reference_contact'],
                    $_POST['relationship'],
                    $_POST['duration_of_relationship']
                );

                $stmt->execute();
            }

            // Commit transaction
            $conn->commit();
            $success = true;

            // Redirect to success page
            header("Location: success.php?id=" . $applicant_id);
            exit();
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $error = "An error occurred while submitting your application. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Application Form - Job Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs" defer></script>
</head>

<body class="bg-gray-50">
    <?php require_once '../includes/public_header.php'; ?>

    <div class="min-h-screen py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Form Header -->
            <div class="text-center mb-12">
                <h1 class="text-3xl font-bold text-gray-900">Job Application Form</h1>
                <p class="mt-2 text-gray-600">Please fill out all required fields marked with an asterisk (*)</p>
            </div>

            <?php if ($error): ?>
                <div class="mb-8 bg-red-50 border-l-4 border-red-400 p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700"><?php echo htmlspecialchars($error); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="bg-white shadow-xl rounded-lg px-8 pt-6 pb-8 mb-4" x-data="{ step: 1 }">
                <!-- Progress Bar -->
                <div class="mb-8">
                    <div class="flex justify-between mb-2">
                        <span x-bind:class="{ 'text-blue-600 font-medium': step >= 1 }">Personal Info</span>
                        <span x-bind:class="{ 'text-blue-600 font-medium': step >= 2 }">Education & Experience</span>
                        <span x-bind:class="{ 'text-blue-600 font-medium': step >= 3 }">Additional Info</span>
                    </div>
                    <div class="overflow-hidden h-2 text-xs flex rounded bg-blue-100">
                        <div x-bind:style="'width: ' + ((step/3)*100) + '%'"
                            class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-600 transition-all duration-500">
                        </div>
                    </div>
                </div>

                <!-- Step 1: Personal Information -->
                <div x-show="step === 1">
                    <h2 class="text-xl font-semibold mb-6">Personal Information</h2>

                    <div class="grid grid-cols-1 gap-6 mb-6">
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2" for="full_name">
                                Full Name *
                            </label>
                            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                id="full_name" type="text" name="full_name" required>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="email">
                                    Email Address *
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    id="email" type="email" name="email" required>
                            </div>

                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="phone_number">
                                    Phone Number *
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    id="phone_number" type="tel" name="phone_number" required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2" for="IC_number">
                                IC Number
                            </label>
                            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                id="IC_number" type="text" name="IC_number">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2" for="address">
                                Address
                            </label>
                            <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                id="address" name="address" rows="3"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Education and Experience -->
                <div x-show="step === 2">
                    <h2 class="text-xl font-semibold mb-6">Education and Experience</h2>

                    <!-- Education -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Education</h3>
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="school_name">
                                    School/University Name
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    id="school_name" type="text" name="school_name">
                            </div>

                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="degree_obtained">
                                    Degree/Certification
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    id="degree_obtained" type="text" name="degree_obtained">
                            </div>

                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="relevant_courses">
                                    Relevant Courses/Certifications
                                </label>
                                <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    id="relevant_courses" name="relevant_courses" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Work Experience -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Work Experience</h3>
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="previous_employer">
                                    Previous Employer
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    id="previous_employer" type="text" name="previous_employer">
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="job_title">
                                        Job Title
                                    </label>
                                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                        id="job_title" type="text" name="job_title">
                                </div>

                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="employment_duration">
                                        Duration
                                    </label>
                                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                        id="employment_duration" type="text" name="employment_duration" placeholder="e.g., 2 years">
                                </div>
                            </div>

                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="reason_for_leaving">
                                    Reason for Leaving
                                </label>
                                <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    id="reason_for_leaving" name="reason_for_leaving" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Additional Information -->
                <div x-show="step === 3">
                    <h2 class="text-xl font-semibold mb-6">Additional Information</h2>

                    <!-- Position Details -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Position Details</h3>
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="job_position_applied">
                                    Position Applied For *
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    id="job_position_applied" type="text" name="job_position_applied" required>
                            </div>

                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="availability">
                                    Availability
                                </label>
                                <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    id="availability" name="availability">
                                    <option value="Full-time">Full-time</option>
                                    <option value="Part-time">Part-time</option>
                                    <option value="Shift preferences">Shift preferences</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="asking_salary">
                                    Expected Salary
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    id="asking_salary" type="text" name="asking_salary" placeholder="e.g., RM 3000">
                            </div>
                        </div>
                    </div>

                    <!-- Skills and Qualifications -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Skills and Qualifications</h3>
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="skills">
                                    Skills
                                </label>
                                <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    id="skills" name="skills" rows="3" placeholder="List your relevant skills"></textarea>
                            </div>

                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="certifications">
                                    Certifications/Licenses
                                </label>
                                <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    id="certifications" name="certifications" rows="3" placeholder="List any relevant certifications"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- References -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">References</h3>
                        <div class="grid grid-cols-1 gap-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="reference_name">
                                        Reference Name
                                    </label>
                                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                        id="reference_name" type="text" name="reference_name">
                                </div>

                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="reference_contact">
                                        Reference Contact
                                    </label>
                                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                        id="reference_contact" type="text" name="reference_contact">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="relationship">
                                        Relationship
                                    </label>
                                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                        id="relationship" type="text" name="relationship">
                                </div>

                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="duration_of_relationship">
                                        Duration of Relationship
                                    </label>
                                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                        id="duration_of_relationship" type="text" name="duration_of_relationship" placeholder="e.g., 3 years">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="additional_information">
                            Additional Information
                        </label>
                        <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="additional_information" name="additional_information" rows="4"
                            placeholder="Any additional information you'd like to share"></textarea>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="mt-8 flex justify-between">
                    <button type="button"
                        x-show="step > 1"
                        @click="step--"
                        class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Previous
                    </button>

                    <button type="button"
                        x-show="step < 3"
                        @click="step++"
                        class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Next
                    </button>

                    <button type="submit"
                        x-show="step === 3"
                        class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Submit Application
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>

    <script>
        // Client-side validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const requiredFields = ['full_name', 'email', 'phone_number', 'job_position_applied'];
            let isValid = true;

            requiredFields.forEach(field => {
                const input = document.getElementById(field);
                if (!input.value.trim()) {
                    isValid = false;
                    input.classList.add('border-red-500');
                } else {
                    input.classList.remove('border-red-500');
                }
            });

            // Email validation
            const email = document.getElementById('email');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email.value.trim())) {
                isValid = false;
                email.classList.add('border-red-500');
            }

            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields correctly.');
            }
        });
    </script>
</body>

</html>