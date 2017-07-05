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
namespace Docalist\Biblio\Field;

use Docalist\Type\TableEntry;

/**
 * Une étiquette de collation.
 */
/**
 * Format du document.
 *
 * Ce champ contient des tags (des étiquettes de collation) qui décrivent le format, les caractéristiques et la
 * composition du document catalogué :
 *
 * - contenu (bibliographie, annexes, cartes, photos, glossaire...),
 * - matériel d'accompagnement (dvd, livret...),
 * - couleur ou langues (n&b, vost, audio-description...),
 * - sous-titres disponibles,
 * - format des fichiers (pdf, mp3...),
 * - périodicité (mensuel, annuel...),
 * - etc.
 *
 * Le champ est associé à une table d'autorité qui indique les valeurs possibles ("thesaurus:format" par défaut).
 */
class Format extends TableEntry
{
    public static function loadSchema()
    {
        return [
            'label' => __('Format', 'docalist-biblio'),
            'description' => __(
                "Etiquettes de collation indiquant le contenu du document (tableaux, annexes, références...)",
                'docalist-biblio'
            ),
            'table' => 'thesaurus:format',
        ];
    }
}
