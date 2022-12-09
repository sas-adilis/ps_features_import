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

            $this->processImport();
            $this->processExport();


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
                'export_id_feature' => \Tools::getValue('export_id_feature'),
                'export_separator' => \Tools::getValue('export_separator', self::SEPARATOR_SEMICOLON),
                'export_add_header' => \Tools::getValue('export_add_header', false),
                'export_id_manufacturer' => \Tools::getValue('export_id_manufacturer', false),
                'export_id_supplier' => \Tools::getValue('export_id_supplier', false),
                'export_include_empties' => \Tools::getValue('export_include_empties', false),
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
                            'label' => $this->l('Pick an selector'),
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
            ],
            [
                'form' => [
                    'legend' => [
                        'title' => $this->l('Export'),
                        'icon' => 'icon-cogs'
                    ],
                    'input' => [
                        [
                            'type' => 'select',
                            'name' => 'export_id_feature',
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
                            'name' => 'export_separator',
                            'label' => $this->l('Pick an selector'),
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
                            'type' => 'select',
                            'name' => 'export_id_manufacturer',
                            'multiple' => true,
                            'label' => $this->l('Filter by manufacturer'),
                            'options' => [
                                'query' => \Manufacturer::getManufacturers(false, \Context::getContext()->cookie->id_lang),
                                'id' => 'id_manufacturer',
                                'name' => 'name'
                            ]
                        ],
                        [
                            'type' => 'select',
                            'name' => 'export_id_supplier',
                            'multiple' => true,
                            'label' => $this->l('Filter by supplier'),
                            'required' => true,
                            'options' => [
                                'query' => \Supplier::getSuppliers(false, \Context::getContext()->cookie->id_lang),
                                'id' => 'id_supplier',
                                'name' => 'name'
                            ]
                        ],
                        [
                            'type' => 'switch',
                            'name' => 'export_include_empties',
                            'required' => true,
                            'is_bool' => true,
                            'label' => $this->l('Include products without value'),
                            'values' => [
                                ['id' => 'export_include_empties_on', 'value' => 1, 'label' => $this->l('Yes')],
                                ['id' => 'export_include_empties_off', 'value' => 0, 'label' => $this->l('No')],
                            ]
                        ],
                        [
                            'type' => 'switch',
                            'name' => 'export_add_header',
                            'required' => true,
                            'is_bool' => true,
                            'label' => $this->l('Add headers'),
                            'values' => [
                                ['id' => 'active_on', 'value' => 1, 'label' => $this->l('Yes')],
                                ['id' => 'active_off', 'value' => 0, 'label' => $this->l('No')],
                            ]
                        ]
                    ],
                    'submit' => [
                        'icon' => 'process-icon-download-alt',
                        'title' => $this->l('Export'),
                        'name' => 'submitExport'
                    ]
                ]
            ]
        ]);
    }

    private function processImport()
    {
        if (!Tools::isSubmit('submitImport')) {
            return;
        }

        if (!($id_feature = (int)\Tools::getValue('import_id_feature'))) {
            $this->context->controller->errors[] = $this->l('Please select a feature');
            return;
        }

        if (!($separator = \Tools::getValue('import_separator'))) {
            $this->context->controller->errors[] = $this->l('Please select a separator');
            return;
        }

        if (mime_content_type($_FILES['import_file']['tmp_name']) !== 'text/plain') {
            $this->context->controller->errors[] = $this->l('The file must be a CSV file');
            return;
        }

        $handle = false;
        if (is_file($_FILES['import_file']['tmp_name']) && is_readable($_FILES['import_file']['tmp_name'])) {
            $handle = fopen($_FILES['import_file']['tmp_name'], 'r');
        }

        if (!$handle) {
            $this->context->controller->errors[] = $this->l('Impossible de lire le fichier CSV');
            return;
        }

        $headers_have_been_checked = $separator_have_been_checked = false;
        $current_line = 1;
        while (($data = fgetcsv($handle, 0, $separator)) !== FALSE) {
            if (!$separator_have_been_checked) {
                if (count($data) <= 1) {
                    $this->context->controller->errors[] = $this->l('It seems that the separator is not correct');
                    return;
                }
            }

            if (!Tools::getValue('import_have_header')) {
                if (!$headers_have_been_checked && !(int)$data[0]) {
                    $this->context->controller->errors[] = $this->l('It seems that the file contains headers');
                    return;
                }
                $headers_have_been_checked = true;
            }

            $id_product = (int)$data[0];
            if (!$id_product) {
                $this->context->controller->errors[] = sprintf(
                    $this->l('Line #%d, the product ID is missing'),
                    $current_line
                );
                continue;
            }

            $feature_value = trim($data[1]);
            if ($feature_value != '') {
                if (!$this->addCustomFeatureToProduct($id_product, $id_feature, $feature_value)) {
                    $this->context->controller->errors[] = sprintf(
                        $this->l('Line #%d, impossible to add the feature to the product'),
                        $current_line
                    );
                }
            }

            $current_line++;
        }
        fclose($handle);
    }

    private function deleteFeatureFromProduct($id_product, $id_feature, $value) {

    }

    private function addCustomFeatureToProduct($id_product, $id_feature, $value) {
        $rs = Db::getInstance()->insert('feature_value', ['id_feature' => (int)$id_feature, 'custom' => 1]);
        if (!$rs) {
            return false;
        }

        $id_feature_value = Db::getInstance()->Insert_ID();
        foreach (Language::getLanguages() as $language) {
            $rs = Db::getInstance()->insert('feature_value_lang', [
                'id_feature_value' => (int)$id_feature_value,
                'id_lang' => (int)$language['id_lang'],
                'value' => pSQL($value)
            ]);
            if (!$rs) {
                return false;
            }
        }

        return Db::getInstance()->insert('feature_product', [
            'id_feature' => (int)$id_feature,
            'id_product' => (int)$id_product,
            'id_feature_value' => (int)$id_feature_value
        ], false, true, Db::REPLACE);
    }

    private function processExport()
    {

        if (!Tools::isSubmit('submitExport')) {
            return;
        }

        if (!($id_feature = (int)\Tools::getValue('export_id_feature'))) {
            $this->context->controller->errors[] = $this->l('Please select a feature');
            return;
        }

        if (!($separator = \Tools::getValue('export_separator'))) {
            $this->context->controller->errors[] = $this->l('Please select a separator');
            return;
        }

        $id_manufacturer = (int)\Tools::getValue('export_id_manufacturer');
        $id_supplier = (int)\Tools::getValue('export_id_supplier');
        $export_add_header = (int)\Tools::getValue('export_add_header');
        $export_include_empties = (int)\Tools::getValue('export_include_empties');
        $query = new DbQuery();
        $query->select('p.id_product, fvl.value');

        if ($export_include_empties) {
            $query->from('product', 'p');
            $query->leftJoin('feature_product', 'fp', 'fp.id_product = p.id_product AND fp.id_feature = ' . (int)$id_feature);
            $query->leftJoin('feature_value_lang', 'fvl', 'fvl.id_feature_value = fp.id_feature_value AND fvl.id_lang = ' . (int)$this->context->language->id);
        } else {
            $query->from('feature_product', 'fp');
            $query->innerJoin('product', 'p', 'p.id_product = fp.id_product');
            $query->leftJoin('feature_value_lang', 'fvl', 'fvl.id_feature_value = fp.id_feature_value AND fvl.id_lang = ' . (int)$this->context->language->id);
            $query->where('fp.id_feature = ' . (int)$id_feature);
        }
        if ($id_manufacturer) {
            $query->where('p.id_manufacturer = ' . (int)$id_manufacturer);
        }
        if ($id_supplier) {
            $query->where('p.id_supplier = ' . (int)$id_supplier);
        }

        $products = \Db::getInstance()->executeS($query);
        if (!$products) {
            $this->context->controller->errors[] = $this->l('No products found');
            return;
        }

        $file = 'export-'.date('Ymd').'-'.date('His').'.csv';

        header('Content-Type: text/csv');
        header('Cache-Control: no-store, no-cache');
        header('Content-Disposition: attachment; filename="'.$file.'"');

        $handle = fopen('php://output', 'w+');
        fputs($handle,  chr(0xEF) . chr(0xBB) . chr(0xBF));
        if ($export_add_header) {
            fputcsv($handle, ['id_product', 'feature_value'], $separator);
        }
        foreach($products as $product) {
            fputcsv($handle, [(int)$product['id_product'], $product['value']], $separator);
        }
        exit;
    }
}
