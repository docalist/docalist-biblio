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

use Docalist\Forms\Fragment;
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
    public function __construct(array $data = null) {
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
    public function metaboxes() {
        $metaboxes = array();

        $type = Utils::classname($this);

        $box = new Fragment();
        foreach($this->__get('fields') as $field) {
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
        // var_dump($metaboxes);
        // die();
        return $metaboxes;

    }

    protected function createField(FieldSettings $def) {
        $name = $def->name;
        switch($name) {
            case 'ref':
                $field = new Input($name);
                break;

            case 'type':
                $types = apply_filters('docalist_biblio_get_types', array()); // code => class
                $types = array_keys($types);

                $field = new Select($name);
                $field->options($types);
                break;

            case 'genre':
                $table = $def->table[0];
                $field = new Select($name);
                $field->options($this->tableOptions($table));
                break;

            case 'media':
                $table = $def->table[0];
                $field = new Select($name);
                $field->options($this->tableOptions($table));
                break;

            case 'author':
                $roles = $def->table[0];

                $field = (new Table($name))->attribute('class', 'author');
                $field->input('name')
                      ->attribute('class', 'name');
                $field->input('firstname')
                      ->attribute('class', 'firstname');
                $field->select('role')
                      ->options($this->tableOptions($roles))
                      ->attribute('class', 'role');

                break;

            case 'organisation':
                $countries = $def->table[0];
                $roles = $def->table[1] ?: 'dclrefrole';

                $field = (new Table($name))->attribute('class', 'organisation');
                $field->input('name')
                      ->attribute('class', 'name');
                $field->input('city')
                      ->attribute('class', 'city');
                $field->select('country')
                      ->options($this->tableOptions($countries))
                      ->attribute('class', 'country');
                $field->select('role')
                      ->options($this->tableOptions($roles))
                      ->attribute('class', 'role');
                break;

            case 'title':
                $field = new Input($name);
                $field->addClass('large-text')->attribute('id', 'DocTitle');
                break;

            case 'othertitle':
                $titles = $def->table[0];

                $field = new Table($name);
                $field->select('type')->options($this->tableOptions($titles));
                $field->input('title');
                break;

            case 'translation':
                $languages = $def->table[0];

                $field = new Table($name);
                $field->select('language')->options($this->tableOptions($languages));
                $field->input('title');
                break;

            case 'date':
                $field = new Input($name);

                break;

            case 'journal':
                $field = new Input($name);
                $field->attribute('class', 'large-text');

                break;

            case 'issn':
                $field = new Input($name);

                break;

            case 'volume':
                $field = new Input($name);

                break;

            case 'issue':
                $field = new Input($name);

                break;

            case 'language':
                $languages = $def->table[0];

                $field = new Select($name);
                $field->options($this->tableOptions($languages));

                break;

            case 'pagination':
                $field = new Input($name);
                break;

            case 'format':
                $field = new Input($name);
                break;

            case 'isbn':
                $field = new Input($name);
                break;

            case 'editor':
                $countries = $def->table[0];

                $field = new Table($name);
                $field->input('name');
                $field->input('city');
                $field->select('country')->options($this->tableOptions($countries));
                break;

            case 'edition':
                $field = new Table($name);
                $field->input('type');
                $field->input('value');
                break;

            case 'collection':
                $field = new Table($name);
                $field->input('name');
                $field->input('number');
                break;

            case 'event':
                $field = new Table($name);
                $field->input('title');
                $field->input('date');
                $field->input('place');
                $field->input('number');
                break;

            case 'degree':
                $field = new Table($name);
                $field->input('title');
                $field->input('level');
                break;

            case 'abstract':
                $languages = $def->table[0];

                $field = new Table($name);
                $field->select('language')->options($this->tableOptions($languages));
                $field->textarea('content');
                break;

            case 'topic':
//                 $table = $def->table[0] ?: 'dcllanguage'; // TODO mettre la bonne table
// var_dump($def->table->toArray());
// die();
                $field = new Table($name);
                $field->select('type');//->options($def->table->toArray());
                $field->input('term');
                break;

            case 'note':
                $notes = $def->table[0];

                $field = new Table($name);
                $field->select('type')->options($this->tableOptions($notes));
                $field->textarea('content');
                break;

            case 'link':
                $links = $def->table[0];

                $field = new Table($name);
                $field->input('url');
                $field->select('type')->options($this->tableOptions($links));
                $field->input('label');
                $field->input('date');
                break;

            case 'doi':
                $field = new Input($name);
                break;

            case 'relations':
                $table = $def->table[0] ?: 'dclrefrelation';

                $field = new Table($name);
                $field->select('type')->options($this->taxonomy($table));
                $field->input('ref');
                break;

            case 'owner':
                $field = new Input($name);
                break;

            case 'creation':
                $field = new Table($name);
                $field->input('date');
                $field->input('by');
                break;

            case 'lastupdate':
                $field = new Table($name);
                $field->input('date');
                $field->input('by');
                break;

            case 'status':
                $field = new Input($name);
                break;

            default:
                throw new \Exception("Champ inconnu : '$name'");
        }

        $field->label($def->label)->description($def->description);

        return $field;
    }

    protected function taxonomy($name) {
        $terms = get_terms($name, array(
            'hide_empty' => false,
        ));

        $result = array();
        foreach ($terms as $term) {
            $result[$term->slug] = $term->name;
        }

        return $result;
    }

    protected function tableOptions($table, $fields = 'code,label') {
        /* @var $tableManager TableManager */
        $tableManager = apply_filters('docalist_get_table_manager', null);
        return $tableManager->get($table)->search($fields);
    }
}