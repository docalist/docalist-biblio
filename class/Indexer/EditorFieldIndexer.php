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

namespace Docalist\Biblio\Indexer;

use Docalist\Biblio\Indexer\CorporationFieldIndexer;

/**
 * Indexeur pour le champ "editor".
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class EditorFieldIndexer extends CorporationFieldIndexer
{
    /**
     * {@inheritdoc}
     */
    protected function getAttributeLabel(string $attribute, string $type = ''): string
    {
        switch ($attribute) {
            case 'search':
                return __('Recherche sur les éditeurs indiqués dans les références docalist.', 'docalist-biblio');

            case 'filter':
                return __('Filtre sur les éditeurs indiqués dans les références docalist.', 'docalist-biblio');

            case 'suggest':
                return __('Autocomplete sur les éditeurs indiqués dans les références docalist.', 'docalist-biblio');

            case 'sort':
                return __('Tri sur le premier éditeur indiqué dans les références docalist.', 'docalist-biblio');
        }

        return parent::getAttributeLabel($attribute, $type);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAttributeDescription(string $attribute, string $type = ''): string
    {
        switch ($attribute) {
            case 'search':
                return __(
                    "Contient le nom, le sigle, la ville et le pays de l'éditeur indiqué dans les
                    références docalist. Supporte la recherche par mot, par troncature et par
                    expression (sans tenir compte de l\'ordre des mots).",
                    'docalist-biblio'
                );

            case 'filter':
                return __(
                    'Contient des valeurs structurées de la forme "nom¤sigle¤ville¤pays".',
                    'docalist-biblio'
                );

            case 'suggest':
                return __(
                    'Contient des valeurs structurées de la forme "nom¤sigle¤ville¤pays".',
                    'docalist-biblio'
                );

            case 'sort':
                return __(
                    'Version en minuscules sans accents ni signes de ponctuation du nom
                    du premier éditeur indiqué dans la référence docalist.',
                    'docalist-biblio'
                );
        }

        return parent::getAttributeDescription($attribute, $type);
    }
}
