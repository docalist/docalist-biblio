<?php
/**
* @package     Prisme
* @subpackage  modules
* @author      Daniel Ménard <daniel.menard.35@gmail.com>
*/
namespace Docalist\Biblio\Import;

use Iterator;
use stdClass;

/**
 * Classe permettant de lire les fichiers BDSP au format texte tabulé.
 *
 * Cette classe implémente l'interface Iterator. L'utilisation classique est une simple boucle :
 * <code>
 * foreach(new PrismeReader($fileToLoad) as $index => $record)
 * {
 *     // chargement du index-ième enregistrement
 *     // $record est un tableau de la forme nom de champ => contenu du champ
 * }
 *
 * </code>
 *
 * @package     Prisme
 * @subpackage  modules
 * @author      Daniel Ménard <daniel.menard.35@gmail.com>
 */
class BdspCsv implements Iterator
{
    /**
     * Délimiteur utilisé entre les champs.
     *
     * @var string
     */
    const FIELD_DELIMITER = ';';

    /**
     * Longueur maximale d'un enregistrement.
     *
     * @var int
     */
    const MAX_LINE_LENGTH = 65536;

    /**
     * Path du fichier en cours.
     *
     * @var string
     */
    private $path;

    /**
     * Handle du fichier en cours.
     *
     * @var resource
     */
    protected $file;

    /**
     * Noms des champs.
     *
     * @var array
     */
    protected $headers;

    /**
     * Données de l'enregistrement en cours.
     *
     * @var array
     */
    protected $data;

    /**
     * Numéro de l'enregistrement en cours.
     *
     * @var int
     */
    protected $recordNumber;

    /**
     * Flag indiquant si la fin de fichier a été atteinte
     *
     * @var bool
     */
    protected $eof;

    /**
     * Indique s'il faut convertir les données de l'ancien format
     * (éclatement des champs Url, So, etc.)
     *
     * @var bool
     */
    protected $conversion = true;

    protected $fieldIndex;
    protected $handles = array();

    protected $cache;

    /**
     * Constructeur. Ouvre le fichier passé en paramètre.
     *
     * @param string $path
     */
    public function __construct($path, $conversion = true, $fieldIndex = true)
    {
        $this->conversion = $conversion;
        $this->path = $path;
        $this->file = fopen($path, 'rb');
        $this->fieldIndex = $fieldIndex;
    }


    /**
     * Destructeur. Ferme le fichier en cours.
     */
    public function __destruct()
    {
        fclose($this->file);
        foreach($this->handles as $file) {
            fclose($file);
        }
    }


    /**
     * Lit une ligne du fichier et retourne un tableau contenant les données.
     *
     * @return array|false retourne un tableau contenant les données ou false si la fin
     * du fichier est atteinte (dans ce cas, la propriété $eof est mise à true).
     */
    protected function read()
    {
        if (feof($this->file))
        {
            $this->eof = true;
            return false;
        }
        $data = fgetcsv($this->file, self::MAX_LINE_LENGTH, self::FIELD_DELIMITER);
        $data = array_map('utf8_encode', $data);

        return $data;
    }


    /**
     * Interface Iterator. Initialise une boucle foreach.
     *
     * Charge la ligne d'entête du fichier, initialise le numéro de l'enregistrement en cours et
     * le flag de fin de fichier puis charge le premier enregistrement.
     */
    function rewind()
    {
        fseek($this->file, 0);
        $this->headers = $this->read();
        $this->recordNumber = 0;
        $this->eof = false;
        $this->next();
    }


