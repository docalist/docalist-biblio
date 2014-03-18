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

use Docalist\Biblio\Database;
use Docalist\Biblio\DatabaseSettings;
use Docalist\Forms\Form;

/**
 * Import de fichier dans une base
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
        <?= __("Choisissez le ou les fichiers que vous souhaitez charger, indiquez le format de chacun des fichiers puis cliquez sur le bouton lancer l'import.", 'docalist-biblio') ?>
    </p>

    <form action="" method="post">
        <ul id="file-list">
            <!-- Template utilisé pour afficher le(s) fichier(s) choisi(s) -->
            <script type="text/html" id="file-template">
                <li class="file">
                    <img class="file-icon" src="{icon}" title="Type {mime}, id {id}">
                    <div class="file-info">
                        <h3>{filename} <span class="file-date">({dateFormatted})</span></h3>
                        <p class="file-description">
                            <b>{caption}</b><br />
                            {description}
                        </p>
                        <label>
                            <?=__('Format : ', 'docalist-biblio') ?>
                            <select name="formats[]">
                                <option value=""><?=__('Indiquez le format', 'docalist-biblio')?></option>
                                <?php foreach($converters as $name => $label): ?>
                                <option value="<?=esc_attr($name)?>"><?=esc_html($label)?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <p>
                            <a class="remove-file" href="#"><?=__('Retirer ce fichier', 'docalist-biblio') ?></a>
                        </p>
                    </div>
                    <input type="hidden" name="ids[]" value="{id}" />
                </li>
            </script>
        </ul>

        <div class="buttons">
            <button type="button"
                class="add-file button button-secondary">
                <?=__('Ajouter un fichier...', 'docalist-biblio') ?>
            </button>

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
    border: 1px solid #eee;
    padding: 0.5em;
    margin-bottom: 0.5em;
}

.file-icon,.file-info {
    display: inline-block;
    vertical-align: top;
    margin-right: 1em;
}

.file-info h3 {
    margin: 0;
}

.file-date {
    font-style: italic;
    font-size: smaller;
}

.file-description {
    margin: 0;
}

.add-file, .remove-file {
    text-decoration: none;
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
    'docalist-biblio-import',
    plugins_url('docalist-biblio/views/import/choose.js?140318'),
    array(),
    '20131219',
    true
);