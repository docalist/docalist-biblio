<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Biblio\Aggregation;

use Docalist\Search\Aggregation\Bucket\TermsAggregation;
use Docalist\Biblio\Field\Organisation;
use stdClass;

/**
 * Une agrégation standard de type "terms" sur le champ "organisation".
 */
class TermsOrganisation extends TermsAggregation
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
        !isset($options['title']) && $options['title'] = __('Organisme', 'docalist-search');
        parent::__construct('organisation.filter', $parameters, $options);
    }

    public function getBucketLabel(stdClass $bucket)
    {
        // Le bucket est de la forme 'nom¤prénom' (cf. Reference::map)
        list($name, $acronym, $city, $country) = explode('¤', $bucket->key);
        $org = new Organisation([
            'name' => $name,
            'acronym' => $acronym,
            'city' => $city,
            'country' => $country,
        ]);

        return $org->getFormattedValue(['format' => 'name']);
    }
}
