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

use Docalist\Biblio\Database;

/**
 * Permet à l'utilisateur de choisir le type de notice à créer.
 *
 * @param Database $database Base de données en cours.
 */
?>
<div class="wrap">
    <?= screen_icon() ?>
    <?php
        $title = sprintf(__('%1$s - %2$s', 'docalist-search'),
            $database->settings()->label(),
            get_post_type_object($database->postType())->labels->add_new_item
        );
    ?>
    <h2><?= $title ?></h2>

    <p class="description">
        <?= __("Choisissez le type de notice à créer.", 'docalist-biblio') ?>
    </p>
    <table class="widefat">
        <?php $nb = 0 ?>
        <?php foreach($database->settings()->types as $type): ?>
            <tr class="<?= ++$nb % 2 ? 'alternate' : '' ?>">
                <td class="row-title">
                    <a href="<?= esc_url(add_query_arg('ref_type', $type->name())) ?>">
                        <?= $type->label() ?>
                    </a>
                </td>
                <td class="desc">
                    <?= $type->description() ?>
                </td>
            </tr>
        <?php endforeach ?>

        <?php if ($nb === 0): ?>
            <tr>
                <td class="desc" colspan="2">
                    <?= __('Aucun type disponible dans cette base.', 'docalist-biblio') ?>
                </td>
            </tr>
        <?php endif ?>
    </table>
</div>