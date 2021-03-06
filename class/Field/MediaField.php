<?php
/**
 * This file is part of Docalist Biblio.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Docalist\Biblio\Field;

use Docalist\Data\Type\IndexableTableEntry;

/**
 * Champ "media" : mots-clés décrivant le support physique du document.
 *
 * Ce champ répétable permet de décrire le support physique sur lequel réside le document catalogué : pour un
 * livre, par exemple, il permet d'indiquer s'il s'agit d'un livre broché, d'un DVD, d'une clé USB, etc.
 *
 * Le champ est associé à une table d'autorité qui indique les valeurs possibles ("thesaurus:medias" par défaut).
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class MediaField extends IndexableTableEntry
{
    /*
     * Remarque : sur le fond, ce champ est juste un type particulier d'indexation (d'ailleurs la table par
     * défaut est un thésaurus). On pourrait envisager de le supprimer et d'utiliser le champ "topic" à la place.
     * On garde un champ distinct pour le moment, à reconsidérer si un jour le champ topic supporte "explode" et
     * qu'on peut mettre l'indexation "media" où on veut dans le formulaire de saisie.
     */

    /**
     * {@inheritDoc}
     */
    public static function loadSchema(): array
    {
        return [
            'name' => 'media',
            'repeatable' => true,
            'label' => __('Support', 'docalist-biblio'),
            'description' => __('Support physique du document (imprimé, numérique, dvd...)', 'docalist-biblio'),
            'table' => 'thesaurus:medias',
        ];
    }
}
