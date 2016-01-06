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

use Docalist\Type\DateTime;
use Docalist\Biblio\DatabaseIndexer;
use Docalist\MappingBuilder;

/**
 * La date de dernière modification de la notice.
 */
class LastUpdate extends DateTime {
    public function setupMapping(MappingBuilder $mapping)
    {
        DatabaseIndexer::standardMapping('post_modified', $mapping);
    }

    public function mapData(array & $document) {
        DatabaseIndexer::standardMap('post_modified', $this->value(), $document);
    }
}