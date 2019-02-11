<?php
/**
 * This file is part of Docalist Biblio.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Docalist\Biblio;

// Les scripts suivants ne sont dispos que dans le back-office
add_action('admin_init', function () {
    $base = DOCALIST_BIBLIO_URL;

    wp_register_style(
        'docalist-biblio-edit-reference',
        "$base/assets/edit-reference.css",
        [],
        '190211'
    );
});
