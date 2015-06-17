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
 * @version     $Id$
 */
namespace Docalist\Biblio\Views;

use Docalist\Biblio\Database;
use Docalist\Biblio\Settings\DatabaseSettings;
use Docalist\Forms\Form;

/**
 * Import de fichier dans une base : choix des fichiers.
 *
 * @param Database $database Base de données en cours.
 * @param DatabaseSettings $settings Paramètres de la base de données en cours.
 * @param array $converters Liste des formats d'imports disponibles (code => label).
 */
?>
<div class="wrap">
    <?= screen_icon() ?>
    <h2><?= sprintf(__('Import %s', 'docalist-biblio'), $settings->label) ?></h2>

    <p class="description">
        <?= __("Ajoutez les fichiers à importer, choisissez l'ordre en déplaçant l'icone, indiquez le format de chacun des fichiers puis cliquez sur le bouton lancer l'import.", 'docalist-biblio') ?>
    </p>

    <form action="" method="post">
        <h3 class="title"><?=__('Liste des fichiers à importer', 'docalist-biblio') ?></h3>

        <ul id="file-list">
            <!-- Template utilisé pour afficher le(s) fichier(s) choisi(s) -->
            <script type="text/html" id="file-template">
                <li class="file postbox"><?php // postbox : pour avoir le cadre, la couleur, ... ?>
                    <img class="file-icon" src="{icon}" title="Type {mime}, id {id}">
                    <div class="file-info">
                        <h4>{filename} <span class="file-date">({dateFormatted})</span>
                            - <a class="remove-file" href="#"><?=__('Retirer ce fichier', 'docalist-biblio') ?></a>
                        </h4>
                        <p class="file-description">
                            <i>{caption} {description}</i><br />
                        </p>
                        <label>
                            <?=__('Format : ', 'docalist-biblio') ?>
                            <select name="formats[]">
                                <option value=""><?=__('Indiquez le format', 'docalist-biblio')?></option>
                                <?php foreach($converters as $name => $label): ?>
                                <option value="<?=esc_attr($name)?>" selected="selected"><?=esc_html($label)?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>
                    <input type="hidden" name="ids[]" value="{id}" />
                </li>
            </script>
        </ul>

        <button type="button"
            class="add-file button button-secondary">
            <?=__('Ajouter un fichier...', 'docalist-biblio') ?>
        </button>

        <h3 class="title"><?=__('Options', 'docalist-biblio') ?></h3>

        <ul>
            <li>
                <label>
                    Statut des notices importées :
                    <select name="options[status]">
                    <?php
                        $statuses = get_post_stati(['show_in_admin_all_list' => true], 'objects');
                        unset($statuses['future']);
                    ?>
                    <?php foreach ($statuses as $name => $status): ?>
                        <option value="<?=esc_attr($name)?>"<?=selected('pending', $name, false)?>><?=esc_html($status->label)?></option>
                    <?php endforeach; ?>
                    </select>
                </label>
            </li>
            <li>
                <label>
                    <input type="checkbox" name="options[simulate]" value="1" checked="checked" />
                    <?=__("Simuler l'import (ne pas créer de notices)", 'docalist-biblio') ?>
                </label>
            </li>
        </ul>

        <div class="submit buttons">
            <button type="submit"
                class="run-import button button-primary"
                disabled="disabled">
                <?=__("Lancer l'import...", 'docalist-biblio') ?>
            </button>
        </div>
    </form>
</div>

<style>
.file {
    padding: 1em;
}

.file-icon,.file-info {
    display: inline-block;
    vertical-align: top;
    margin-right: 1em;
}

.file-icon {
    cursor: move;
}

.file-info h4 {
    margin: 0;
}

.file-date {
    font-style: italic;
    font-size: smaller;
}

.file-description {
    margin: 0;
}

/* Réduit un peu la taille de la boite pour que le titre reste visible */
.smaller {
    top: 20%;
    right: 15%;
    bottom: 10%;
    left: 15%;
}
</style>

<?php
wp_enqueue_media();

wp_enqueue_script(
    'docalist-biblio-import-choose',
    plugins_url('docalist-biblio/views/import/choose.js'),
    ['jquery-ui-sortable'],
    20140417,
    true
);