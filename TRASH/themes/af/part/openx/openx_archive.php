<?php
global $af_theme;
$show_open_x = 'in_content_ad:archive_list_az_'.$i;
$af_theme->openxZones = ($af_theme->openxZones) ? $af_theme->openxZones . ',' . $show_open_x : $show_open_x;
?>
<div data-profile-event-name="openx" class="openx-wrapper" id="archive_list_az_<?php echo $i; ?>"></div>