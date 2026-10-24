<?php
/**
 * Schematic of the nominal GLONASS constellation.
 *
 * @package GNSS_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<figure class="orbit-card">
    <svg viewBox="0 0 640 430" role="img" aria-labelledby="orbitTitle">
        <title id="orbitTitle">Схема трёх орбитальных плоскостей ГЛОНАСС</title>
        <g class="orbit-spin">
            <circle cx="320" cy="210" r="188" fill="none" stroke="#9ae7ff" stroke-opacity="0.18"></circle>
            <ellipse cx="320" cy="210" rx="190" ry="86" fill="none" stroke="#9ae7ff" stroke-opacity="0.45" transform="rotate(98 320 210)"></ellipse>
            <ellipse cx="320" cy="210" rx="210" ry="70" fill="none" stroke="#d7f4ff" stroke-opacity="0.75" transform="rotate(40 320 210)"></ellipse>
            <ellipse cx="320" cy="210" rx="230" ry="78" fill="none" stroke="#f0c56a" transform="rotate(-18 320 210)"></ellipse>
            <circle class="sat" cx="540" cy="149.9" r="5" fill="#f0c56a"></circle>
            <circle class="sat" cx="470.9" cy="226.5" r="5" fill="#9ae7ff"></circle>
            <circle class="sat" cx="313.4" cy="293.4" r="5" fill="#f0c56a"></circle>
            <circle class="sat" cx="159.8" cy="311.4" r="5" fill="#9ae7ff"></circle>
            <circle class="sat" cx="100" cy="270.1" r="5" fill="#f0c56a"></circle>
            <circle class="sat" cx="169.1" cy="193.5" r="5" fill="#9ae7ff"></circle>
            <circle class="sat" cx="326.6" cy="126.6" r="5" fill="#f0c56a"></circle>
            <circle class="sat" cx="480.2" cy="108.6" r="5" fill="#9ae7ff"></circle>
        </g>
        <circle class="earth-glow" cx="320" cy="210" r="78" fill="#1a6ea8" fill-opacity="0.28"></circle>
        <circle cx="320" cy="210" r="62" fill="#1d4e86"></circle>
        <ellipse cx="320" cy="210" rx="62" ry="16" fill="none" stroke="#d7f4ff" stroke-opacity="0.55"></ellipse>
    </svg>
    <figcaption>
        Три эллипса — орбитальные плоскости. Точки на ближней плоскости — восемь аппаратов. Схема штатной группировки, без масштаба и без оперативной сводки.
    </figcaption>
</figure>
