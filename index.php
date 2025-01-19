<?php
session_start();
$isAdmin = isset($_SESSION['admin_id']);
$baseUrl = '/career'; // Adjust this according to your setup
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Job Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        }

        .hero-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.05'/%3E%3C/svg%3E");
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-20 items-center justify-between">
                <!-- Logo and Brand -->
                <div class="flex items-center">
                    <a href="<?php echo $baseUrl; ?>" class="flex items-center space-x-2">
                        <svg class="h-10 w-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span class="text-2xl font-bold text-gray-900">Job Portal</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="flex items-center space-x-6">
                    <?php if ($isAdmin): ?>
                        <a href="<?php echo $baseUrl; ?>/admin/dashboard/index.php"
                            class="text-gray-700 hover:text-blue-600 font-medium transition duration-150 ease-in-out">
                            Admin Dashboard
                        </a>
                    <?php else: ?>
                        <a href="<?php echo $baseUrl; ?>/admin/auth/login.php"
                            class="text-gray-700 hover:text-blue-600 font-medium transition duration-150 ease-in-out">
                            Admin Login
                        </a>
                    <?php endif; ?>

                    <a href="<?php echo $baseUrl; ?>/public/apply.php"
                        class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-full text-white bg-blue-600 hover:bg-blue-700 transition duration-150 ease-in-out shadow-md hover:shadow-lg">
                        Apply Now
                        <svg class="ml-2 -mr-1 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative hero-pattern bg-blue-900 overflow-hidden">
        <div class="absolute inset-0 gradient-bg opacity-90"></div>
        <div class="relative">
            <div class="mx-auto max-w-7xl px-6 py-24 sm:py-32 lg:flex lg:h-screen lg:items-center lg:py-40 lg:px-8">
                <div class="max-w-2xl lg:max-w-xl">
                    <h1 class="text-4xl font-bold tracking-tight text-white sm:text-6xl lg:text-7xl">
                        Find Your Dream
                        <span class="block text-blue-400">Career Today</span>
                    </h1>
                    <p class="mt-6 text-lg leading-8 text-gray-300">
                        Take the next step in your career journey. We connect talented professionals
                        with outstanding opportunities at leading companies.
                    </p>
                    <div class="mt-10 flex items-center gap-x-6">
                        <a href="<?php echo $baseUrl; ?>/public/apply.php"
                            class="transform transition-all duration-150 ease-in-out hover:scale-105 inline-flex items-center px-8 py-4 border border-transparent text-base font-semibold rounded-full text-blue-600 bg-white hover:bg-gray-100 shadow-lg hover:shadow-xl">
                            Submit Your Application
                            <svg class="ml-2 -mr-1 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="bg-white py-24 sm:py-32">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-base font-semibold leading-7 text-blue-600 uppercase tracking-wide">Why Choose Us</h2>
                <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                    Your Success Is Our Priority
                </p>
                <p class="mt-6 text-lg leading-8 text-gray-600">
                    We provide the tools and support you need to take your career to the next level.
                </p>
            </div>
            <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-none">
                <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-16 lg:max-w-none lg:grid-cols-3">
                    <!-- Feature Cards -->
                    <div class="relative group">
                        <div class="rounded-2xl bg-white p-8 shadow-lg ring-1 ring-gray-200 transition-all duration-200 ease-in-out group-hover:shadow-xl group-hover:ring-blue-200">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600 group-hover:bg-blue-700">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <h3 class="mt-6 text-lg font-semibold leading-8 text-gray-900">Secure Process</h3>
                            <p class="mt-2 text-base leading-7 text-gray-600">
                                Your information is protected with enterprise-grade security measures.
                            </p>
                        </div>
                    </div>

                    <div class="relative group">
                        <div class="rounded-2xl bg-white p-8 shadow-lg ring-1 ring-gray-200 transition-all duration-200 ease-in-out group-hover:shadow-xl group-hover:ring-blue-200">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600 group-hover:bg-blue-700">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                            </div>
                            <h3 class="mt-6 text-lg font-semibold leading-8 text-gray-900">Easy Tracking</h3>
                            <p class="mt-2 text-base leading-7 text-gray-600">
                                Monitor your application status in real-time through our intuitive dashboard.
                            </p>
                        </div>
                    </div>

                    <div class="relative group">
                        <div class="rounded-2xl bg-white p-8 shadow-lg ring-1 ring-gray-200 transition-all duration-200 ease-in-out group-hover:shadow-xl group-hover:ring-blue-200">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600 group-hover:bg-blue-700">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <h3 class="mt-6 text-lg font-semibold leading-8 text-gray-900">Expert Support</h3>
                            <p class="mt-2 text-base leading-7 text-gray-600">
                                Get personalized assistance from our dedicated support team throughout your journey.
                            </p>
                        </div>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <!-- Statistics Section -->
    <div class="bg-blue-900 py-24 sm:py-32">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Stat 1 -->
                <div class="text-center">
                    <h4 class="text-4xl font-bold text-white">5000+</h4>
                    <p class="mt-2 text-sm font-semibold text-blue-200">Jobs Posted</p>
                </div>

                <!-- Stat 2 -->
                <div class="text-center">
                    <h4 class="text-4xl font-bold text-white">10K+</h4>
                    <p class="mt-2 text-sm font-semibold text-blue-200">Active Users</p>
                </div>

                <!-- Stat 3 -->
                <div class="text-center">
                    <h4 class="text-4xl font-bold text-white">98%</h4>
                    <p class="mt-2 text-sm font-semibold text-blue-200">Success Rate</p>
                </div>

                <!-- Stat 4 -->
                <div class="text-center">
                    <h4 class="text-4xl font-bold text-white">24/7</h4>
                    <p class="mt-2 text-sm font-semibold text-blue-200">Support Available</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action Section -->
    <div class="bg-white">
        <div class="mx-auto max-w-7xl py-24 sm:px-6 sm:py-32 lg:px-8">
            <div class="relative isolate overflow-hidden bg-blue-600 px-6 py-24 shadow-2xl sm:rounded-3xl sm:px-24">
                <div class="absolute -top-24 right-0 -z-10 transform-gpu blur-3xl" aria-hidden="true">
                    <div class="aspect-[1404/767] w-[87.75rem] bg-gradient-to-r from-blue-400 to-blue-800 opacity-25"></div>
                </div>
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                        Ready to Start Your Journey?
                    </h2>
                    <p class="mx-auto mt-6 max-w-xl text-lg leading-8 text-blue-100">
                        Join thousands of professionals who have found their dream jobs through our platform.
                    </p>
                    <div class="mt-10 flex items-center justify-center gap-x-6">
                        <a href="<?php echo $baseUrl; ?>/public/apply.php"
                            class="rounded-xl bg-white px-8 py-4 text-lg font-semibold text-blue-600 shadow-sm hover:bg-blue-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white transition duration-150 ease-in-out">
                            Apply Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900">
        <div class="mx-auto max-w-7xl px-6 py-12 md:flex md:items-center md:justify-between lg:px-8">
            <div class="flex justify-center space-x-6 md:order-2">
                <!-- Social Links -->
                <a href="#" class="text-gray-400 hover:text-gray-300 transition duration-150 ease-in-out">
                    <span class="sr-only">LinkedIn</span>
                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                    </svg>
                </a>

                <a href="#" class="text-gray-400 hover:text-gray-300 transition duration-150 ease-in-out">
                    <span class="sr-only">Facebook</span>
                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385h-3.047v-3.47h3.047v-2.642c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953h-1.514c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385c5.737-.9 10.125-5.864 10.125-11.854z" />
                    </svg>
                </a>

                <a href="#" class="text-gray-400 hover:text-gray-300 transition duration-150 ease-in-out">
                    <span class="sr-only">Twitter</span>
                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
                    </svg>
                </a>
            </div>

            <!-- Footer content -->
            <div class="mt-8 md:order-1 md:mt-0">
                <p class="text-center text-sm leading-5 text-gray-400">
                    &copy; <?php echo date('Y'); ?> Job Portal. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

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