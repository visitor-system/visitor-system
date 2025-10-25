<?php


/**
 * Check if user is logged in
 * @return bool
 */
function is_logged_in()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user has specific role
 * @param string $required_role The role to check for ('admin', 'user', etc.)
 * @return void Redirects to login page if not authorized
 */
function require_role($required_role)
{
    if (!is_logged_in()) {
        header("Location: login.html");
        exit();
    }

    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $required_role) {
        header("Location: dashboard.php");
        exit();
    }
}

/**
 * Check if user has admin role
 * @return bool
 */
function is_admin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Get current user's ID
 * @return int|null
 */
function get_current_user_id()
{
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user's company ID
 * @return int|null
 */
function get_current_company_id()
{
    return $_SESSION['company_id'] ?? null;
}

/**
 * Get current user's role
 * @return string|null
 */
function get_current_role()
{
    return $_SESSION['role'] ?? null;
}
?>