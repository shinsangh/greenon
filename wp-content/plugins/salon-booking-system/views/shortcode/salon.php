<?php
/**
 * @var string               $content
 * @var SLN_Shortcode_Salon $salon
 */

$style = $salon->getStyleShortcode();
$cce = !$plugin->getSettings()->isCustomColorsEnabled();
$class = SLN_Enum_ShortcodeStyle::getClass($style);
?>
<div id="sln-salon" class="sln-bootstrap container-fluid <?php
            echo $class;
            if(!$cce) {
              echo ' sln-customcolors';
            }
            echo ' sln-step-' . $salon->getCurrentStep(); ?>">
    <?php

    $args = array(
        'label'        => __('Book an appointment', 'salon-booking-system'),
        'tag'          => 'h2',
        'textClasses'  => 'sln-salon-title',
        'inputClasses' => '',
        'tagClasses'   => 'sln-salon-title',
    );
    echo $plugin->loadView('shortcode/_editable_snippet', $args);
    do_action('sln.booking.salon.before_content', $salon, $content);
    echo $content;
    ?>
<div id="sln-notifications"></div>
</div>
