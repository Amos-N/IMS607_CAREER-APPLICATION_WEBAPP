<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['admin_id']);

// Get current page for active navigation
$currentPage = basename($_SERVER['PHP_SELF']);

// Base URL for the project
$baseUrl = '/career';

// Function to check if link is active
function isActive($page)
{
    global $currentPage;
    return $currentPage === $page ? 'bg-blue-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white';
}
?>

<nav class="bg-gray-800">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <!-- Logo and Brand -->
            <div class="flex items-center">
                <a href="<?php echo $baseUrl; ?>/admin/dashboard/index.php" class="text-white font-bold text-xl">Job Portal</a>
            </div>

            <?php if ($isLoggedIn): ?>
                <!-- Navigation Links -->
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <a href="<?php echo $baseUrl; ?>/admin/dashboard/index.php"
                            class="<?php echo isActive('index.php'); ?> rounded-md px-3 py-2 text-sm font-medium">
                            Dashboard
                        </a>
                        <a href="<?php echo $baseUrl; ?>/admin/dashboard/list.php"
                            class="<?php echo isActive('list.php'); ?> rounded-md px-3 py-2 text-sm font-medium">
                            Applications
                        </a>
                        <a href="<?php echo $baseUrl; ?>/admin/dashboard/search.php"
                            class="<?php echo isActive('search.php'); ?> rounded-md px-3 py-2 text-sm font-medium">
                            Search
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Right side menu -->
            <div class="flex items-center">
                <?php if ($isLoggedIn): ?>
                    <span class="text-gray-300 mr-4">Hi, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="<?php echo $baseUrl; ?>/admin/auth/logout.php"
                        class="bg-red-600 hover:bg-red-700 text-white rounded-md px-3 py-2 text-sm font-medium">
                        Logout
                    </a>
                <?php else: ?>
                    <a href="<?php echo $baseUrl; ?>/admin/auth/login.php"
                        class="text-gray-300 hover:bg-gray-700 hover:text-white rounded-md px-3 py-2 text-sm font-medium">
                        Login
                    </a>
                    <a href="<?php echo $baseUrl; ?>/admin/auth/register.php"
                        class="bg-blue-600 hover:bg-blue-700 text-white rounded-md px-3 py-2 text-sm font-medium ml-3">
                        Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Mobile menu -->
    <?php if ($isLoggedIn): ?>
        <div class="md:hidden">
            <div class="space-y-1 px-2 pb-3 pt-2">
                <a href="<?php echo $baseUrl; ?>/admin/dashboard/index.php"
                    class="<?php echo isActive('index.php'); ?> block rounded-md px-3 py-2 text-base font-medium">
                    Dashboard
                </a>
                <a href="<?php echo $baseUrl; ?>/admin/dashboard/list.php"
                    class="<?php echo isActive('list.php'); ?> block rounded-md px-3 py-2 text-base font-medium">
                    Applications
                </a>
                <a href="<?php echo $baseUrl; ?>/admin/dashboard/search.php"
                    class="<?php echo isActive('search.php'); ?> block rounded-md px-3 py-2 text-base font-medium">
                    Search
                </a>
            </div>
        </div>
    <?php endif; ?>
</nav>

<?php if (isset($pageTitle)): ?>
    <header class="bg-white shadow">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900"><?php echo $pageTitle; ?></h1>
        </div>
    </header>
<?php endif; ?>