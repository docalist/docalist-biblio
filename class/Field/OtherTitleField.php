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

use Docalist\Type\TypedText;
use Docalist\Data\Indexable;
use Docalist\Data\Type\Collection\IndexableTypedValueCollection;
use Docalist\Biblio\Indexer\OtherTitleFieldIndexer;

/**
 * Champ "othertitle" : autres titres du document.
 *
 * Ce champ répétable permet d'indiquer d'autres titres associés au document catalogué mais différents du titre
 * exact indiqué dans le champ title) : un complément de titre, un sous-titre, le titre de la série, un sigle ou
 * un titre abrégé, l'ancien titre du document, etc.
 *
 * Chaque occurence du champ othertitle comporte deux sous-champs :
 * - `type` : type de titre,
 * - `value` : titre.
 *
 * Le sous-champ type est associé à une table d'autorité qui indique les valeurs possibles ("table:titles" par défaut).
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class OtherTitleField extends TypedText implements Indexable
{
    /**
     * {@inheritDoc}
     */
    public static function loadSchema(): array
    {
        return [
            'name' => 'othertitle',
            'repeatable' => true,
            'label' => __('Autre titre', 'docalist-biblio'),
            'description' => __('Autre titre du document : titre du dossier, ancien titre...', 'docalist-biblio'),
            'fields' => [
                'type' => [
                    'table' => 'table:titles',
                    'label' => __('Type de titre', 'docalist-biblio'),
                ],
                'value' => [
                    'label' => __('Autre titre', 'docalist-biblio'),
                ]
            ]
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function getCollectionClass(): string
    {
        return IndexableTypedValueCollection::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getIndexerClass(): string
    {
        return OtherTitleFieldIndexer::class;
    }
}
