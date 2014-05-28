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

use Docalist\Biblio\Database;

/**
 * Demande une confirmation à l'utilisateur.
 *
 * Si l'utilisateur clique "ok", la requête en cours est relancée avec en plus
 * le paramètre confirm=1.
 *
 * @param Database $database La base en cours.
 */

$count = $this->database->count();

$href = add_query_arg('confirm', '1');
$back = 'javascript:history.go(-1)';
?>

<div class="wrap">
    <?= screen_icon() ?>
    <h2><?= __('Vider la base', 'docalist-biblio') ?></h2>

    <div class="error">
        <?php if ($count ===0): ?>
            <h3><?= __('La base est vide', 'docalist-biblio') ?></h3>
            <p><?= __("Il n'y a aucune notice à supprimer", 'docalist-biblio') ?></p>
            <p>
                <a href="<?= $back ?>" class="button-primary">
                    <?= __('Ok', 'docalist-biblio') ?>
                </a>
            </p>

        <?php else: ?>
            <h3><?= __('Attention', 'docalist-biblio') ?></h3>

            <p>
                <?= sprintf(__('Vous allez supprimer définitivement <b>%d notices</b>.', 'docalist-biblio'), $count) ?>
            </p>

            <p>
                <?= __('Toutes les données liées à ces notices seront également supprimées :', 'docalist-biblio') ?>
            </p>

            <ul class="ul-square">
                <li><?= __('meta-données des notices,', 'docalist-biblio') ?></li>
                <li><?= __('termes de taxonomies éventuels (mots-clés, catégories, etc.),', 'docalist-biblio') ?></li>
                <li><?= __('révisions et sauvegardes automatiques des notices,', 'docalist-biblio') ?></li>
                <li><?= __('commentaires sur les notices et meta-données liées à ces commentaires.', 'docalist-biblio') ?></li>
            </ul>

            <p>
                <i><?= __("Remarque :", 'docalist-biblio') ?></i>
                <?= __("Si cette base contient des notices parent de notices situées dans d'autres bases, celles-ci n'auront plus de parent.", 'docalist-biblio') ?>
            </p>

            <p>
                <b><?= __("La suppression est définitive. Voulez-vous continuer ?", 'docalist-biblio') ?></b>
            </p>

            <p>
                <a href="<?= $href ?>" class="button-primary">
                    <?= __('Vider la base', 'docalist-biblio') ?>
                </a>

                <a href="<?= $back ?>" class="button">
                    <?= __('Annuler', 'docalist-biblio') ?>
                </a>
            </p>
        <?php endif; ?>
    </div>
</div>