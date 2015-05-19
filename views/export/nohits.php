<?php
/**
 * This file is part of the "Docalist Biblio Export" plugin.
 *
 * Copyright (C) 2015-2015 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist\Biblio\Export
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     SVN: $Id$
 */
namespace Docalist\Biblio\Export\Views;

/**
 * Affiche le message "aucune réponse".
 *
 * Cette vue est affichée quand la dernière requête exécutée ne donne aucune
 * réponses.
 * Par défaut, on se contente d'afficher la vue "norequest".
 */
echo $this->view('docalist-biblio-export:norequest');
?>

<small>La dernière requête exécutée ne donne aucune réponse.</small>
