<?php

/**
 * @var string $logo_url
 * @var array $icons
 * @var object $banner
 * @var string $before_form
 * @var array $fields
 * @var array $menu
 * @var string $after_form
 */

use WC_P24\Render;

?>


<div class="p24-ui-banner">
    <div class="p24-ui-banner__left">
        <img src="<?= $logo_url ?>" alt="Przelewy 24 logo" height="35" width="100" class="p24-ui-banner__logo" />

        <h2><?= __('Welcome to Przelewy24', 'woocommerce-p24') ?></h2>
        <p><?= __('Start accepting payments via online transfers, BLIK, payment cards or other methods tailored to your business.', 'woocommerce-p24') ?></p>

        <div class="p24-ui-banner__icons">
            <?php foreach ($icons as $icon): ?>
                <figure><img src="<?= $icon ?>" alt=""></figure>
            <?php endforeach; ?>

            <span><?= __('...and more', 'woocommerce-p24') ?></span>
        </div>
    </div>

    <?php if ($banner): ?>
        <figure class="p24-ui-banner__right">
                <a href="<?= $banner->url ?>" target="_blank">
                    <img src="<?= $banner->banner ?>" alt="" />
                </a>
        </figure>
    <?php endif; ?>
</div>


<div class="p24-ui-wrapper">
    <ul class="p24-ui-navigation">
        <?php foreach ($menu as $item): ?>
            <?php if ($item === null): ?>
                <li>
                    <hr />
                </li>
            <?php else: ?>
                <li>
                    <a href="<?= $item['url'] ?>" class="<?= $item['class'] ?>">
                        <svg class="p24-ui-icon">
                            <use href="#p24-icon-<?= $item['id'] ?>" />
                        </svg>
                        <span><?= $item['label'] ?></span>
                    </a>
                </li>
            <?php endif; ?>
        <?php endforeach ?>
    </ul>

    <div class="p24-ui-content">
        <?= $before_form ?>
        <?php Render::template('admin/form', [
            'fields' => $fields,
            'show_submit' => true
        ]) ?>
        <?= $after_form ?>
    </div>
</div>

