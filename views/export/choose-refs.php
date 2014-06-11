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
use Docalist\Forms\Fragment;

/**
 * Export de notices : choix des notices à exporter.
 *
 * @param Database $database Base de données en cours.
 * @param array $exporter Le nom de code de l'exporteur en cours.
 * @param string $error Optionnel, erreur à afficher.
 */

// TODO : utiliser get_search_form() ?
$form = new Fragment();
/*
$form->attribute('class', 'form-horizontal')
->attribute('id', 'advanced-search');
*/
$form->input('q')
->label('Equation :')
->attribute('class', 'input-block-level');
/*
$form->input('topic.filter')
->label('Mots-clés :')
->attribute('class', 'input-block-level')
->attribute('data-lookup', 'index:topic.suggest')
->attribute('data-multiple', true);

$form->input('title')
->label('Mots du titre :')
->attribute('class', 'input-block-level');

$form->input('author.filter')
->label('Auteur :')
->attribute('class', 'input-block-level')
->attribute('data-lookup', 'index:author.suggest')
->attribute('data-multiple', true);

$form->input('organisation.filter')
->label('Organisme :')
->attribute('class', 'input-block-level')
->attribute('data-lookup', 'index:organisation.suggest');

$form->input('journal.filter')
->label('Revue :')
->attribute('class', 'input-block-level')
->attribute('data-lookup', 'index:journal.suggest');

$form->input('date')
->label('Date :');

$form->checklist('type.filter')
->noBrackets(true)
->label('Type :')
->options(array('Article', 'Livre', 'Mémoire', 'Document audiovisuel', 'Cd-rom'))
->attribute('class', 'inline');

$form->tag('div.form-actions', "
        <button type='submit' class='btn btn-primary pull-left'>Rechercher...</button>
        <a class='btn btn-mini pull-right' href='$reset'>Nouvelle recherche</a>
        ");

$form->bind($url);
$form->render('bootstrap');
*/
?>
<div class="wrap">
    <?= screen_icon() ?>
    <h2><?= sprintf(__('Export %s', 'docalist-biblio'), $database->settings()->label) ?></h2>

    <p class="description">
        <?= __("Choisissez les notices à exporter.", 'docalist-biblio') ?>
    </p>

    <?php if (isset($error)): ?>
    <div class="error">
        <p><?=$error ?>
    </div>

    <?php endif; ?>

    <form action="" method="post">
        <?php
            $form->bind($_REQUEST);
            $form->render('wordpress');
        ?>

        <div class="submit buttons">
            <button type="submit" class="run-export">
                <?=__("Lancer l'export...", 'docalist-biblio') ?>
            </button>
        </div>
        <?php if(!empty($exporter)): ?>
            <input type="hidden" name="exporter" value="<?= $exporter ?>" />
        <?php endif; ?>
    </form>
</div>