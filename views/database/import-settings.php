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
 */
namespace Docalist\Biblio\Views;

use Docalist\Biblio\Settings\DatabaseSettings;
use Docalist\Forms\Form;

/**
 * Importe les paramètres d'une base. Etape 1 :récupération du code json.
 *
 * @param DatabaseSettings $database La base en cours.
 * @param string $dbindex L'index de la base.
 */
?>
<div class="wrap">
    <?= screen_icon() ?>
    <h2><?= sprintf(__('%s - importer des paramètres', 'docalist-biblio'), $database->label()) ?></h2>

    <p class="description">
        <?= __('Collez le code contenant les paramètres à importer dans la zone de texte ci-dessous.', 'docalist-biblio') ?>
    </p>

    <?php
        $form = new Form('', 'post');
        $form->textarea('settings')
             ->label(__('Paramètres à importer', 'docalist-biblio'))
             ->description(__("Collez le code que vous avez copié en utilisant l'option 'exporter paramètres'. Le code commence par { et se termine par }, veillez à tout inclure.", 'docalist-biblio'))
             ->attribute('class', 'code large-text')
             ->attribute('style', 'height: 70vh');
        $form->submit(__('Importer...', 'docalist-biblio'));

        $form->render('wordpress');
    ?>
</div>