<?php
if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}
?>
<div id="vcv-editor"><?php echo esc_html__('Loading...', 'vcwb'); ?></div>