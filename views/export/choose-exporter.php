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
use Docalist\Forms\Form;

/**
 * Export de notices : choix du format d'export.
 *
 * @param Database $database Base de données en cours.
 * @param array $exporters Liste des formats d'exports disponibles.
 * @param string $args Arguments supplémentaires à transmettre.
 */
?>
<div class="wrap">
    <?= screen_icon() ?>
    <h2><?= sprintf(__('Export %s', 'docalist-biblio'), $database->settings()->label) ?></h2>

    <p class="description">
        <?= __("Choisissez le format d'export.", 'docalist-biblio') ?>
    </p>

    <form action="" method="post">
        <ul class="exporters">
            <?php foreach($exporters as $exporter => $settings): ?>
                <li>
                    <input
                        type="radio"
                        class="radio"
                        name="exporter"
                        id="<?= $exporter ?>"
                        value="<?= $exporter ?>"
                        />

                    <h3>
                        <label title="Code exporter : <?= $exporter ?>" for="<?= $exporter ?>">
                            <?= $settings['label'] ?>
                        </label>
                    </h3>

                    <?php if (isset($settings['description'])): ?>
                        <p class="description">
                            <?= $settings['description'] ?>
                        </p>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="submit buttons">
            <button type="submit" class="run-export" disabled="disabled">
                <?=__("Lancer l'export...", 'docalist-biblio') ?>
            </button>
        </div>

        <?php foreach($args as $key => $value): ?>
            <?php if (is_array($value)): ?>
                <?php foreach($value as $$item): ?>
                    <input type="hidden" name="<?=$key ?>[]" value="<?= esc_attr($item) ?>" />
                <?php endforeach; ?>
            <?php else: ?>
                <input type="hidden" name="<?=$key ?>" value="<?= esc_attr($value) ?>" />
            <?php endif; ?>
        <?php endforeach; ?>
    </form>
</div>

<style type="text/css">
.exporters li h3 {
    display: inline;
}
</style>

<script type="text/javascript">
// Active le bouton "run" une fois que le format a été choisi
jQuery(document).on('click', '.radio', function(event) {
    jQuery('.run-export').attr('disabled', false);
});
</script>