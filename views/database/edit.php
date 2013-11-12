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

use Docalist\Biblio\DatabaseSettings;
use Docalist\Forms\Form;

/**
 * Edite les paramètres d'un base de données.
 *
 * @param DatabaseSettings $database La base à éditer.
 * @param int $dbindex L'index de la base.
 * @param string $error Erreur éventuelle à afficher.
 */
?>
<div class="wrap">
    <?= screen_icon() ?>
    <h2><?= __('Paramètres de la base', 'docalist-biblio') ?></h2>

    <p class="description">
        <?= __('Utilisez le formulaire ci-dessous pour modifier les paramètres de votre base de données :', 'docalist-biblio') ?>
    </p>

    <?php if ($error) :?>
        <div class="error">
            <p><?= $error ?></p>
        </div>
    <?php endif ?>

    <?php
        $form = new Form('', 'post');
        $form->input('name')->attribute('class', 'regular-text');
        $form->input('slug')->attribute('class', 'regular-text');
        $form->input('label')->attribute('class', 'large-text');
        $form->textarea('description')->attribute('rows', 10)->attribute('class', 'large-text');
        $form->submit(__('Enregistrer les modifications', 'docalist-biblio'));

        $form->bind($database)->render('wordpress');
    ?>
</div>
<script type="text/javascript">
(function($) {
    /**
     * Si la base n'a pas de slug, change le slug quand on tape le nom
     */
    $(document).ready(function () {
        var noslug;

        var update = function() {
            var slug = $('#slug').val(), name = $('#name').val();
            noslug = slug === '' || slug === name;
        };

        update();

        $(document).on('keydown', '#slug', update);

        $(document).on('input propertychange', '#name', function() {
            noslug && $('#slug').val($(this).val());
        });
    });
}(jQuery));
</script>