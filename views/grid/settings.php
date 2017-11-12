<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
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
use Docalist\Schema\Schema;
use Docalist\Forms\Form;

/**
 * Edite les paramètres d'une grille.
 *
 * @var AdminDatabases      $this
 * @var DatabaseSettings    $database   La base à éditer.
 * @var int                 $dbindex    L'index de la base.
 * @var TypeSettings        $type       Le type à éditer.
 * @var int                 $typeindex  L'index du type.
 * @var Schema              $grid       La grille à éditer.
 * @var string              $gridname   L'index de la grille.
 */
?>
<div class="wrap">
    <h1><?= sprintf(__('%s - %s - %s - paramètres', 'docalist-biblio'), $database->label(), $type->name(), $grid->label()) ?></h1>

    <p class="description">
        <?= __('Utilisez le formulaire ci-dessous pour modifier les paramètres généraux de la grille.', 'docalist-biblio') ?>
    </p>

    <?php
        $form = new Form();

        $form->input('name')
             ->addClass('regular-text')
             ->setAttribute('disabled')
             ->setLabel(__('Nom', 'docalist-biblio'))
             ->setDescription(__('Nom interne de la grille (non modifiable).', 'docalist-biblio'));
        $form->input('label')
             ->addClass('regular-text')
             ->setLabel(__('Libellé', 'docalist-biblio'))
             ->setDescription(__('Libellé utilisé pour désigner cette grille.', 'docalist-biblio'));
        $form->textarea('description')
             ->setAttribute('rows', 2)
             ->addClass('large-text')
             ->setLabel(__('Description', 'docalist-biblio'))
             ->setDescription(__('Description libre.', 'docalist-biblio'));
        $form->submit(__('Enregistrer les modifications', 'docalist-biblio'));

        $form->bind($grid)->display();
    ?>
</div>
