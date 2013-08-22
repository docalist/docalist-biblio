<?php
namespace Docalist\Biblio\Entity;

use WP_UnitTestCase;

class EntityTestCase extends WP_UnitTestCase {

    protected function checkIs($object, $field, $type) {
        $value = $object->$field;

        $coll = false;
        if (substr($type, -1) === '*') {
            $coll = true;
            $type = substr($type, 0, -1);
        }

        // Scalaire
        if (in_array($type, array('bool','boolean', 'float', 'integer', 'int', 'string'))) {
            if ($coll) {
                $this->assertInstanceOf('Docalist\Data\Entity\Collection', $value);
                foreach ($value as $key => $item) {
                    $this->assertInternalType('int', $key, "Type(key($field)) === 'int'");
                    $this->assertInternalType($type, $item, "Type($field[$key]) === $type");
                }
            } else {
                $this->assertInternalType($type, $value, "Type($field) === '$type'");
            }
        }

        // Objets
        else {
            // $type = __NAMESPACE__ . '\\' . $type;
            if ($coll) {
                $this->assertInstanceOf('Docalist\Data\Entity\Collection', $value);
                foreach ($value as $key => $item) {
                    $this->assertInternalType('int', $key, "Collection offset is an int");
                    $this->assertInstanceOf($type, $item, "Type($field[$key]) === '$type'");
                }
            } else {
                $this->assertInstanceOf($type, $value, "Type($field) === '$type'");
            }
        }
    }
}
