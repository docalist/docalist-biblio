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

use Docalist\Biblio\Database;
use Docalist\Biblio\Settings\DatabaseSettings;
use Docalist\Forms\Form;
use Docalist\Biblio\Pages\ImportPage;

/**
 * Import de fichier dans une base : choix des fichiers.
 *
 * @var ImportPage $this
 * @var Database $database Base de données en cours.
 * @var DatabaseSettings $settings Paramètres de la base de données en cours.
 * @var array $converters Liste des formats d'imports disponibles (code => label).
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

        <ul id="file-list"></ul>

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

        <button type="button"
            class="add-file button button-secondary">
            <?=__('Ajouter un fichier...', 'docalist-biblio') ?>
        </button>

        <h3 class="title"><?=__('Options', 'docalist-biblio') ?></h3>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="simulate"><?=__("Simuler l'import", 'docalist-biblio')?></label>
                </th>
                <td>
                    <input type="checkbox" name="options[simulate]" value="1" checked="checked" id="simulate" />
                    <label for="simulate"><?=__("Ne pas créer de notices", 'docalist-biblio') ?></label>
                    <p class="description">
                        <?=__('Utilisez cette option pour valider votre fichier et vérifier que les notices peuvent être converties au format docalist.', 'docalist-biblio')?>
                        <?=__('Décochez la case pour lancer réellement l\'import.', 'docalist-biblio')?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="limit"><?=__('Limite de l\'import', 'docalist-biblio')?></label>
                </th>
                <td>
                    <input name="options[limit]" type="number" min="0" id="limit" placeholder="<?=__('Toutes les', 'docalist-biblio')?>" />
                    <?=__('notices.', 'docalist-biblio')?>
                    <p class="description">
                        <?=__('Utilisez cette option pour limiter le nombre de notices importées.', 'docalist-biblio')?>
                        <?=__('Par défaut, toutes les notices présentes dans le fichier seront importées.', 'docalist-biblio')?>
                        <?=__('Si vous souhaitez faire un test d\'import (par exemple pour valider le fichier à importer), indiquez un nombre pour traiter seulement les n premières notices du fichier.', 'docalist-biblio')?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="status"><?=__('Statut des notices', 'docalist-biblio')?></label>
                </th>
                <td>
                    <select name="options[status]" id="status">
                    <?php
                        $statuses = get_post_stati(['show_in_admin_all_list' => true], 'objects');
                        unset($statuses['future']);
                    ?>
                    <?php foreach ($statuses as $name => $status): ?>
                        <option value="<?=esc_attr($name)?>"<?=selected('pending', $name, false)?>><?=esc_html($status->label)?></option>
                    <?php endforeach; ?>
                    </select>
                    <p class="description">
                        <?=__('Par défaut, les notices importées seront créées avec le statut "en attente".', 'docalist-biblio')?>
                        <?=__('Choisissez l\'une des options proposées dans la liste pour leur affecter un statut différent.', 'docalist-biblio')?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="importref"><?=__('N° de référence existant', 'docalist-biblio')?></label>
                </th>
                <td>
                    <select name="options[importref]" id="importref">
                        <option value="0" selected="selected">
                            <?=__('Ignorer', 'docalist-biblio')?>
                        </option>
                        <option value="1">
                            <?=__('Importer', 'docalist-biblio')?>
                        </option>
                    </select>
                    <p class="description">
                        <?=__('Par défaut, docalist ne tient pas compte du numéro de référence éventuel (REF) qui figurent dans les notices importées et un nouveau numéro de référence sera attribué aux notices lorsque celles-ci seront publiées.', 'docalist-biblio')?>
                        <?=__('Choisissez l\'option "importer" si vous souhaitez conserver tel quel le numéro de référence qui figure dans le fichier d\'import.', 'docalist-biblio')?>
                    </p>
                </td>
            </tr>
        </table>

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