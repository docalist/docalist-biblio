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
use Docalist\Biblio\Settings\TypeSettings;
use Docalist\Forms\Form;

/**
 * Edite les paramètres d'un type.
 *
 * @var AdminDatabases $this
 * @var DatabaseSettings $database La base à éditer.
 * @var int $dbindex L'index de la base.
 * @var TypeSettings $type Le type à éditer.
 * @var int $typeindex L'index du type.
 */
?>
<div class="wrap">
    <h1><?= sprintf(__('%s - paramètres du type "%s"', 'docalist-biblio'), $database->label(), $type->name()) ?></h1>

    <p class="description">
        <?= __('Utilisez le formulaire ci-dessous pour modifier les paramètres du type :', 'docalist-biblio') ?>
    </p>

    <?php
        $form = new Form();

        $form->input('label')
             ->addClass('regular-text');
        $form->textarea('description')
             ->setAttribute('rows', '2')
             ->addClass('large-text');
        $form->submit(__('Enregistrer les modifications', 'docalist-biblio'));

        $form->bind($type)->display();
    ?>
</div>