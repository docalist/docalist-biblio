<?php
/**
 * This file is part of the "Docalist Biblio" plugin.
 *
 * Copyright (C) 2015-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist\Biblio\Export
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Biblio\Export\Views;

use Docalist\Biblio\Export\Plugin;

/**
 * Affiche un message "aucun format disponible"
 *
 * @var Plugin  $this
 * @var array   $types Types des notices (libellé => count)
 * @var int     $total Nombre total de hits obtenus (notices à exporter).
 * @var int     $max   Nombre maximum de notices exportables.
 */

// Crée le détail par type des notices qui seront exportées
if (count($types) === 1) {
    $detail = lcfirst(key($types));
} else {
    $detail = [];
    foreach ($types as $label => $count) {
        $detail[] = sprintf(__('%s : %d', 'docalist-biblio'), lcfirst($label), $count);
    }
    $detail = implode(', ', $detail);
}
?>
<p>
    Impossible d'exporter votre sélection avec les formats disponibles.
</p>

<ul>
    <li>
        Votre sélection contient plusieurs types de notices (<?=$detail ?>) mais
        les formats d'export disponibles de permettent pas de tout exporter en
        une seule étape.
    </li>

    <li>
        Réessayez en segmentant votre sélection par type : retournez sur la page
        précédente, utilisez la facette "type de document" pour faire une
        sous-sélection et relancez l'export. Recommencez ensuite pour chacun des
        types de notices à exporter.
    </li>
</ul>

<p style="float: right">
    <a href="javascript:history.back()">Retour à la page précédente</a>
</p>