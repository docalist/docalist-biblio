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

use Docalist\Type\Text;
use Docalist\MappingBuilder;
use Docalist\Biblio\DatabaseIndexer;

/**
 * Le statut wordpress de la notice.
 */
class Status extends Text
{
    public function setupMapping(MappingBuilder $mapping)
    {
        DatabaseIndexer::standardMapping('post_status', $mapping);
    }

    public function mapData(array & $document)
    {
        DatabaseIndexer::standardMap('post_status', $this->value(), $document);
    }
}
