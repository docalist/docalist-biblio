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

use Docalist\Type\TableEntry;

/**
 * Champ 'language" : langues du document catalogué.
 *
 * Ce champ répétable permet de préciser la langue du document catalogué : la langue des textes pour un document
 * écrit, les pistes audio disponibles pour un film, etc.
 *
 * Le champ est associé à une table d'autorité qui indique les langues possibles (par défaut : codes ISO à deux
 * lettres des langues de l'Union Européenne).
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class LanguageField extends TableEntry
{
    public static function loadSchema(): array
    {
        return [
            'name' => 'language',
            'repeatable' => true,
            'label' => __('Langue', 'docalist-biblio'),
            'description' => __(
                'Langue du document : langue des textes qui figurent dans le document, pistes audio disponibles...',
                'docalist-biblio'
            ),
            'table' => 'table:ISO-639-2_alpha3_EU_fr',
        ];
    }
}
