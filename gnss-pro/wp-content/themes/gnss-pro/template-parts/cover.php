<?php
/**
 * Geometric cover for a rubric. Decorative.
 */

if (!defined('ABSPATH')) {
    exit;
}

$slug = isset($args['slug']) ? (string) $args['slug'] : 'glonass';
$seed = isset($args['seed']) ? (int) $args['seed'] : 1;
[$bg, $fg, $accent] = gnss_rubric_palette($slug);
$angle = deg2rad(($seed * 47) % 360);
$sx = (int) round(320 + 150 * cos($angle));
$sy = (int) round(200 + 78 * sin($angle));
?>
<svg class="cover" viewBox="0 0 640 400" preserveAspectRatio="xMidYMid slice" aria-hidden="true" focusable="false">
    <rect width="640" height="400" fill="<?php echo esc_attr($bg); ?>"/>
    <?php if ($slug === 'signaly') : ?>
        <g fill="<?php echo esc_attr($fg); ?>" opacity="0.9">
            <?php for ($i = 0; $i < 18; $i++) :
                $height = 40 + (($i * 37 + $seed) % 150);
                $x = 48 + $i * 32;
                ?>
                <rect x="<?php echo esc_attr((string) $x); ?>" y="<?php echo esc_attr((string) (320 - $height)); ?>" width="14" height="<?php echo esc_attr((string) $height); ?>" fill="<?php echo $i === 7 ? esc_attr($accent) : esc_attr($fg); ?>" opacity="<?php echo $i === 7 ? '1' : '0.75'; ?>"/>
            <?php endfor; ?>
        </g>
        <path d="M40 328 H600" stroke="<?php echo esc_attr($accent); ?>" stroke-width="1.5" fill="none"/>
    <?php elseif ($slug === 'vysokaya-tochnost') : ?>
        <g fill="none" stroke="<?php echo esc_attr($fg); ?>" stroke-width="1.6">
            <path d="M90 310 H550"/>
            <path d="M160 310 L320 96 L500 310"/>
            <path d="M320 96 L250 310" stroke="<?php echo esc_attr($accent); ?>"/>
        </g>
        <circle cx="160" cy="310" r="7" fill="<?php echo esc_attr($fg); ?>"/>
        <circle cx="500" cy="310" r="7" fill="<?php echo esc_attr($accent); ?>"/>
        <circle cx="320" cy="96" r="8" fill="<?php echo esc_attr($accent); ?>"/>
        <circle cx="<?php echo esc_attr((string) max(120, min(520, $sx))); ?>" cy="96" r="4" fill="<?php echo esc_attr($fg); ?>"/>
    <?php elseif ($slug === 'otrasli') : ?>
        <g fill="none" stroke="<?php echo esc_attr($fg); ?>" stroke-width="1.5" opacity="0.85">
            <?php for ($i = 0; $i < 8; $i++) : ?>
                <path d="M70 <?php echo 80 + $i * 34; ?> C 180 <?php echo 60 + $i * 34; ?>, 260 <?php echo 110 + $i * 34; ?>, 570 <?php echo 78 + $i * 34; ?>"/>
            <?php endfor; ?>
        </g>
        <circle cx="<?php echo esc_attr((string) max(90, min(560, $sx))); ?>" cy="<?php echo esc_attr((string) max(90, min(300, $sy))); ?>" r="8" fill="<?php echo esc_attr($accent); ?>"/>
    <?php elseif ($slug === 'priemniki') : ?>
        <g fill="none" stroke="<?php echo esc_attr($fg); ?>" stroke-width="1.6">
            <path d="M320 300 V150"/>
            <path d="M250 150 Q320 70 390 150"/>
            <path d="M200 300 H440" stroke="<?php echo esc_attr($accent); ?>"/>
            <circle cx="320" cy="118" r="5" fill="<?php echo esc_attr($accent); ?>" stroke="none"/>
        </g>
        <g fill="none" stroke="<?php echo esc_attr($fg); ?>" stroke-width="1.2" opacity="0.7">
            <path d="M430 120 C 500 100, 540 150, 520 200"/>
            <path d="M450 90 C 560 70, 590 160, 540 230"/>
        </g>
    <?php elseif ($slug === 'sistemy-gnss') : ?>
        <?php
        $centers = [[120, 200], [260, 200], [400, 200], [520, 200]];
        foreach ($centers as $index => $center) :
            $color = $index === ($seed % 4) ? $accent : $fg;
            ?>
            <g fill="none" stroke="<?php echo esc_attr($color); ?>" stroke-width="1.5">
                <circle cx="<?php echo esc_attr((string) $center[0]); ?>" cy="<?php echo esc_attr((string) $center[1]); ?>" r="28"/>
                <ellipse cx="<?php echo esc_attr((string) $center[0]); ?>" cy="<?php echo esc_attr((string) $center[1]); ?>" rx="46" ry="18"/>
            </g>
            <circle cx="<?php echo esc_attr((string) ($center[0] + 30)); ?>" cy="<?php echo esc_attr((string) ($center[1] - 16)); ?>" r="4" fill="<?php echo esc_attr($color); ?>"/>
        <?php endforeach; ?>
    <?php else : ?>
        <g fill="none" stroke="<?php echo esc_attr($fg); ?>" stroke-width="1.5">
            <ellipse cx="320" cy="200" rx="78" ry="78"/>
            <ellipse cx="320" cy="200" rx="150" ry="70" transform="rotate(-32 320 200)"/>
            <ellipse cx="320" cy="200" rx="210" ry="86" transform="rotate(18 320 200)"/>
            <path d="M110 200 H530" opacity="0.45"/>
        </g>
        <circle cx="320" cy="200" r="7" fill="<?php echo esc_attr($accent); ?>"/>
        <circle cx="<?php echo esc_attr((string) $sx); ?>" cy="<?php echo esc_attr((string) $sy); ?>" r="6" fill="<?php echo esc_attr($fg); ?>"/>
    <?php endif; ?>
</svg>
