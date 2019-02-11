<?php
/**
 * This file is part of Docalist Biblio.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Biblio\Field;

use Docalist\Type\TableEntry;

/**
 * Champ "format" : étiquettes de collation.
 *
 * Ce champ répétable permet d'indiquer des mots-clés (étiquettes de collation) qui décrivent le format du
 * document catalogué, ses caractéristiques, sa composition et le matériel qui l'accompagne :
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
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class FormatField extends TableEntry
{
    /*
     * Remarque : sur le fond, ce champ est juste un type particulier d'indexation (d'ailleurs la table par
     * défaut est un théaurus). On pourrait envisager de le supprimer et d'utiliser le champ "topic" à la place.
     * Cependant, dans le formulaire de saisie, il est logique que le champ apparaisse dans le bloc "informations
     * bibliographiques" et non dans la partie "indexation". Donc on garde un champ distinct pour le moment,
     * à reconsidérer si un jour le champ topic supporte "explode" et qu'on peut mettre l'indexation "format"
     * au bon endroit.
     */

    public static function loadSchema()
    {
        return [
            'name' => 'format',
            'repeatable' => true,
            'label' => __('Format', 'docalist-biblio'),
            'description' => __(
                "Mots-clés (étiquettes de collation) indiquant les caractéristiques du document 
                (tableaux, annexes, références...)",
                'docalist-biblio'
            ),
            'table' => 'thesaurus:format',
        ];
    }
}
