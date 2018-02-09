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
 * Champ "owner" : titulaire de la notice.
 *
 * Champ répétable.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class OwnerField extends Text
{
    /*
     * Il y a un problème de définition du rôle exact de ce champ. Qu'est-ce qu'il contient exactment ?
     * - code du producteur de la notice ?
     * - code de celui qui l'a corrigée ou validée ?
     * - code du détenteur du document catalogué (i.e. qui a des exemplaires) ?
     * - code identifiant les comptes qui peuvent éditer la notice ? (en plus de l'auteur wordpress)
     * etc.
     *
     * A revoir.
     */

    public static function loadSchema()
    {
        return [
            'name' => 'owner',
            'repeatable' => true,
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
