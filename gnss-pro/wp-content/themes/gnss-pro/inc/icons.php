<?php
/**
 * Small inline marks.
 */

if (!defined('ABSPATH')) {
    exit;
}

function gnss_logo_mark(): void
{
    echo '<svg class="mark" viewBox="0 0 48 48" aria-hidden="true" focusable="false">';
    echo '<circle cx="24" cy="24" r="16" fill="none" stroke="currentColor" stroke-width="1.6"/>';
    echo '<circle cx="24" cy="24" r="6" fill="none" stroke="#c45c26" stroke-width="1.6"/>';
    echo '<circle cx="36" cy="16" r="2.4" fill="#c45c26"/>';
    echo '</svg>';
}
