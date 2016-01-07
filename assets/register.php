<?php
/**
 * This file is part of the "Docalist Biblio" plugin.
 *
 * Copyright (C) 2015-2015 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Biblio;

// Les scripts suivants ne sont dispos que dans le back-office
add_action('admin_init', function () {
    $url = plugins_url('docalist-biblio');

    // Css pour EditReference (également utilisé dans le paramétrage de la grille de saisie)
    wp_register_style(
        'docalist-biblio-edit-reference',
        "$url/assets/edit-reference.css",
        ['wp-admin'],
        '160106'
    );

    // Editeur de grille
    wp_register_script(
        'docalist-biblio-grid-edit',
        "$url/views/grid/edit.js",
        ['jquery', 'jquery-ui-sortable'],
        '20150510',
        true
    );

    wp_register_style(
        'docalist-biblio-grid-edit',
        "$url/views/grid/edit.css",
        [],
        '20150510'
    );
});
