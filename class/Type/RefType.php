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
namespace Docalist\Biblio\Type;

use Docalist\Type\ListEntry;
use Docalist\Biblio\Database;

/**
 * Le type docalist de la notice.
 */
class RefType extends ListEntry
{
    public static function loadSchema()
    {
        return [
            'label' => __('Type de fiche', 'docalist-biblio'),
            'description' => __('Type docalist de la fiche.', 'docalist-biblio'),
        ];
    }

    /**
     * Retourne la liste des types docalist disponibles.
     *
     * @return array Un tableau de la forme [Nom du type => Libellé du type]
     *
     * Remarque : le tableau retourné contient les libellés par défaut des types docalist, pas ceux qui ont été
     * définis par l'utilisateur dans les paramètres des bases docalist.
     */
    protected function getEntries()
    {
        static $types = null;

        // Initialise la liste des types disponibles lors du premier appel
        if (is_null($types)) {
            // Récupère les types disponibles (tableau de la forme type => classe php)
            $types = Database::getAvailableTypes();

            // Détermine le libellé de chaque type
            foreach ($types as $type => $class) {
                $types[$type] = $class::getDefaultSchema()->label();
            }
        }

        // Ok
        return $types;
    }
}
