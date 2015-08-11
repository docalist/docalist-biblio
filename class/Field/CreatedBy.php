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
use Docalist\Search\MappingBuilder;
use Docalist\Biblio\DatabaseIndexer;
use WP_User;

/**
 * L'auteur WordPress de la notice (login).
 */
class CreatedBy extends String {
    public function mapping(MappingBuilder $mapping) {
        DatabaseIndexer::standardMapping('post_author', $mapping);
    }

    public function map(array & $document) {
        DatabaseIndexer::standardMap('post_author', $this->value(), $document);
    }

    public function format() {
        $author = get_user_by('id', $this->value()); /* @var $author WP_User */
        return $author->display_name;
    }
}