<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2015 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Biblio\Views;

use Docalist\Biblio\Pages\AdminDatabases;
use Docalist\Biblio\Settings\DatabaseSettings;
use Docalist\Biblio\Settings\TypeSettings;

/**
 * Liste les grilles et les formulaires disponibles pour un type.
 *
 * @var AdminDatabases      $this
 * @var DatabaseSettings    $database   La base à éditer.
 * @var int                 $dbindex    L'index de la base.
 * @var TypeSettings        $type       Le type à éditer.
 * @var int                 $typeindex  L'index du type.
 */
?>
<style>
.grid-label{
    width: 30%;
}
.grid-name{
    width: 10%;
}
.grid-description{
    width: 60%;
}
</style>

<div class="wrap">
    <h1><?= sprintf(__('%s - %s - grilles et formulaires', 'docalist-biblio'), $database->label(), $type->label()) ?></h1>

    <p class="description">
        <?= __("L'écran ci-dessous affiche la liste des grilles disponibles pour ce type de notice.", 'docalist-biblio') ?>
    </p>

    <table class="widefat fixed">

    <thead>
        <tr>
            <th class="grid-label"><?= __('Nom de la grille', 'docalist-biblio') ?></th>
            <th class="grid-name"><?= __('Code', 'docalist-biblio') ?></th>
            <th class="grid-description"><?= __('Description', 'docalist-biblio') ?></th>
        </tr>
    </thead>

    <?php
    $nb = 0;
    foreach($type->grids as $name => $grid) {
        /* @var $type TypeSettings */

        $settings = esc_url($this->url('GridSettings', $dbindex, $typeindex, $name));
        $edit = esc_url($this->url('GridEdit', $dbindex, $typeindex, $name));
        $copy = esc_url($this->url('GridCopy', $dbindex, $typeindex, $name));
        $delete = esc_url($this->url('GridDelete', $dbindex, $typeindex, $name));
        $tophp = esc_url($this->url('GridToPhp', $dbindex, $typeindex, $name));

        $nb++;
    ?>

    <tr>
        <th class="grid-label column-title">
            <strong><a href="<?= $edit ?>"><?= $grid->label() ?></a></strong>
            <div class="row-actions">
                <span class="settings">
                    <a href="<?= $settings ?>">
                        <?= __('Paramètres', 'docalist-biblio') ?>
                    </a>
                </span>
                |
                <span class="edit">
                    <a href="<?= $edit ?>">
                        <?= __('Modifier', 'docalist-biblio') ?>
                    </a>
                </span>
                |
                <span class="copy">
                    <a href="<?= $copy ?>">
                        <?= __('Dupliquer', 'docalist-biblio') ?>
                    </a>
                </span>
                |
                <span class="delete">
                    <a href="<?= $delete ?>">
                        <?= __('Supprimer', 'docalist-biblio') ?>
                    </a>
                </span>
                <?php if (wp_get_current_user()->user_login === 'dmenard') : ?>
                |
                <span class="tophp">
                    <a href="<?= $tophp ?>">
                        <?= __('Code PHP', 'docalist-biblio') ?>
                    </a>
                </span>
                <?php endif;?>
            </div>
        </th>

        <td class="grid-name"><?= $grid->name() ?></td>
        <td class="grid-description"><?= $grid->description() ?></td>
    </tr>
    <?php
    } // end foreach

    if ($nb === 0) : ?>
        <tr>
            <td colspan="2">
                <em><?= __('Erreur interne : aucune grille disponible pour ce type de notice.', 'docalist-biblio') ?></em>
            </td>
        </tr>
    <?php endif; ?>

    </table>
</div>