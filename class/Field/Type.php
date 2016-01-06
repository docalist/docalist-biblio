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
use Docalist\Forms\Select;
use Docalist\MappingBuilder;

/**
 * Le type de la notice.
 */
class Type extends Text
{
    // Remarque :
    // Pour getFormattedValue et getEditorForm, on n'utilise pas le bon libellé.
    // Le libellé utilisé est celui qui figure dans le schéma par défaut du
    // type alors qu'il faudrait utiliser le libellé définit pour le
    // TypeSettings qui figure dans la base.
    // Problème : comment le champ peut-il savoir dans quelle base il est et
    // comment peut-il accéder aux settings correspondants ?

    public function getFormattedValue($options = null)
    {
        $types = apply_filters('docalist_biblio_get_types', []);
        $type = $this->value();
        if (isset($types[$type])) {
            $type = $types[$type]::getDefaultSchema()->label();
        }
        return $type;
    }

    public function getEditorForm($options = null)
    {
        $types = apply_filters('docalist_biblio_get_types', []);
        foreach ($types as $type => $class) {
            $types[$type] = $class::getDefaultSchema()->label() . " ($type)";
        }

        $field = new Select($this->schema->name());
        $field->setOptions($types);

        return $field;
    }

    public function setupMapping(MappingBuilder $mapping)
    {
        $mapping->addField('type')->text()->filter();
    }

    public function mapData(array & $document)
    {
        $document['type'] = $this->getFormattedValue();
    }
}
