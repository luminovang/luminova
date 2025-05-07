<?php
/**
 * Redirect Script to public controller in development mode.
 * 
 * This script is intended to redirect all incoming requests to the `public` directory,
 * which contains the main entry point for the application. It is typically placed in 
 * the project root to ensure that users are redirected to the correct public folder.
 */
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Wed, 03 Apr 1991 05:00:00 GMT');
header('Location: ./public/', true, 301);
exit();