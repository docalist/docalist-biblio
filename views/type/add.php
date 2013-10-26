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
use Docalist\Biblio\TypeSettings;

/**
 * Choisit un type de notice à ajouter dans la base.
 *
 * @param DatabaseSettings $database La base à éditer.
 * @param int $dbindex L'index de la base.
 * @param TypeSettings[] $types Liste des types disponibles.
 *
 */

$back = $this->url('TypesList', $dbindex);
/* @var $database DatabaseSettings */
?>
<div class="wrap">
    <?= screen_icon() ?>
    <h2><?= sprintf(__('%s : ajouter un type de notice', 'docalist-biblio'), $database->label) ?></h2>

    <p class="description">
        <?= __('Choisissez le type de notice à ajouter dans la base :', 'docalist-biblio') ?>
    </p>

    <ul class="ul-disc">
        <?php
        foreach($types as $type) :
            /* @var $type TypeSettings */

            $add = $this->url('TypeAdd', $dbindex, $type->name);
            ?>
            <li>
                <h3>
                    <a href="<?= esc_url($add) ?>"><?= $type->label ?></a> (<?= $type->name ?>)
                </h3>
                <p class="description"><?= $type->description ?></p>
            </li>
        <?php endforeach ?>
    </ul>

    <p>
        <a href="<?= esc_url($back) ?>" class="button">
            <?= __('Annuler', 'docalist-biblio') ?>
        </a>
    </p>
</div>