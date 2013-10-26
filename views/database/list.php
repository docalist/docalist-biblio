<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012, 2013 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     $Id$
 */
namespace Docalist\Biblio\Views;

use Docalist\Biblio\DatabaseSettings;

/**
 * Affiche la liste des bases de données existantes.
 *
 * @param DatabaseSettings[] $databases Liste des bases de données.
 */
?>
<div class="wrap">
    <?= screen_icon() ?>
    <h2><?= __('Gestion des bases de données Docalist Biblio', 'docalist-biblio') ?></h2>

    <p class="description">
        <?= __('Voici la liste de vos bases de données :', 'docalist-biblio') ?>
    </p>

    <table class="widefat fixed">

    <thead>
        <tr>
            <th><?= __('Libellé', 'docalist-biblio') ?></th>
            <th><?= __('Identifiant', 'docalist-biblio') ?></th>
            <th><?= __('Slug', 'docalist-biblio') ?></th>
            <th><?= __('Types', 'docalist-biblio') ?></th>
            <th><?= __('Notices', 'docalist-biblio') ?></th>
        </tr>
    </thead>

    <?php
    $nb = 0;
    foreach($databases as $dbindex => $database) {
        /* @var $database DatabaseSettings */

        $edit = esc_url($this->url('DatabaseEdit', $dbindex));
        $delete = esc_url($this->url('DatabaseDelete', $dbindex));
        $listTypes = esc_url($this->url('TypesList', $dbindex));

        $types = implode(', ', $database->typeNames());
        $types === '' && $types = __('Aucun type défini pour cette base.', 'docalist-biblio');

        $count = wp_count_posts($database->postType())->publish;
        $nb++; ?>

        <tr>
            <td class="column-title">
                <strong>
                    <a href="<?= $edit ?>"><?= $database->label ?></a>
                </strong>
                <div class="row-actions">
                    <span class="edit">
                        <a href="<?= $edit ?>">
                            <?= __('Paramètres', 'docalist-biblio') ?>
                        </a>
                    </span>
                    |
                    <span class="list-types">
                        <a href="<?= $listTypes ?>">
                            <?= __('Types de notices', 'docalist-biblio') ?>
                        </a>
                    </span>
                    |
                    <span class="delete">
                        <a href="<?= $delete ?>">
                            <?= __('Supprimer', 'docalist-biblio') ?>
                        </a>
                    </span>
                </div>
            </td>

            <td><?= $database->name ?></td>
            <td><?= $database->slug ?></td>
            <td><?= $types ?></td>
            <td><?= $count ?></td>
        </tr>
        <?php
    } // end foreach

    if ($nb === 0) : ?>
        <tr>
            <td colspan="4">
                <em><?= __('Aucune base définie.', 'docalist-biblio') ?></em>
            </td>
        </tr>
    <?php endif; ?>

    </table>

    <p>
        <a href="<?= esc_url($this->url('DatabaseAdd')) ?>" class="button button-primary">
            <?= __('Créer une nouvelle base...', 'docalist-biblio') ?>
        </a>
    </p>
</div>