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
 * @version     $Id$
 */
namespace Docalist\Biblio\Field;

use Docalist\Biblio\Type\String;
use Docalist\Search\MappingBuilder;

/**
 * Une mention d'édition
 */
class Edition extends String {
    public function mapping(MappingBuilder $mapping) {
        $mapping->field('edition')->text();
    }

    public function map(array & $document) {
        $document['edition'][] = $this->value();
    }
}