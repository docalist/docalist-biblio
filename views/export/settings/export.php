<?php
/**
 * This file is part of the "Docalist Biblio Export" plugin.
 *
 * Copyright (C) 2015-2015 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist\Biblio\Export
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     SVN: $Id$
 */
namespace Docalist\Biblio\Export\Views;

use Docalist\Biblio\Export\Settings;
use Docalist\Forms\Form;

/**
 * Paramètres de l'export.
 *
 * @param Settings $settings Les paramètres pour l'export.
 */
?>
<div class="wrap">
    <?= screen_icon() ?>
    <h2><?= __("Export et bibliographies", 'docalist-biblio-export') ?></h2>

    <p class="description"><?php
        echo __(
            "Le module d'export vous permet de générer des fichiers d'export et des bibliographies à partir d'une recherche docalist-search ou du panier de notices.",
            'docalist-biblio-export'
        );
    ?></p>

    <?php
        $form = new Form();
        $form->select('exportpage')->options(pagesList())->firstOption(false);
        $form->submit(__('Enregistrer les modifications', 'docalist-biblio-export'));

        $form->bind($settings)->render('wordpress');
    ?>
</div>

<?php
/**
 * Retourne la liste hiérarchique des pages sous la forme d'un tableau
 * utilisable dans un select.
 *
 * @return array Un tableau de la forme PageID => PageTitle
 */
function pagesList() {
    $pages = ['…'];
    foreach(get_pages() as $page) { /* @var $page \WP_Post */
        $pages[$page->ID] = str_repeat('   ', count($page->ancestors)) . $page->post_title;
    }

    return $pages;
}
