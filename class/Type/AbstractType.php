<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012, 2013 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     $Id$
 */
namespace Docalist\Biblio\Type;

use Docalist\Biblio\Entity\Reference;
use Docalist\Biblio\TypeSettings;
use Docalist\Biblio\FieldSettings;

use Docalist\Forms\Input;
use Docalist\Forms\Select;
use Docalist\Forms\Table;
use Docalist\Forms\Hidden;

use Docalist\Utils;

use Docalist\Table\TableManager;

use Exception;
use Docalist\Forms\Checklist;

/**
 * Classe de base pour les types de documents de docalist-biblio.
 */
class AbstractType extends TypeSettings /* extends Reference */ {
/*
    public function __construct(array $data = null) {
//         echo '<pre>';
//         debug_print_backtrace();
//         var_export($data);
//         echo '</pre>';
        if (isset($data['fields'])) {
            $schema = (new Reference())->schema();

            foreach($data['fields'] as &$def) {
                if (! isset($def['name'])) {
                    throw new Exception('champ sans nom');
                }

                $name = $def['name'];
                if ($name === 'group') {

                } else {
                    $field = $schema->field($name);
                    ! isset($def['label']) && $def['label'] = $field->label();
                    ! isset($def['description']) && $def['description'] = $field->description();
                }
            }
        }
        parent::__construct($data);
    }
*/
    /**
     * Retourne le formulaire à utiliser pour créer une notice de ce type.
     *
     * @return Fragment|null
     */
/*
   //TODO
    public function createForm(){
        die('her');
        return null;
    }
*/

    /**
     * Retourne les formulaires utilisés pour saisir une notice de ce type.
     *
     * @return Fragment[] Un tableau de la forme id metabox => form fragment
     */
/*
    public function metaboxes() {
        die('IN abstractType.metaboxes()');
        $metaboxes = array();

        $type = Utils::classname($this);

        $box = new Fragment();
        foreach($this->__get('fields') as $field) {
            echo '<pre>';
            var_export($field);
            echo '</pre>';
            // Nouvelle métabox. Sauve la courante si non vide et crée une nouvelle
            if ($field->name === 'group') {
                if (count($box->fields()) !== 0) {
                    $id = $type . '-' . $box->fields()[0]->name();
                    $metaboxes[$id] = $box;
                }

                $box = new Fragment();
                $box->label($field->label)->description($field->description);
            } else {
                $field = $this->createField($field);
                // $field->label($def->label)->description($def->label);
                $box->add($field);
            }
        }

        if (count($box->fields()) !== 0) {
            $id = $type . '-' . $box->fields()[0]->name();
            $metaboxes[$id] = $box;
        }
        var_dump($metaboxes);
         die();
        return $metaboxes;

    }
*/
}