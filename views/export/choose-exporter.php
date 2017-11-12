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

use Docalist\Biblio\Database;
use Docalist\Forms\Form;
use Symfony\Component\HttpFoundation\Cookie;
use Docalist\Biblio\Pages\ImportPage;

/**
 * Export de notices : choix du format d'export.
 *
 * @var ImportPage $this
 * @var Database $database Base de données en cours.
 * @var array $formats Liste des formats d'exports disponibles.
 * @var string $args Arguments supplémentaires à transmettre.
 */

// Classe les formats d'export par convertisseur et par exporteur
$converters = [];
foreach($formats as $name => $format) {
    $converter = $format['converter'];
    if (!isset($converters[$converter])) {
        $converters[$converter] = [];
    }

    $exporter = $format['exporter'];
    if (!isset($converters[$converter][$exporter])) {
        $converters[$converter][$exporter] = [];
    }

    $converters[$converter][$exporter][$name] = $format['label'];
}
?>
<div class="wrap">
    <?= screen_icon() ?>
    <h2><?= sprintf(__("Export %s : choix du format d'export", 'docalist-biblio'), $database->settings()->label()) ?></h2>

    <p class="description">
        <?= sprintf(__("Le tableau ci-dessous liste les formats d'export disponibles pour la base %s. ", 'docalist-biblio'), $database->settings()->label()) ?>
        <?= __("Survolez les options pour obtenir une bulle d'aide avec des informations complémentaires. ", 'docalist-biblio') ?>
        <br />
        <?= __("Choisissez le format d'export à utiliser pour sélectionner le format des notices que vous voulez obtenir et le type de fichier à générer puis cliquez sur l'un des boutons en bas de page. ", 'docalist-biblio') ?>
        <br />
        <?= __("Remarque : le format choisi sera enregistré comme option par défaut pour la prochaine fois. ", 'docalist-biblio') ?>
    </p>

    <form action="" method="post">
        <table class="widefat export-formats">
            <tr>
                <th><?= __("Format des notices", 'docalist-biblio') ?></th>
                <th><?= __("Fichier généré", 'docalist-biblio') ?></th>
                <th><?= __("Format d'export", 'docalist-biblio') ?></th>
            </tr>
            <?php $i = 0; foreach($converters as $converter => $exporters): ?>
                <?php $export = 0; foreach($exporters as $exporter => $options): ?>
                    <tr class="<?= $i % 2 ? 'alt' : '' ?>">
                        <td class="converter <?=$converter::id()?>">
                            <?php if ($export === 0) :?>
                                <span title="<?=esc_attr($converter::description()) ?>">
                                    <?=$converter::label()?>
                                </span>
                            <?php endif;?>
                        </td>
                        <td class="exporter <?=$exporter::id()?>">
                            <span title="<?=esc_attr($exporter::description())?>">
                                <?=$exporter::label()?>
                            </span>
                        </td>
                        <td class="format">
                            <?php foreach($options as $name => $label): ?>
                                <label title="Nom de code du format : <?= $name ?>">
                                    <input
                                        type="radio"
                                        name="format"
                                        id="<?= $name ?>"
                                        value="<?= $name ?>"
                                        data-converter="<?=$converter::id()?>"
                                        data-exporter="<?=$exporter::id()?>"
                                        data-binary="<?=var_export($exporter::defaultSettings()['binary'])?>"
                                    />
                                    <?= $label ?>
                                </label>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                <?php $i++; $export++; endforeach; ?>
            <?php endforeach; ?>
        </table>

        <div class="options">
            <label>
                <input type="checkbox" name="zip" value="1" />
                Créer une archive compressée au <a href="http://fr.wikipedia.org/wiki/ZIP_(format_de_fichier)">format ZIP</a>.
            </label>
        </div>

        <div class="submit buttons"> <!-- button-group -->
            <button name="mode" value="download" type="submit" class="button button-primary">
                <?=__("Enregistrer le fichier...", 'docalist-biblio') ?>
            </button>
            <button name="mode" value="display" type="submit" class="button button-secondary">
                <?=__("Afficher les notices...", 'docalist-biblio') ?>
            </button>
            <button name="mode" value="mail" type="submit" class="button button-secondary">
                <?=__("Envoyer dans ma messagerie...", 'docalist-biblio') ?>
            </button>
        </div>

        <?php foreach($args as $key => $value): ?>
            <?php if (is_array($value)): ?>
                <?php foreach($value as $item): ?>
                    <input type="hidden" name="<?=$key ?>[]" value="<?= esc_attr($item) ?>" />
                <?php endforeach; ?>
            <?php else: ?>
                <input type="hidden" name="<?=$key ?>" value="<?= esc_attr($value) ?>" />
            <?php endif; ?>
        <?php endforeach; ?>
    </form>
