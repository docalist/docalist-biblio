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
 * Affiche le message "aucune requête en cours".
 *
 * Cette vue est affichée quand on n'a aucun transient ou que celui-ci a expiré.
 *
 * @var Plugin  $this
 */
$searchPage = get_permalink(docalist('docalist-search-engine')->searchPage());
$basketPage = docalist('basket-controller')->basketPageUrl();
?>

<p>Je ne sais pas ce que vous voulez exporter !</p>

<ul>
    <li>
        Pour exporter des notices,
        <a href="<?=esc_url($searchPage)?>">lancez une recherche</a>
        puis <b>cliquez sur l'un des liens "exporter"</b> qui vous sont proposés.
    </li>

    <li>
        Vous pouvez également constituer un <b>panier de notices</b> en sélectionnant
        celles qui vous intéressent. Ensuite,
        <a href="<?=esc_url($basketPage)?>">affichez votre panier</a>
        puis cliquez sur l'un des liens "exporter".
    </li>
</ul>

<p>
    <i>Remarque :</i> il se peut qu'il se soit écoulé trop longtemps depuis votre
    dernière recherche. Dans ce cas, relancez-votre requête et essayez à
    nouveau.
</p>

<p style="float: right">
    <a href="javascript:history.back()">Retour à la page précédente</a>
</p>
