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
 * Liste des types d'une base de données.
 *
 * @var AdminDatabases $this
 * @var DatabaseSettings $database La base à éditer.
 * @var int $dbindex L'index de la base.
 */
?>
<div class="wrap">
    <h1><?= sprintf(__('%s : types de notices', 'docalist-biblio'), $database->label()) ?></h1>

    <p class="description">
        <?= __('Votre base de données contient les types de notices suivants :', 'docalist-biblio') ?>
    </p>

    <table class="widefat fixed">

    <thead>
        <tr>
            <th><?= __('Type', 'docalist-biblio') ?></th>
            <th><?= __('Description', 'docalist-biblio') ?></th>
        </tr>
    </thead>

    <?php
    $addType = $this->url('TypeAdd', $dbindex);
    $nb = 0;
    foreach($database->types as $typeindex => $type) {
        /** @var TypeSettings $type */

        $edit = esc_url($this->url('TypeEdit', $dbindex, $typeindex));
        $delete = esc_url($this->url('TypeDelete', $dbindex, $typeindex));
        $listGrids = esc_url($this->url('GridList', $dbindex, $typeindex));
        $recreate = esc_url($this->url('TypeRecreate', $dbindex, $typeindex));

        $nb++;
    ?>

    <tr>
        <td class="column-title">
            <strong><a href="<?= $edit ?>"><?= $type->label() ?></a></strong> (<?= $type->name() ?>)
            <div class="row-actions">
                <span class="edit">
                    <a href="<?= $edit ?>">
                        <?= __('Paramètres', 'docalist-biblio') ?>
                    </a>
                </span>
                |
                <span class="fields">
                    <a href="<?= $listGrids ?>">
                        <?= __('Grilles et formulaires', 'docalist-biblio') ?>
                    </a>
                </span>
                |
                <span class="delete">
                    <a href="<?= $delete ?>">
                        <?= __('Supprimer ce type', 'docalist-biblio') ?>
                    </a>
                </span>
                |
                <span class="recreate">
                    <a href="<?= $recreate ?>">
                        <?= __('debug : recréer', 'docalist-biblio') ?>
                    </a>
                </span>
            </div>
        </td>

        <td><?= $type->description() ?></td>
    </tr>
    <?php
    } // end foreach

    if ($nb === 0) : ?>
        <tr>
            <td colspan="2">
                <em><?= __('Aucun type de notices dans cette base.', 'docalist-biblio') ?></em>
            </td>
        </tr>
    <?php endif; ?>

    </table>

    <p>
        <a href="<?= esc_url($addType) ?>" class="button button-primary">
            <?= __('Ajouter un type de notices...', 'docalist-biblio') ?>
        </a>
    </p>
</div>