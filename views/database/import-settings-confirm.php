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
use Docalist\Forms\Form;

/**
 * Importe les paramètres d'une base. Etape 2 : choix des types.
 *
 * @var AdminDatabases $this
 * @var DatabaseSettings $database La base en cours.
 * @var string $dbindex L'index de la base.
 * @var array $settings Les paramètres à importer.
 */

$settings = $settings; // évite warning 'not initialize dans le foreach ci-dessous, bug pdt-extensions

// Initialise la liste des types présents dans les settings à importer
$types = [];
foreach($settings['types'] as $type) {
    $types[$type['name']] = $type['label'] . ' (' . $type['name'] . ')';
}

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
    <h1><?= sprintf(__('%s - importer des paramètres', 'docalist-biblio'), $database->label()) ?></h1>

    <p class="description">
        <?= __('Les paramètres que vous avez collés contiennent les types listés ci-dessous.', 'docalist-biblio') ?>
        <?= __("Par défaut, <b>tous les types seront importés</b> mais vous pouvez décocher certains types pour n'importer qu'une partie des paramètres.", 'docalist-biblio') ?>
    </p>

    <?php
        $form = new Form();
        $form->checklist('types')
             ->setOptions($types)
             ->setLabel(__('Types à importer', 'docalist-biblio'))
             ->setDescription(__("Désélectionnez les types que vous ne voulez pas importer.", 'docalist-biblio'));

        $form->submit(__('Importer...', 'docalist-biblio'));
        $form->hidden('settings');

        $form->bind([
            'types' => array_keys($types),
            'settings' => json_encode($settings, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        ]);

        $form->display();
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