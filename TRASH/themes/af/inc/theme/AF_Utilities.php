<?php
/**
 * Utility functions
 */

// Delete transient data by query string for dev testing
if (isset($_GET['ref']) && $_GET['ref'] == 1) {
    $transient_id = isset($_GET['tid']) ? $_GET['tid'] : '' ;
    if($transient_id) delete_transient($transient_id);
}