<?php
global $af_theme;
$show_open_x = 'exit_popup_ad:exit_popup_az';
$af_theme->openxZones = ($af_theme->openxZones) ? $af_theme->openxZones . ',' . $show_open_x : $show_open_x;
?>
<div class="reveal exitpop" id="exitpop" data-reveal>
    <section class="openx-section">
        <div data-profile-event-name="openx" class="openx-wrapper" id="exit_popup_az"></div>
    </section>
    <button class="close-button" data-close aria-label="Close modal" type="button">
        <span aria-hidden="true">&times;</span>
    </button>
</div>