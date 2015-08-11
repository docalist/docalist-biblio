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
use Docalist\Forms\Input;
use Docalist\Search\MappingBuilder;
use Docalist\Biblio\DatabaseIndexer;

/**
 * Le titre de la notice.
 */
class Title extends String {
    public function editForm() {
        $field = new Input($this->schema->name());
        $field->addClass('large-text');//->attribute('id', 'DocTitle');

        return $field;
    }

    public function mapping(MappingBuilder $mapping) {
        DatabaseIndexer::standardMapping('post_title', $mapping);
    }

    public function map(array & $document) {
        DatabaseIndexer::standardMap('post_title', $this->value(), $document);
    }
}