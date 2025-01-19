<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current page for active navigation
$currentPage = basename($_SERVER['PHP_SELF']);

// Base URL for the project
$baseUrl = '/career';

// Function to check if link is active
function isActive($page)
{
    global $currentPage;
    return $currentPage === $page ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600';
}
?>

<nav class="bg-white shadow-lg">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <!-- Logo and Brand -->
            <div class="flex items-center">
                <a href="<?php echo $baseUrl; ?>/public/index.php" class="flex items-center space-x-2">
                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span class="text-xl font-bold text-gray-900">Job Portal</span>
                </a>
            </div>

            <!-- Navigation Links -->
            <div class="hidden sm:flex sm:items-center sm:space-x-4">
                <a href="<?php echo $baseUrl; ?>/public/index.php"
                    class="<?php echo isActive('index.php'); ?> px-3 py-2 rounded-md text-sm font-medium transition-colors">
                    Available Positions
                </a>
                <a href="<?php echo $baseUrl; ?>/public/apply.php"
                    class="<?php echo isActive('apply.php'); ?> px-3 py-2 rounded-md text-sm font-medium transition-colors">
                    Submit Application
                </a>
                <a href="<?php echo $baseUrl; ?>/admin/auth/login.php"
                    class="ml-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                    Admin Login
                    <svg class="ml-2 -mr-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 3.293a1 1 0 010 1.414L9.414 9H17a1 1 0 110 2H9.414l1.293 1.293a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>

            <!-- Mobile menu button -->
            <div class="sm:hidden">
                <button type="button" onclick="toggleMobileMenu()"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-700 hover:text-blue-600 hover:bg-blue-50">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile menu -->
    <div class="sm:hidden hidden" id="mobile-menu">
        <div class="px-2 pt-2 pb-3 space-y-1">
            <a href="<?php echo $baseUrl; ?>/public/index.php"
                class="<?php echo isActive('index.php'); ?> block px-3 py-2 rounded-md text-base font-medium">
                Available Positions
            </a>
            <a href="<?php echo $baseUrl; ?>/public/apply.php"
                class="<?php echo isActive('apply.php'); ?> block px-3 py-2 rounded-md text-base font-medium">
                Submit Application
            </a>
            <a href="<?php echo $baseUrl; ?>/admin/auth/login.php"
                class="block px-3 py-2 rounded-md text-base font-medium text-white bg-blue-600 hover:bg-blue-700">
                Admin Login
            </a>
        </div>
    </div>
</nav>

<!-- Page Title if set -->
<?php if (isset($pageTitle)): ?>
    <header class="bg-white shadow">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900"><?php echo $pageTitle; ?></h1>
        </div>
    </header>
<?php endif; ?>

<script>
    function toggleMobileMenu() {
        const mobileMenu = document.getElementById('mobile-menu');
        mobileMenu.classList.toggle('hidden');
    }
</script>