</div>

<style type="text/css">
    .export-formats {
        width: auto;
    }
    .converter, .exporter {
        width: 150px;
    }
    .format {
        width: 250px;
    }
    .converter span, .exporter span, .format label {
        cursor: help;
        display: block;
        padding: 2px 10px;
    }
    .export-formats .wp-ui-highlight {
        font-weight: bold;
        border-radius: 5px;
    }
*/
</style>

<?php
    // nom du cookie
    $cookie = $database->settings()->name() . '-export';

    // nom du format d'export par défaut si pas de cookie
    reset($formats);
    $favorite = key($formats);

    // Gestion des cookies en javascript adaptée de :
    // https://developer.mozilla.org/en-US/docs/Web/API/document.cookie
?>
<script type="text/javascript">
    jQuery(document).ready(function($) {

        // Nom du cookie utilisé pour mémoriser le format d'export choisi
        var cookie = "<?=$cookie?>";

        // Quand l'utilisateur choisit un format :
        // - met la ligne en surbrillance
        // - mémorise son choix dans le cookie
        $(document).on('click', 'input[name=format]', function(event) {
            var $this = $(this);
            var hl= 'wp-ui-highlight';

            // Supprime ce qui est actuellement surligné
            $('.' + hl).removeClass(hl);

            // Surligne : le libellé du format, l'exporteur et le convertisseur
            $this.parents('label').addClass(hl);
            $('.exporter span', $this.parents('tr')).addClass(hl);
            $('.' + $this.data('converter') + ' span').addClass(hl);

            // Enregistre le format choisi sous forme de cookie
            var value = encodeURIComponent(this.value);
            var expires = '; expires=' + new Date(9999, 12, 31).toUTCString(); // 1 an
            var domain = ''; // '; domain=<?=COOKIE_DOMAIN?>' ;
            var path = ''; // '; path=' + window.location.toString();

            document.cookie = cookie + '=' + value + expires + domain + path;
        });

        // Quand l'utilisateur clique sur un convertisseur ou un exporteur,
        // sélectionne le premier format dispo
        $(document).on('click', '.converter span, .exporter span', function() {
            $('input:first', $(this).parents('tr')).click();
        });

        // Active ou désactive le bouton "afficher" selon que l'export génère
        // un fichier texte ou un fichier binaire ou zip.
        $(document).on('change', 'input[name=format], input[name=zip]', function() {
            var binary = $('input[name=format]:checked').data('binary') || $('input[name=zip]').is(':checked');
            $('button[name=mode][value=inline]')
                .attr('disabled', binary)
                .attr('title', binary ? "Non disponible pour les fichiers binaires et l'option zip" : '');
        });

        // Initialisation : sélectionne et donne le focus au format par défaut
        var favorite = decodeURIComponent(document.cookie.replace(new RegExp("(?:(?:^|.*;)\\s*" + cookie.replace(/[\-\.\+\*]/g, "\\$&") + "\\s*\\=\\s*([^;]*).*$)|^.*$"), "$1")) || "<?=$favorite?>";

        $('#' + favorite).click().focus();
    });
</script>
