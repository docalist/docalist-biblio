<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2015 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Biblio\Field;

use Docalist\Biblio\Type\TypedRelation;

/**
 * Relation
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class Relation extends TypedRelation
{
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
