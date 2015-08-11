<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2014 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Biblio\Type;

use Docalist\Schema\Schema;

/**
 * Un champ objet répétable qui se comporte comme un ensemble de champs.
 *
 * Ce type d'objet contient un champ avec une table d'autorité associée qui
 * permet de classer les différents articles.
 *
 * Le champ peut alors être affiché soit comme un champ unique, soit
 * comme plusieurs champs ('explode' i.e. 'vue éclatée'). Dans ce cas, c'est le
 * libellé qui figure dans la table qui sera utilisé pour chacune des
 * catégories.
 *
 * Exemples de multi-champs :
 * - author : classement par role,
 * - organisation : classement par role,
 * - othertitle : classement par type,
 * - translation : classement par language,
 * - date : classement par type,
 * - number : classement par type,
 * - extent : classement par type,
 * - editor : classement par role,
 * - topic : classement par type,
 * - content : classement par type,
 * - link : classement par type,
 * - relation : classement par type.
 *
 * Le nom de la propriété de l'objet utilisée comme clé de classement DOIT être
 * indiquée dans la propriété $groupkey (i.e. les classes descendantes doivent
 * surcharger la propriété).
 */
class MultiField extends Object {

    static protected $groupkey;
    static protected $table2ForGroupkey = false;

    /**
     * Retourne le nom du champ utilisé comme clé de classement
     */
    public static final function groupkey() {
        return static::$groupkey;
    }

    public function format(Repeatable $parent = null) {
        $content = self::callFormat($parent->schema->format(), $this, $parent);
        if ($parent->schema()->explode) {
            $label = $parent->lookup($this->__get(static::$groupkey)->value(), static::$table2ForGroupkey);
            return [$label, $content];
        }
        return $content;
    }
}