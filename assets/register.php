<?php
/**
 * This file is part of the "Docalist Biblio UserData" plugin.
 *
 * Copyright (C) 2015-2015 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     SVN: $Id$
 */
namespace Docalist\Biblio;

// Les scripts suivants ne sont dispos que dans le back-office
add_action('admin_init', function() {
    $url = plugins_url('docalist-biblio/assets');

    // Css pour EditReference (également utilisé dans le paramétrage de la grille de saisie)
    wp_register_style(
        'docalist-biblio-edit-reference',
        "$url/edit-reference.css",
        ['wp-admin'],
        '140927'
    );
});

// Scripts dispos en front et en back
// add_action('init', function() {
//     $url = plugins_url('docalist-biblio/assets');

//     // ...
// });