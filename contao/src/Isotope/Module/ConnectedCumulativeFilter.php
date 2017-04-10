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

namespace Isotope\Module;

use Haste\Generator\RowClass;
use Haste\Input\Input;
use Haste\Util\Url;
use Isotope\Interfaces\IsotopeAttributeWithOptions;
use Isotope\Interfaces\IsotopeFilterModule;
use Isotope\Isotope;
use Isotope\Model\Attribute;
use Isotope\Model\Product;
use Isotope\RequestCache\CsvFilter;
use Isotope\RequestCache\Filter;
use Isotope\RequestCache\FilterQueryBuilder;
use Isotope\Template;
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
        parent::__construct($objModule, $strColumn);
        
        $this->iso_connectedFilterModules = deserialize($this->iso_connectedFilterModules);

        if(!in_array($this->id,$this->iso_connectedFilterModules))
        {
			/** $this->iso_connectedFilterModules is not writeabel here **/
			$arrTemp = $this->iso_connectedFilterModules;
       		$arrTemp[] = $this->id;
			$this->iso_connectedFilterModules = $arrTemp;
		}
			
		$this->activeFilters = Isotope::getRequestCache()->getFiltersForModules($this->iso_connectedFilterModules);
       
    }
	
    /**
     * Compile the module
     */
    protected function compile()
    {
        $arrFilter = explode(';', base64_decode(\Input::get('cumulativefilter', true)), 4);

        if ($arrFilter[0] == $this->id && isset($this->iso_cumulativeFields[$arrFilter[2]])) {
            $this->saveFilter($arrFilter[1], $arrFilter[2], $arrFilter[3]);
            return;
        }

        $this->generateFilter();

        $this->Template->linkClearAll  = ampersand(preg_replace('/\?.*/', '', \Environment::get('request')));
        $this->Template->labelClearAll = $GLOBALS['TL_LANG']['MSC']['clearFiltersLabel'];
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

        $strLocation = \Environment::get('base') .
            Url::addQueryString(
                'isorc='.$objCache->id,
                Url::removeQueryString(array('cumulativefilter'), ($this->jumpTo ?: null))
            );
            
        // pjax
        if(isset($_SERVER['HTTP_X_PJAX'])) {
            header('HTTP/1.1 302 Found');
            header('Location: ' . $strLocation);
            header('X-PJAX-URL: ' . $strLocation);
            exit();
        }

        // Include \Environment::base or the URL would not work on the index page
        \Controller::redirect($strLocation);
    }
    
       /**
     * @param Filter[] $filters
     * @param string   $attribute
     * @param string   $value
     *
     * @return Filter[]
     */
    private function addFilter(array $filters, $attribute, $value)
    {
        if ($this->isCsv($attribute)) {
            $filter = CsvFilter::attribute($attribute)->contains($value);
        } else {
            $filter = Filter::attribute($attribute)->isEqualTo($value);
        }

        if (!$this->isMultiple($attribute) || self::QUERY_OR === $this->iso_cumulativeFields[$attribute]['queryType']) {
            $group = 'cumulative_' . $attribute;
            $filter->groupBy($group);

            if (self::QUERY_AND === $this->iso_cumulativeFields[$attribute]['queryType']) {
                foreach ($filters as $k => $oldFilter) {
                    if ($oldFilter->getGroup() == $group) {
                        unset($filters[$k]);
                    }
                }
            }
        }

        $filters[$this->generateFilterKey($attribute, $value)] = $filter;

        return $filters;
    }
    
    
	/**
     * Generates a filter key for the field and value.
     *
     * @param string $field
     * @param string $value
     *
     * @return string
     */
    public function generateFilterKey($field, $value)
    {
        return $field . '=' . $value;
    }
	
}
