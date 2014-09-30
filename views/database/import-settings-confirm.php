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

use Docalist\Biblio\Settings\DatabaseSettings;
use Docalist\Forms\Form;

/**
 * Importe les paramètres d'une base. Etape 2 : choix des types.
 *
 * @param DatabaseSettings $database La base en cours.
 * @param string $dbindex L'index de la base.
 * @param array $settings Les paramètres à importer.
 */
?>
<style>
    p.warning {
        font-size: larger;
        font-weight: bold;
    }
    p.warning b {
        color: #F00;
    }
</style>
<div class="wrap">
    <?= screen_icon() ?>
    <h2><?= sprintf(__('%s - importer des paramètres', 'docalist-biblio'), $database->label()) ?></h2>

    <p class="description">
        <?= __('Les paramètres que vous avez collés contiennent les types listés ci-dessous.', 'docalist-biblio') ?>
        <?= __("Par défaut, <b>tous les types seront importés</b> mais vous pouvez décocher certains types pour n'importer qu'une partie des paramètres.", 'docalist-biblio') ?>
    </p>

    <?php
        $types = [];
        foreach($settings['types'] as $index => $type) {
            $types[$type['name']] = $type['label'] . ' (' . $type['name'] . ')';
        }
    ?>
    <?php
        $form = new Form('', 'post');
        $form->checklist('types')
             ->options($types)
             ->label(__('Types à importer', 'docalist-biblio'))
             ->description(__("Désélectionnez les types que vous ne voulez pas importer.", 'docalist-biblio'));

        $form->submit(__('Importer...', 'docalist-biblio'));
        $form->hidden('settings');

        $form->bind([
            'types' => array_keys($types),
            'settings' => json_encode($settings, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        ]);

        $form->render('wordpress');
    ?>

    <p class="warning">
        <b>Attention :</b> en cliquant sur le bouton importer, vous allez écraser les paramètres de la base <b><?=$database->label()?></b>.
    </p>
    <p class="warning">
        Si la base <b><?=$database->label()?></b> contient déjà certains des types sélectionnés, <b>il seront écrasés et tous les réglages seront perdus</b> (tables d'autorité, grilles de saisie, libellés, descriptions, etc.)
    </p>
    <p class="warning">
        Assurez-vous que c'est la bonne base, les bons types, le bon sens de transfert, etc. : il n'y aura pas d'autre demande de confirmation...
    </p>

</div>