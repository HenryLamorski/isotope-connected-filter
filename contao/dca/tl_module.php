<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @author Henry Lamorski <henry.lamorski@mailbox.org>
 * 
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Add palettes to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_connectedfilter'] = str_replace(
	'iso_cumulativeFields,',
	'iso_cumulativeFields, iso_connectedFilterModules,',
	$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_cumulativefilter']
);


/**
 * Add fields to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['iso_connectedFilterModules'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['iso_filterModules'],
    'exclude'                   => true,
    'inputType'                 => 'checkboxWizard',
    'foreignKey'                => 'tl_module.name',
    'options_callback'          => array('Isotope\Backend\Module\Callback', 'getFilterModules'),
    'eval'                      => array('multiple'=>true, 'tl_class'=>'clr'),
    'sql'                       => 'blob NULL',
    'relation'                  => array('type'=>'hasMany', 'load'=>'lazy'),
);
