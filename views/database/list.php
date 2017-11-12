<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
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
 * Affiche la liste des bases de données existantes.
 *
 * @var AdminDatabases $this
 * @var DatabaseSettings[] $databases Liste des bases de données.
 */
?>
<style>
div.dbdesc{
    white-space: pre-wrap;
    max-height: 10em;
    overflow-y: auto;
}
</style>
<div class="wrap">
    <h1><?= __('Gestion des bases documentaires', 'docalist-biblio') ?></h1>

    <p class="description">
        <?= __('Voici la liste de vos bases de données :', 'docalist-biblio') ?>
    </p>

    <table class="widefat fixed">

    <thead>
        <tr>
            <th><?= __('Nom de la base', 'docalist-biblio') ?></th>
            <th><?= __('Page d\'accueil', 'docalist-biblio') ?></th>
            <th><?= __('Types de notices', 'docalist-biblio') ?></th>
            <th><?= __('Nombre de notices', 'docalist-biblio') ?></th>
            <th><?= __('Description', 'docalist-biblio') ?></th>
        </tr>
    </thead>

    <?php
    $nb = 0;
    foreach($databases as $dbindex => $database) { /** @var DatabaseSettings $database */
        $edit = esc_url($this->url('DatabaseEdit', $dbindex));
        $delete = esc_url($this->url('DatabaseDelete', $dbindex));
        $listTypes = esc_url($this->url('TypesList', $dbindex));
        $exportSettings = esc_url($this->url('DatabaseExportSettings', $dbindex));
        $importSettings = esc_url($this->url('DatabaseImportSettings', $dbindex));

        $count = wp_count_posts($database->postType())->publish;
        $listRefs = esc_url(admin_url('edit.php?post_type=' . $database->postType()));
        $nb++; ?>

        <tr>
            <td class="column-title">
                <strong>
                    <a href="<?= $edit ?>"><?= $database->label() ?></a>
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
                    <br />
                    <span class="export-settings">
                        <a href="<?= $exportSettings ?>">
                            <?= __('Exporter paramètres', 'docalist-biblio') ?>
                        </a>
                    </span>
                    |
                    <span class="import-settings">
                        <a href="<?= $importSettings ?>">
                            <?= __('Importer paramètres', 'docalist-biblio') ?>
                        </a>
                    </span>
                </div>
            </td>

            <td><a href="<?= $database->url() ?>"><?= $database->slug() ?></a></td>
            <td>
                <?php if (0 === count($database->types)): ?>
                    <a href="<?= esc_url($this->url('TypeAdd', $dbindex)) ?>">
                        <?= __('Ajouter un type...', 'docalist-biblio') ?>
                    </a>
                <?php else: ?>
                    <?php foreach ($database->types as $typeindex => $type): /** @var TypeSettings $type */ ?>
                        <a href="<?= esc_url($this->url('GridList', $dbindex, $typeindex)) ?>">
                            <?= $type->label() ?>
                        </a>
                        <br />
                    <?php endforeach ?>
                <?php endif ?>

            </td>
            <td><a href="<?= $listRefs ?>"><?= $count ?></a></td>
            <td><div class="dbdesc"><?= $database->description() ?></div></td>
        </tr>
        <?php
    } // end foreach

    if ($nb === 0) : ?>
        <tr>
            <td colspan="4">
                <em><?= __('Aucune base définie.', 'docalist-biblio') ?></em>
            </td>
        </tr><?php
    endif; ?>

    </table>

    <p>
        <a href="<?= esc_url($this->url('DatabaseAdd')) ?>" class="button button-primary">
            <?= __('Créer une base...', 'docalist-biblio') ?>
        </a>
    </p>
</div>
