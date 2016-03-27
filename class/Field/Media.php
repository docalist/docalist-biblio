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

use Docalist\Type\TableEntry;

/**
 * Un support de document.
 */
class Media extends TableEntry
{
/*
    public function setupMapping(MappingBuilder $mapping)
    {
        $mapping->addField('media')->text()->filter();
    }

    public function mapData(array & $document)
    {
        $document['media'][] = $this->getEntryLabel();
    }
*/
}