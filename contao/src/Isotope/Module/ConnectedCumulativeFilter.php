<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Module;

/**
 * @property array $iso_cumulativeFields
 */
class ConnectedCumulativeFilter extends CumulativeFilter
{
 
	/**
     * Constructor.
     *
     * @param object $objModule
     * @param string $strColumn
     */
    public function __construct($objModule, $strColumn = 'main')
    {

		$this->iso_connectedFilterModules[] = $this->id; 

		/*
        if(!in_array(80,$this->iso_connectedFilterModules))
			$this->iso_connectedFilterModules[] = 80;
		if(!in_array(81,$this->iso_connectedFilterModules))
			$this->iso_connectedFilterModules[] = 81;
        */
		file_put_contents("/var/www/contao.log","\n\nfilter modules: ".print_r($this->iso_connectedFilterModules,true),FILE_APPEND);
		
		parent::__construct($objModule, $strColumn);
	}
	
	
	 /**
     * @param string $action
     * @param string $attribute
     * @param string $value
     */
    private function saveFilter($action, $attribute, $value)
    {	
        if ($action == 'add') {
			foreach($this->iso_connectedFilterModules as $intModuleId)
			{
				Isotope::getRequestCache()->setFiltersForModule(
					$this->addFilter($this->activeFilters, $attribute, $value),
					$intModuleId
				);
				file_put_contents("/var/www/contao.log",print_r($this->addFilter($this->activeFilters, $attribute, $value),true),FILE_APPEND);
			}
        } else {
			foreach($this->iso_connectedFilterModules as $intModuleId)
			{
				Isotope::getRequestCache()->removeFilterForModule(
					$this->generateFilterKey($attribute, $value),
					$intModuleId
				);
			}
        }

        $objCache = Isotope::getRequestCache()->saveNewConfiguration();

        // Include \Environment::base or the URL would not work on the index page
        \Controller::redirect(
            \Environment::get('base') .
            Url::addQueryString(
                'isorc='.$objCache->id,
                Url::removeQueryString(array('cumulativefilter'), ($this->jumpTo ?: null))
            )
        );
    }
	
}
