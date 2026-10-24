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
        <circle cx="320" cy="210" r="188" fill="none" stroke="#f2eee6" stroke-opacity="0.18"></circle>
        <ellipse cx="320" cy="210" rx="190" ry="86" fill="none" stroke="#f2eee6" stroke-opacity="0.45" transform="rotate(98 320 210)"></ellipse>
        <ellipse cx="320" cy="210" rx="210" ry="70" fill="none" stroke="#f2eee6" stroke-opacity="0.7" transform="rotate(40 320 210)"></ellipse>
        <ellipse cx="320" cy="210" rx="230" ry="78" fill="none" stroke="#e3b17a" transform="rotate(-18 320 210)"></ellipse>
        <circle cx="320" cy="210" r="62" fill="#14635c"></circle>
        <ellipse cx="320" cy="210" rx="62" ry="16" fill="none" stroke="#f2eee6" stroke-opacity="0.55"></ellipse>
        <circle cx="540" cy="149.9" r="5" fill="#e3b17a"></circle>
        <circle cx="470.9" cy="226.5" r="5" fill="#e3b17a"></circle>
        <circle cx="313.4" cy="293.4" r="5" fill="#e3b17a"></circle>
        <circle cx="159.8" cy="311.4" r="5" fill="#e3b17a"></circle>
        <circle cx="100" cy="270.1" r="5" fill="#e3b17a"></circle>
        <circle cx="169.1" cy="193.5" r="5" fill="#e3b17a"></circle>
        <circle cx="326.6" cy="126.6" r="5" fill="#e3b17a"></circle>
        <circle cx="480.2" cy="108.6" r="5" fill="#e3b17a"></circle>
    </svg>
    <figcaption>
        Три эллипса — орбитальные плоскости. Точки на ближней плоскости — восемь аппаратов. Схема штатной группировки, без масштаба и без оперативной сводки.
    </figcaption>
</figure>
