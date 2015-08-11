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
 * Exporte (affiche) les paramètres d'une base.
 *
 * @param DatabaseSettings $database La base à éditer.
 * @param string $dbindex L'index de la base.
 * @param bool $pretty Code indenté ou pas.
 */
?>
<div class="wrap">
    <?= screen_icon() ?>
    <h2><?= sprintf(__('%s - exporter les paramètres de la base', 'docalist-biblio'), $database->label()) ?></h2>

    <p class="description">
        <?= __('Recopiez le code ci-dessous pour faire une sauvegarde des paramètres de la base ou transférer les paramètres vers une autre base.', 'docalist-biblio') ?>
        <br />
        <?= __('Le code JSON affiché contient les paramètres de la base, les paramètres des types et les paramètres des grilles.', 'docalist-biblio') ?>
        <br />
        <?= __('Veillez à copier <b>la totalité du code</b> du, premier "{" au dernier "}", sinon cela ne fonctionnera pas.', 'docalist-biblio') ?>
        <?= __('Par exemple, cliquez dans la zone de texte et tapez Ctrl+A puis Ctrl+C.', 'docalist-biblio') ?>
    </p>

    <textarea class="code large-text" style="height: 80vh" readonly><?php
        echo json_encode($database, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | ($pretty ? JSON_PRETTY_PRINT : 0));
    ?></textarea>

    <p>
        <?php if ($pretty) :?>
            <a href="<?=remove_query_arg('pretty') ?>">Version compacte.</a>
        <?php else:?>
            <a href="<?=add_query_arg('pretty', 1) ?>">Version indentée.</a>
        <?php endif;?>
    </p>
</div>