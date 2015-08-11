<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2015 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Biblio\Field;

use Docalist\Biblio\Type\String;
use Docalist\Forms\Select;
use Docalist\Biblio\Reference;
use Docalist\Search\MappingBuilder;
use Docalist\Biblio\DatabaseIndexer;

/**
 * Le type de la notice.
 */
class Type extends String {
    // Remarque (pour editForm et format) : on n'utilise pas le bon libellé
    // le libellé utilisé est celui qui figure dans le schéma par défaut
    // du type.
    // Il faudrait utiliser le libellé définit pour le TypeSettings qui figure
    // dans la base.
    // Problème : comment le champ peut-il savoir dans quelle base il est et
    // comment peut-il accéder aux settings correspondants ?
    public function editForm() {
        $types = [];
        foreach(Reference::types() as $type => $class) {
            $types[$type] = $class::defaultSchema()->label() . " ($type)";
        }

        $field = new Select($this->schema->name());
        $field->options($types);

        return $field;
    }

    public function mapping(MappingBuilder $mapping) {
        $mapping->field('type')->text()->filter();
    }

    public function map(array & $document) {
        $document['type'] = $this->format();
    }

    public function format() {
        $types = Reference::types();
        $type = $this->value();
        if (isset($types[$type])) {
            $class = $types[$type];
            $type = $class::defaultSchema()->label();
        }
        return $type;
    }
}