    /**
     * Interface Iterator. Charge le prochain enregistrement du fichier.
     *
     */
    function next()
    {
        $this->data = $this->read();
        if ($this->data === false) return;

        // Détection d'erreurs
        if (count($this->data) !== count($this->headers)) {
            echo "<p>Notice erronnée dans le fichier CSV : le nombre de champs dans la notice ne correspond pas au nombre de champs dans la ligne d'entête</p>";
            echo '<table border="1"><tr><th>Entête</th><th>Notice</th></tr>';
            $max = max(count($this->data), count($this->headers));
            for($i=0; $i < $max; $i++) {
                echo '<tr>';
                echo '<th>', isset($this->headers[$i]) ? $this->headers[$i] : '-', '</th>';
                echo '<td>', isset($this->data[$i])    ? $this->data[$i]    : '-', '</td>';
                echo '</tr>';
            }
            echo '</table>';
            return $this->next();
        }

        $this->data = array_combine($this->headers, $this->data);
        ++$this->recordNumber;

        // Supprime les champs vides
        $this->data = array_map('trim', $this->data); // trim all
        $this->data = array_filter($this->data, 'strlen'); // ne garde que ceux dont len !== 0
        if ($this->fieldIndex) {
            foreach($this->data as $key=>$value) {
                if (! isset($this->handles[$key])) {
                    $path = $this->path . "-$key.txt";
                    $this->handles[$key] = fopen($path, 'w');
                }
                fwrite($this->handles[$key], "$value\r\n");
            }
        }

        if ($this->conversion) $this->data = $this->convert();
    }


    /**
     * Interface Iterator. Indique si la fin de fichier a été atteinte.
     *
     * @return bool
     */
    function valid()
    {
        return ! $this->eof;
    }


    /**
     * Interface Iterator. Retourne le numéro d'enregistrement en cours.
     *
     * @return int
     */
    function key()
    {
        return $this->recordNumber;
    }


    /**
     * Interface Iterator. Retourne les données de l'enregistrement en cours.
     *
     *  @return array
     */
    function current()
    {
        return $this->data;
    }
    protected $errors;
    protected function error($message) {
        $this->errors[] = $message;
    }

    public function convert()
    {
        // Réinitialise a liste des erreurs rencontrées
        $this->errors = array();

        // Commence à créer l'enreg
        $doc = new StdClass;

        // Stocke la notice d'origine
        $imported = '';
        foreach($this->data as $key=>$value) {
            $imported .= "$key:$value\n";
        }

        // Convertit les champs articles en tableaux de valeurs
        $repeatable = array_flip(explode(' ', 'AUT AUTS AUTCOLL AUTCOLS ORGCOMM DATEDIT DATORIGI ISBN ISSN TYPDOC TYPDOCB PAYS LANGUE VILED MOTSCLE1 NOUVDESC'));
        foreach($this->data as $name => & $value)
        {
            if (empty($value))
            {
                unset($this->data[$name]);
            }
            elseif (isset($repeatable[$name]))
            {
                $value = array_map('trim', explode('¨', $value));
            }
        }
        unset($value);
        $data = (object) $this->data;

        // REF
        if (isset($data->REF)) {
            $doc->ref = (int) $data->REF;
            if ($doc->ref != $data->REF) {
                $this->error ('Champ REF incorrect');
            }
            unset($data->REF);
        } else {
            $this->error ('Champ REF absent');
        }

        // AUT, AUTS
        foreach(array('AUT' => '', 'AUTS' => 'interviewer') as $AU => $defaultRole) {
            if (isset($data->$AU)) {
                foreach($data->$AU as $aut) {
//                    if (strpos($aut, 'CAYLA') !== false) die('here');
                    if (false !== $pt = strpos($aut, ':')) {
                        $aut = rtrim(substr($aut, 0, $pt));
                    }
                    if (preg_match('~^([A-Z-]+)\s*\(([^\)]+)\)[\s,/]*(.*)$~', $aut, $match)) {
                        $aut = array(
                            'name' => $match[1],
                            'firstname' => trim($match[2]),
                        );
                        $match[3] = trim($match[3]);
                        if (!$match[3] && $defaultRole) $match[3] = $defaultRole;
                        $match[3] && $aut['role'] = $match[3];

                        $doc->author[] = $aut;
                    } else {
                        $doc->author[] = array('name' => $aut);
                        $this->error ("Auteur invalide dans $AU : $aut");
                    }
                }
            }
        }
        unset($data->AUT, $data->AUTS);

        // Terminé
        $doc->imported = $imported;
        if ($this->errors) {
            $doc->errors = $this->errors;
        }

        unset($data->SO, $data->DATRI, $data->NO);

        $data=(array)$data;
        if ($data) $doc->todo = array_keys($data);

        return $doc;
    }
}
