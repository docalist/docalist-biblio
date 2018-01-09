<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Biblio\Field;

use Docalist\Type\Text;

/**
 * Un producteur.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class Owner extends Text
{
    public static function loadSchema()
    {
        return [
            'label' => __('Producteur de la notice', 'docalist-biblio'),
            'description' => __(
                'Personne ou organisme producteur de la notice.',
                'docalist-biblio'
            ),
        ];
    }

/*
    public function setupMapping(MappingBuilder $mapping)
    {
        $mapping->addField('owner')->text()->filter();
    }

    public function mapData(array & $document)
    {
        $document['owner'] = $this->value();
    }
*/
}
