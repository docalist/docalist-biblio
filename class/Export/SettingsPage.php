<?php
/**
 * This file is part of the "Docalist Biblio Export" plugin.
 *
 * Copyright (C) 2015-2015 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist\Biblio\Export
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Biblio\Export;

use Docalist\AdminPage;

/**
 * Options de configuration du plugin.
 */
class SettingsPage extends AdminPage
{
    /**
     * Action par défaut du contrôleur.
     *
     * @var string
     */
    protected $defaultAction = 'ExportSettings';

    /**
     * Paramètres du plugin.
     *
     * @var Settings
     */
    protected $settings;

    /**
     * Crée la page de réglages des paramètres du plugin.
     *
     * @param Settings $settings Paramètres du plugin.
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;

        parent::__construct(
            'docalist-biblio-export-settings',                // ID
            'options-general.php',                            // page parent
            __('Export et biblios', 'docalist-biblio-export') // libellé menu
        );

        // Ajoute un lien "Réglages" dans la page des plugins
        $filter = 'plugin_action_links_docalist-biblio-export/docalist-biblio-export.php';
        add_filter($filter, function ($actions) {
            $action = sprintf(
                '<a href="%s" title="%s">%s</a>',
                esc_attr($this->url()),
                $this->menuTitle(),
                __('Réglages', 'docalist-biblio-export')
            );
            array_unshift($actions, $action);

            return $actions;
        });
    }

    /**
     * Paramètres de l'export.
     */
    public function actionExportSettings()
    {
        if ($this->isPost()) {
            try {
                $_POST = wp_unslash($_POST);
                $this->settings->exportpage = (int) $_POST['exportpage'];
                $this->settings->limit = $_POST['limit'];

                // $settings->validate();
                $this->settings->save();

                docalist('admin-notices')->success(
                    __('Options enregistrées.', 'docalist-biblio-export')
                );

                return $this->redirect($this->url($this->defaultAction()), 303);
            } catch (Exception $e) {
                docalist('admin-notices')->error($e->getMessage());
            }
        }

        return $this->view('docalist-biblio-export:settings/export', [
            'settings' => $this->settings,
        ]);
    }
}
