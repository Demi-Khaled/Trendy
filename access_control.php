<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Function to restrict access based on user role
function restrict_access($allowed_roles)
{
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
        if (isset($_SESSION['role']) && $_SESSION['role'] == 'Employee') {
            // If the user is an employee, set a session message and redirect to the home page
            $_SESSION['message'] = "You do not have permission to access this page.";
            header('Location: Home page.php');
        } else {
            // If the user's role is not in the allowed roles, redirect to an error page or login page
            header('Location: access_denied.php');
        }
        exit;
    }
}
