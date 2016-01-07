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

/**
 * Une mention d'édition
 */
class Edition extends Text
{
    public function setupMapping(MappingBuilder $mapping)
    {
        $mapping->addField('edition')->text();
    }

    public function mapData(array & $document)
    {
        $document['edition'][] = $this->value();
    }
}