<svg width="0" height="0">
    <defs>
        <symbol id="p24-icon-general" viewBox="0 0 24 24">
            <path
                d="M1 4.75h2.736a3.728 3.728 0 0 0 7.195 0H23a1 1 0 0 0 0-2H10.931a3.728 3.728 0 0 0-7.195 0H1a1 1 0 0 0 0 2ZM7.333 2a1.75 1.75 0 1 1-1.75 1.75A1.752 1.752 0 0 1 7.333 2ZM23 11h-2.736a3.727 3.727 0 0 0-7.194 0H1a1 1 0 0 0 0 2h12.07a3.727 3.727 0 0 0 7.194 0H23a1 1 0 0 0 0-2Zm-6.333 2.75a1.75 1.75 0 1 1 1.75-1.75 1.752 1.752 0 0 1-1.75 1.75ZM23 19.25H10.931a3.728 3.728 0 0 0-7.195 0H1a1 1 0 0 0 0 2h2.736a3.728 3.728 0 0 0 7.195 0H23a1 1 0 0 0 0-2ZM7.333 22a1.75 1.75 0 1 1 1.75-1.75A1.753 1.753 0 0 1 7.333 22Z" />
        </symbol>
        <symbol id="p24-icon-encryption" viewBox="0 0 24 24">
            <path
                d="M19 8.424V7A7 7 0 0 0 5 7v1.424A5 5 0 0 0 2 13v6a5.006 5.006 0 0 0 5 5h10a5.006 5.006 0 0 0 5-5v-6a5 5 0 0 0-3-4.576ZM7 7a5 5 0 0 1 10 0v1H7Zm13 12a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3v-6a3 3 0 0 1 3-3h10a3 3 0 0 1 3 3Z" />
            <path d="M12 14a1 1 0 0 0-1 1v2a1 1 0 0 0 2 0v-2a1 1 0 0 0-1-1Z" />
        </symbol>
        <symbol id="p24-icon-multicurrency" viewBox="0 0 24 24">
            <path
                d="M21.715 18.363A10 10 0 0 1 4.461 15H15a1 1 0 0 0 0-2H4.051a9.829 9.829 0 0 1 0-2H15a1 1 0 0 0 0-2H4.461a10 10 0 0 1 17.254-3.363 1 1 0 0 0 1.542-1.274A11.989 11.989 0 0 0 2.4 9H1a1 1 0 0 0 0 2h1.051c-.028.331-.051.662-.051 1s.023.669.051 1H1a1 1 0 0 0 0 2h1.4a11.989 11.989 0 0 0 20.862 4.637 1 1 0 0 0-1.542-1.274z" />
        </symbol>
        <symbol id="p24-icon-subscription" viewBox="0 0 24 24">
            <path
                d="M12 2a10.032 10.032 0 0 1 7.122 3H16a1 1 0 0 0-1 1 1 1 0 0 0 1 1h4.143A1.858 1.858 0 0 0 22 5.143V1a1 1 0 0 0-1-1 1 1 0 0 0-1 1v2.078A11.981 11.981 0 0 0 .05 10.9a1.007 1.007 0 0 0 1 1.1.982.982 0 0 0 .989-.878A10.014 10.014 0 0 1 12 2Zm10.951 10a.982.982 0 0 0-.989.878A9.986 9.986 0 0 1 4.878 19H8a1 1 0 0 0 1-1 1 1 0 0 0-1-1H3.857A1.856 1.856 0 0 0 2 18.857V23a1 1 0 0 0 1 1 1 1 0 0 0 1-1v-2.078A11.981 11.981 0 0 0 23.95 13.1a1.007 1.007 0 0 0-1-1.1Z" />
        </symbol>
        <symbol id="p24-icon-installments" viewBox="0 0 24 24">
            <path
                d="M19 2h-1V1a1 1 0 0 0-2 0v1H8V1a1 1 0 0 0-2 0v1H5a5.006 5.006 0 0 0-5 5v12a5.006 5.006 0 0 0 5 5h14a5.006 5.006 0 0 0 5-5V7a5.006 5.006 0 0 0-5-5ZM2 7a3 3 0 0 1 3-3h14a3 3 0 0 1 3 3v1H2Zm17 15H5a3 3 0 0 1-3-3v-9h20v9a3 3 0 0 1-3 3Z" />
            <circle cx="12" cy="15" r="1.5" />
            <circle cx="7" cy="15" r="1.5" />
            <circle cx="17" cy="15" r="1.5" />
        </symbol>

        <symbol id="p24-icon-p24-online-payments" viewBox="0 0 24 24">
            <path
                d="M24 23a1 1 0 0 1-1 1H1a1 1 0 0 1 0-2h22a1 1 0 0 1 1 1zM.291 8.552a2.443 2.443 0 0 1 .153-2.566 4.716 4.716 0 0 1 1.668-1.5L9.613.582a5.174 5.174 0 0 1 4.774 0l7.5 3.907a4.716 4.716 0 0 1 1.668 1.5 2.443 2.443 0 0 1 .153 2.566A2.713 2.713 0 0 1 21.292 10H21v8h1a1 1 0 0 1 0 2H2a1 1 0 0 1 0-2h1v-8h-.292A2.713 2.713 0 0 1 .291 8.552zM5 18h3v-8H5zm5-8v8h4v-8zm9 0h-3v8h3zM2.063 7.625A.717.717 0 0 0 2.708 8h18.584a.717.717 0 0 0 .645-.375.452.452 0 0 0-.024-.5 2.7 2.7 0 0 0-.949-.864l-7.5-3.907a3.176 3.176 0 0 0-2.926 0l-7.5 3.907a2.712 2.712 0 0 0-.949.865.452.452 0 0 0-.026.499z" />
        </symbol>
        <symbol id="p24-icon-p24-google-pay" viewBox="0 0 24 24">
            <path
                d="M12.479 14.265v-3.279h11.049c.108.571.164 1.247.164 1.979 0 2.46-.672 5.502-2.84 7.669C18.744 22.829 16.051 24 12.483 24 5.869 24 .308 18.613.308 12S5.869 0 12.483 0c3.659 0 6.265 1.436 8.223 3.307L18.392 5.62c-1.404-1.317-3.307-2.341-5.913-2.341-4.829 0-8.606 3.892-8.606 8.721s3.777 8.721 8.606 8.721c3.132 0 4.916-1.258 6.059-2.401.927-.927 1.537-2.251 1.777-4.059l-7.836.004z" />
        </symbol>
        <symbol id="p24-icon-p24-apple-pay" viewBox="0 0 24 24">
            <path
                d="M18.546 12.763a5.4527 5.4527 0 0 1 2.597-4.576 5.582 5.582 0 0 0-4.399-2.378c-1.851-.194-3.645 1.107-4.588 1.107-.961 0-2.413-1.088-3.977-1.056a5.86 5.86 0 0 0-4.93 3.007c-2.131 3.69-.542 9.114 1.5 12.097 1.022 1.461 2.215 3.092 3.778 3.035 1.529-.063 2.1-.975 3.945-.975 1.828 0 2.364.975 3.958.938 1.64-.027 2.674-1.467 3.66-2.942a12.0647 12.0647 0 0 0 1.673-3.408 5.2702 5.2702 0 0 1-3.217-4.849zm-3.011-8.916A5.371 5.371 0 0 0 16.763 0a5.4676 5.4676 0 0 0-3.535 1.829 5.111 5.111 0 0 0-1.261 3.705 4.521 4.521 0 0 0 3.568-1.687z" />
        </symbol>
        <symbol id="p24-icon-p24-card" viewBox="0 0 24 24">
            <circle cx="5.5" cy="15.5" r="1.5" />
            <path
                d="M19 3H5a5.006 5.006 0 0 0-5 5v8a5.006 5.006 0 0 0 5 5h14a5.006 5.006 0 0 0 5-5V8a5.006 5.006 0 0 0-5-5ZM5 5h14a3 3 0 0 1 3 3H2a3 3 0 0 1 3-3Zm14 14H5a3 3 0 0 1-3-3v-6h20v6a3 3 0 0 1-3 3Z" />
        </symbol>
        <symbol id="p24-icon-p24-blik" viewBox="0 0 23.798 23.122">
            <defs>
                <radialGradient id="b" cx=".093" cy="-.027" r="1.639" fx=".0742" fy="-.0319"
                                gradientUnits="objectBoundingBox">
                    <stop offset="0" stop-color="red" />
                    <stop offset=".495" stop-color="#e83e49" />
                    <stop offset="1" stop-color="#f0f" />
                </radialGradient>
                <linearGradient id="a" x1=".5" x2=".5" y1="1" gradientUnits="objectBoundingBox">
                    <stop offset="0" stop-color="#262626" />
                    <stop offset="1" />
                </linearGradient>
            </defs>
            <g transform="translate(-415.487 -46.749)">
                <path fill="url(#a)"
                      d="M435.635 69.871h-16.5a3.65 3.65 0 0 1-3.65-3.65V50.4a3.65 3.65 0 0 1 3.65-3.65h16.5a3.65 3.65 0 0 1 3.65 3.65v15.82a3.65 3.65 0 0 1-3.65 3.651Z"
                      data-name="Path 9549" />
                <path fill="#fff"
                      d="M427.135 55.719a5.632 5.632 0 0 0-2.675.672v-6.183h-2.98v11.165a5.655 5.655 0 1 0 5.655-5.655Zm0 8.33a2.675 2.675 0 1 1 2.672-2.676 2.675 2.675 0 0 1-2.672 2.676Z"
                      data-name="Path 9550" />
                <circle cx="2.657" cy="2.657" r="2.657" fill="url(#b)" data-name="Ellipse 133"
                        transform="translate(427.386 49.382)" />
            </g>
        </symbol>

        <symbol id="p24-icon-eye-closed" viewBox="0 0 32 32">
            <path
                d="m26.53 12.529-1.514 1.515a12.752 12.752 0 0 1-18.032 0L5.47 12.529a.75.75 0 0 1 1.06-1.06l1.515 1.515a11.249 11.249 0 0 0 15.91 0l1.515-1.514a.75.75 0 0 1 1.06 1.059Z" />
            <path d="m22.85 18.633-1.5-2.6a.7504.7504 0 0 1 1.3-.75l1.5 2.6a.7504.7504 0 1 1-1.3.75Z" />
            <path d="M15.25 20v-3a.75.75 0 1 1 1.5 0v3a.75.75 0 1 1-1.5 0Z" />
            <path d="m7.85 17.883 1.5-2.6a.7504.7504 0 0 1 1.3.75l-1.5 2.6a.7504.7504 0 1 1-1.3-.75Z" />
        </symbol>

        <symbol id="p24-icon-eye-opened" viewBox="0 0 32 32">
            <g transform="translate(3.75 4.25)">
                <circle cx="3.25" cy="3.25" r="3.25" transform="translate(8.75 11.75)" />
                <path fill-rule="evenodd"
                      d="m22.53 12.97-1.514-1.515a12.751 12.751 0 0 0-18.032 0L1.47 12.97a.75.75 0 0 0 1.06 1.06l1.515-1.514a11.249 11.249 0 0 1 15.91 0l1.515 1.514a.75.75 0 0 0 1.06-1.06Z" />
                <path fill-rule="evenodd" d="m18.85 6.867-1.5 2.6a.75.75 0 0 0 1.3.75l1.5-2.6a.75.75 0 1 0-1.3-.75Z" />
                <path fill-rule="evenodd" d="M11.25 5.5v3a.75.75 0 0 0 1.5 0v-3a.75.75 0 0 0-1.5 0Z" />
                <path fill-rule="evenodd" d="m3.85 7.617 1.5 2.6a.75.75 0 0 0 1.3-.75l-1.5-2.6a.75.75 0 1 0-1.3.75Z" />
            </g>
        </symbol>

        <symbol id="p24-icon-info" viewBox="0 0 16 16">
            <path
                d="M8 0a8 8 0 1 0 8 8 8.0087 8.0087 0 0 0-8-8Zm0 14.6667a6.6663 6.6663 0 0 1-6.1592-4.1155 6.6665 6.6665 0 0 1 4.8586-9.0898A6.6666 6.6666 0 0 1 14.6667 8 6.6743 6.6743 0 0 1 8 14.6667Z" />
            <path
                d="M7.9998 6.667h-.6666a.6667.6667 0 0 0 0 1.3333h.6666v4a.6666.6666 0 1 0 1.3334 0v-4A1.3333 1.3333 0 0 0 7.9998 6.667ZM8 5.333c.5523 0 1-.4477 1-1s-.4477-1-1-1-1 .4477-1 1 .4477 1 1 1Z" />
        </symbol>
    </defs>
</svg>
