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

use Docalist\Data\Type\TypedRelation;

/**
 * Champ "relation" : relations entre le document catalogué et d'autres documents.
 *
 * Champ répétable.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class RelationField extends TypedRelation
{
    public static function loadSchema()
    {
        return [
            'name' => 'relation',
            'repeatable' => true,
            'label' => __('Relations', 'docalist-biblio'),
            'description' => __("Relations typées vers d'autres contenus.", 'docalist-biblio'),
            'fields' => [
                'type' => [
                    'table' => 'table:relations',
                ],
                'value' => [
                    'label' => __('Référence liée', 'docalist-biblio'),
                    'description' => __('ID WordPress de la référence liée.', 'docalist-biblio'),
                ],
            ],
        ];
    }

/*
    public function setupMapping(MappingBuilder $mapping)
    {
        $mapping->addField('relation')->integer();
        $mapping->addTemplate('relation.*')->copyFrom('relation')->copyDataTo('relation');
    }

    public function mapData(array & $document) {
        $document['relation.' . $this->type()][] = $this->ref();
    }
*/
}
