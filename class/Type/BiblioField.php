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
namespace Docalist\Biblio\Type;

use Docalist\Forms\Fragment;

/**
 * Interface pour un champ de premier niveau dans une Reference.
 */
interface BiblioField {
    /**
     * Retourne le formulaire de paramètres utilisé par ce champ dans la
     * l'écran de paramétrage de la grille de saisie.
     *
     * @return Fragment
     */
    public function settingsForm();

    /**
     * Retourne le formulaire de saisie utilisé par ce champ dans la grille de
     * saisie/modification de notices.
     *
     * @return Fragment
     */
    public function editForm();
}