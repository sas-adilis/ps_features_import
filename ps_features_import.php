<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class Ps_Features_Import extends Module {

    const SEPARATOR_COMMA = ',';
    const SEPARATOR_SEMICOLON = ';';
    const SEPARATOR_TAB = "\t";

    function __construct()
    {
        $this->name = 'ps_features_import';
        $this->author = 'Adilis';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->displayName = $this->l('Import / Export features');
        $this->description = $this->l('Import and export features in CSV format easily');

        parent::__construct();
    }

    public function getContent() {
        if (\Tools::isSubmit('submit'.$this->name.'Module')) {
            /** TODO: form validation **/
            if (!count($this->context->controller->errors)) {
                $redirect_after = $this->context->link->getAdminLink('AdminModules', true);
                $redirect_after .= '&conf=4&configure='.$this->name.'&module_name='.$this->name;
                \Tools::redirectAdmin($redirect_after);
            }
        }

        return $this->renderForm();
    }

    private function renderForm() {
        $helper = new \HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = \Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit'.$this->name.'Module';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false);
        $helper->currentIndex .= '&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = \Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'fields_value' => [
                'import_id_feature' => \Tools::getValue('import_id_feature'),
                'import_separator' => \Tools::getValue('import_separator', self::SEPARATOR_SEMICOLON),
                'import_have_header' => \Tools::getValue('import_have_header', false),
            ]
        ];

        return $helper->generateForm([
            [
                'form' => [
                    'legend' => [
                        'title' => $this->l('Import'),
                        'icon' => 'icon-cogs'
                    ],
                    'input' => [
                        [
                            'type' => 'file',
                            'name' => 'import_file',
                            'id' => 'import_file',
                            'label' => $this->l('File to upload'),
                            'required' => true
                        ],
                        [
                            'type' => 'select',
                            'name' => 'import_id_feature',
                            'label' => $this->l('Please select a feature'),
                            'required' => true,
                            'options' => [
                                'default' => ['value' => null, 'label' => $this->l('Please select a feature')],
                                'query' => \Feature::getFeatures(\Context::getContext()->cookie->id_lang),
                                'id' => 'id_feature',
                                'name' => 'name'
                            ]
                        ],
                        [
                            'type' => 'select',
                            'name' => 'import_separator',
                            'label' => $this->l('Pick an option'),
                            'required' => true,
                            'options' => [
                                'default' => [
                                    'value' => null,
                                    'label' => $this->l('Pick an option'),
                                ],
                                'query' => [
                                    ['id' => self::SEPARATOR_SEMICOLON, 'name' => $this->l('Semicolon')],
                                    ['id' => self::SEPARATOR_COMMA, 'name' => $this->l('Comma')],
                                    ['id' => self::SEPARATOR_TAB, 'name' => $this->l('Tabulation')],
                                ],
                                'id' => 'id',
                                'name' => 'name',
                            ]
                        ],
                        [
                            'type' => 'switch',
                            'name' => 'import_have_header',
                            'required' => true,
                            'is_bool' => true,
                            'label' => $this->l('Contains headers'),
                            'values' => [
                                ['id' => 'active_on', 'value' => 1, 'label' => $this->l('Yes')],
                                ['id' => 'active_off', 'value' => 0, 'label' => $this->l('No')],
                            ]
                        ]
                    ],
                    'submit' => [
                        'icon' => 'process-icon-download-alt',
                        'title' => $this->l('Import'),
                        'name' => 'submitImport'
                    ]
                ]
            ]
        ]);
    }
}
