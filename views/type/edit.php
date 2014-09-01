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
use Docalist\Biblio\Settings\TypeSettings;
use Docalist\Forms\Form;

/**
 * Edite les paramètres d'un type.
 *
 * @param DatabaseSettings $database La base à éditer.
 * @param int $dbindex L'index de la base.
 * @param TypeSettings $type Le type à éditer.
 * @param int $typeindex L'index du type.
 */

/* @var $database DatabaseSettings */
/* @var $type TypeSettings */

?>
<div class="wrap">
    <?= screen_icon() ?>
    <h2><?= sprintf(__('%s - paramètres du type "%s"', 'docalist-biblio'), $database->label(), $type->name()) ?></h2>

    <p class="description">
        <?= __('Utilisez le formulaire ci-dessous pour modifier les paramètres du type :', 'docalist-biblio') ?>
    </p>

    <?php
        $form = new Form('', 'post');
        //$form->input('name')->attribute('class', 'regular-text');

        $form->input('label')
             ->attribute('class', 'regular-text');
        $form->textarea('description')
             ->attribute('rows', '2')
             ->attribute('class', 'large-text');
        $form->submit(__('Enregistrer les modifications', 'docalist-biblio'));

        $form->bind($type)->render('wordpress');
    ?>
</div>