<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Biblio\Aggregation;

use Docalist\Search\Aggregation\Bucket\TermsAggregation;
use Docalist\Biblio\Field\Corporation;
use stdClass;

/**
 * Une agrégation standard de type "terms" sur le champ "corporation".
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class TermsCorporation extends TermsAggregation
{
    /**
     * Constructeur
     *
     * @param array $parameters     Autres paramètres de l'agrégation.
     * @param array $options        Options d'affichage.
     */
    public function __construct(array $parameters = [], array $options = [])
    {
        !isset($parameters['size']) && $parameters['size'] = 10;
        !isset($options['title']) && $options['title'] = __('Auteurs moraux', 'docalist-search');
        parent::__construct('corporation.filter', $parameters, $options);
    }

    public function getBucketLabel(stdClass $bucket)
    {
        // Le bucket est de la forme 'nom¤prénom' (cf. Reference::map)
        list($name, $acronym, $city, $country) = explode('¤', $bucket->key);
        $corp = new Corporation([
            'name' => $name,
            'acronym' => $acronym,
            'city' => $city,
            'country' => $country,
        ]);

        return $corp->getFormattedValue(['format' => 'name']);
    }
}