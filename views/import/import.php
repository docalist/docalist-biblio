<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2014 Daniel Ménard
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

/**
 * Cette vue est affichée lorsqu'on lance l'import d'un ou plusieurs fichiers
 * dans une base de données.
 *
 * Lorsqu'elle est exécutée, cette vue installe des filtres qui doivent être
 * appellés au bon moment par les importeurs.
 *
 * Six filtres sont installés :
 * - docalist_biblio_before_import : début de l'import des fichier. Affiche le
 *   début de la page (début de la div.wrap, titre h2, p.description).
 *
 * - docalist_biblio_import_start : début de l'import d'un fichier donné.
 *   Affiche un titre h3 avec le nom du type en cours et ouvre un <ul>.
 *
 * - docalist_biblio_import_progress : affiche l'avancement de l'import (par
 *   exemple le nombre de notices importées jusque là).
 *   Affiche le début d'un <li> indiquant le nombre de notices chargées.
 *
 * - docalist_biblio_import_error : affiche une erreur.
 *
 * - docalist_biblio_import_done : fin de l'import d'un fichier donné. Affiche
 *   le nombre de notices chargées pour ce fichier.
 *
 * - docalist_biblio_after_import : fin de l'import des fichiers choisis.
 *
 * @param array $files Un tableau contenant la liste des fichiers à importer
 * sous la forme $path => $importer.
 */
?>

<?php
    // Nombre de documents indexés pour un type donné.
    // Initialisé à zéro dans before_reindex_type, incrémenté et utilisé
    // dans before_flush.
    $total;

    // Timestamp contenant l'heure de début d'un flush
    // Initialisé par before_flush, utilisé dans after_flush
    $flushTime;
?>

<?php
/**
 * before_import : début de l'import.
 *
 * Affiche le début de la page (début de la div.wrap, titre h2, p.description).
 *
 * @param array $files Un tableau contenant la liste des fichiers à importer
 * sous la forme $path => $importer.
 *
 * @param Database $database La base de données destination
 */
add_action('docalist_biblio_before_import', function(array $files, Database $database) { ?>
    <div class="wrap">
        <?= screen_icon() ?>
        <h2><?= __("Import de fichiers", 'docalist-biblio') ?></h2>

        <p class="description"><?php
            //@formatter:off
            printf(
                __(
                    'Vous avez lancé un import de fichiers dans la base
                    <strong>%s</strong>.<br />
                    La page affichera des informations supplémentaires au fur
                    et à mesure de l\'avancement. Veuillez patienter.',
                    'docalist-biblio'
                ),
                $database->settings()->label
            );
            // @formatter:on
            ?>
        </p>

        <?php
        flush();
}, 10, 2); ?>

<?php
/**
 * docalist_biblio_import_start : début de l'import d'un fichier donné.
 *
 * Affiche un titre h3 et ouvre un <ul>.
 *
 * @param string $file le path du fichier qui va être importé.
 */
add_action('docalist_biblio_import_start', function($file) { ?>
    <h3><?= sprintf(__('Import du fichier %s', 'docalist-biblio'), basename($file)) ?></h3>
    <ul class="ul-square">
    <?php
    flush();
}, 10, 2);
?>

<?php
/**
 * docalist_biblio_import_progress : affiche un message de progression.
 *
 * @param string $message Message à afficher.
 */
add_action('docalist_biblio_import_progress', function($message) {
    echo '<li>', $message, '</li>';
    flush();
}, 10, 1);
?>

<?php
/**
 * docalist_biblio_import_error : signale une erreur
 *
 * @param string $error Message d'erreur à afficher.
 */
add_action('docalist_biblio_import_error', function($error) {
    echo '<li style="color: red; font-weight: bold;">', $error, '</li>';
    flush();
}, 10, 1);
?>

<?php
/**
 * docalist_biblio_import_done : fin de l'import d'un fichier donné.
 *
 * Ferme le <ul> ouvert par docalist_biblio_import_start.
 *
 * @param string $file le path du fichier qui a été importé.
 */
add_action('docalist_biblio_import_done', function($file) { ?>
    </ul>
    <?php
    flush();
}, 1, 3);
?>

<?php
/**
 * docalist_biblio_after_import : fin de l'import.
 *
 * @param array $files Un tableau contenant la liste des fichiers importés
 * sous la forme $path => $importer.
 *
 * @param Database $database La base de données destination
 */
add_action('docalist_biblio_after_import', function(array $files, Database $database) { ?>
        <h3><?= __('Terminé !', 'docalist-biblio') ?></h3>
    </div>
    <?php
    $msg = _n(
        'L\'import est terminé : le fichier %2$s a été importé dans %3$s.',
        'L\'import est terminé : %d fichiers importés dans %3$s (%2$s).',
        count($files), 'docalist-biblio');

    printf($msg,
        count($files),
        implode(', ', array_map('basename', array_keys($files))),
        $database->settings()->label
    );

    flush();
}, 10, 2);
?>