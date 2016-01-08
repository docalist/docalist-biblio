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
namespace Docalist\Biblio\Type;

use Docalist\Type\Integer;
use Docalist\MappingBuilder;

/**
 * Le numéro de référence de la notice.
 */
class RefNumber extends Integer
{
    public function setupMapping(MappingBuilder $mapping)
    {
        $mapping->addField('ref')->integer();
    }

    public function mapData(array & $document)
    {
        $document['ref'] = $this->value();
    }
}