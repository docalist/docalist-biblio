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
use Docalist\Schema\Schema;
use Docalist\Forms\Form;

/**
 * Edite les paramètres d'une grille.
 *
 * @param DatabaseSettings $database La base à éditer.
 * @param int $dbindex L'index de la base.
 * @param TypeSettings $type Le type à éditer.
 * @param int $typeindex L'index du type.
 * @param Schema $grid La grille à éditer.
 * @param string $gridname L'index de la grille.
 */

/* @var $database DatabaseSettings */
/* @var $type TypeSettings */
/* @var $grid Schema */

?>
<div class="wrap">
    <?= screen_icon() ?>
    <h2><?= sprintf(__('%s - %s - %s - paramètres', 'docalist-biblio'), $database->label(), $type->name(), $grid->label()) ?></h2>

    <p class="description">
        <?= __('Utilisez le formulaire ci-dessous pour modifier les paramètres généraux de la grille.', 'docalist-biblio') ?>
    </p>

    <?php
        $form = new Form('', 'post');
        //$form->input('name')->attribute('class', 'regular-text');

        $form->input('name')
             ->attribute('class', 'regular-text')
             ->attribute('disabled', true)
             ->label(__('Nom', 'docalist-biblio'))
             ->description(__('Nom interne de la grille (non modifiable).', 'docalist-biblio'));
        $form->input('label')
             ->attribute('class', 'regular-text')
             ->label(__('Libellé', 'docalist-biblio'))
             ->description(__('Libellé utilisé pour désigner cette grille.', 'docalist-biblio'));
        $form->textarea('description')
             ->attribute('rows', '2')
             ->attribute('class', 'large-text')
             ->label(__('Description', 'docalist-biblio'))
             ->description(__('Description libre.', 'docalist-biblio'));
        $form->submit(__('Enregistrer les modifications', 'docalist-biblio'));

        $form->bind($grid->toArray())->render('wordpress');
    ?>
</div>