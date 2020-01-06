<?php
global $af_theme;
$show_open_x = 'bottom_free_ad:bottom_free_az';
$af_theme->openxZones = ($af_theme->openxZones) ? $af_theme->openxZones . ',' . $show_open_x : $show_open_x;
?>
<section class="bottom-free-az">
    <div class="row">
        <div class="small-12 large-10 large-centered columns">
            <div class="openx-section">
                <div data-profile-event-name="openx" class="openx-wrapper" id="bottom_free_az"></div>
            </div>
        </div>
    </div>
</section>