<?php

/**
 * ConfigurablePage is the page class for the module
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 * @package configurablepage
 */
class ConfigurablePage extends Page {
	private static $many_many = array(
		'Fields' => 'EditableField'
	);
	private static $many_many_extraFields = array(
		'Fields' => array(
			'Value' => 'Text',
			"Sort" => "Int",
		)
	);
	private static $singular_name = 'Configurable Page';
	private static $plural_name = 'Configurable Pages';
	private static $description = 'Create page with configurable fields';
	private static $icon = 'configurablepage/images/icon.png';

	/**
	 * An array of required field names
	 *
	 * @var array
	 */
	protected $requiredFields = array();

	/**
	 * An array containing the current values from the configurable fields
	 *
	 * @var array
	 */
	protected $editableFields;

	/**
	 * (non-PHPdoc)
	 * @see SiteTree::getCMSFields()
	 */
	public function getCMSFields() {
		// Get the fields from the parent implementation
		$fields = parent::getCMSFields();

		// List of available fields in the page
		$list = $this->Fields()->sort('Sort', 'ASC');

		// Add tab to edit fields values
		$this->buildPageFieldsTab($list, $fields);

		// Add Tab to add fields into the page type
		$config = GridFieldConfig_RelationEditor::create();
		$config->getComponentByType('GridFieldPaginator')->setItemsPerPage(10);
		$config->removeComponentsByType('GridFieldAddNewButton');
		$config->removeComponentsByType('GridFieldEditButton');
		$config->getComponentByType('GridFieldDataColumns')->setDisplayFields(array(
			'Name' => _t('ConfigurablePage.NAME', 'Name'),
			'Title' => _t('ConfigurablePage.TITLE', 'Title'),
			'Sort' => _t('ConfigurablePage.SORT', 'Sort'),
		));
		$config->addComponent(new GridFieldEditableManyManyExtraColumns(array('Sort' => 'Int')), 'GridFieldEditButton');
		$field = new GridField('Fields', 'Field', $list, $config);
		// 		$field->getConfig()->getComponentByType('GridFieldDataColumns')->setFieldFormatting(array(
		// 			'Name' => function($val, $obj) {
		// 				if($obj instanceof EditableFormField){
		// 					return '<img src="' . $obj->getIcon() . '"/>';
		// 				}
		// 				return $obj;
		// 			}
		// 		));
		$fields->addFieldToTab('Root.ManagePageFields', $field);

		// JS & CSS for the gridfield sort column
		Requirements::javascript('configurablepage/javascript/ConfigurablePage.js');
		Requirements::css('configurablepage/css/ConfigurablePage.css');

		return $fields;
	}

	/**
	 * Create tab to edit fields values
	 *
	 * @param ManyManyList $list
	 * @param FieldList $fields
	 */
	public function buildPageFieldsTab(ManyManyList $list, FieldList $fields) {
		$fields->findOrMakeTab('Root.Fields', _t('ConfigurablePage.FIELDS', 'Fields'));

		foreach($list as $editableField) {
			// Get the raw form field from the editable version
			$field = $editableField->getFormField();
			if(!$field) {
				continue;
			}

			// Set the error / formatting messages
			$field->setCustomValidationMessage($editableField->getErrorMessage());

			// Set the right title on this field
			$right = $editableField->getSetting('RightTitle');
			if($right) {
				$field->setRightTitle($right);
				$field->addExtraClass('help');
			}

			// Set the required field
			if($editableField->Required) {
				$this->requiredFields[] = $editableField->Name;
			}

			// Set field extra class
			if($editableField->getSetting('ExtraClass')) {
				$field->addExtraClass(Convert::raw2att(
						$editableField->getSetting('ExtraClass')
				));
			}

			// Set the value
			if(!$field instanceof DatalessField) {
				$field->value = Convert::raw2att($editableField->Value);
				$this->setField($editableField->Name, $editableField->Value);
			}

			// Add field to tab
			$fields->addFieldToTab('Root.Fields', $field);
		}
	}

	/**
	 * Set required fields
	 *
	 * @return RequiredFields
	 */
	public function getCMSValidator() {
		return new RequiredFields($this->requiredFields);
	}

	/**
	 * (non-PHPdoc)
	 * @see SiteTree::onBeforeWrite()
	 */
	public function onAfterWrite() {
		parent::onAfterWrite();

		// Skip on publishing
		if(Versioned::get_live_stage() == Versioned::current_stage()) {
			return;
		}

		// Update the values of all fields added from editable field
		if($this->ID && $this->many_many('Fields') && $pageFields = $this->getEditableFields()) {
			foreach($pageFields as $pageField) {
				// Set submitted value into the field
				$field = $pageField->getFormField();
				if(!$field) {
					continue;
				}
				$field->setValue($this->{$pageField->Name});

				// Extra fields to be saved
				$value = $field->Value();
				$sort = $pageField->Sort;

				// Clone the editable field object
				// Remove the current saved one
				$pageFields->remove($pageField);
				// Add the clone with the new extra data
				$pageFields->add($pageField, array('Value' => $value, 'Sort' => $sort));
			}
		}
	}

	/**
	 * Format the page Content
	 *
	 * @return AHTMLText
	 */
	public function Content() {
		// Get custom fields
		$fields = $this->getEditableFields();
		$values = array();

		// Add custom fields to the current object
		// & create dictionary of the custom fields values
		foreach($fields as $field) {
			$value = $field->getViewValue();
			$name = $field->getViewFieldName();

			// Fields with false value are not viewable data
			if($value !== false) {
				$this->setField($name, $value);
				$values['$' . $name] = $field->getValueAsString();
			}
		}

		// Execute content from extensions
		// Set content from view template module
		$this->extend('Content');

		// & Replace ${Field Name} with a string value
		return strtr($this->Content, $values);
	}

	/**
	 * Get an array of all of the editable fields for the view template
	 *
	 * @return array|string|object
	 */
	public function getEditableFields() {
		if(null == $this->editableFields) {
			$this->editableFields = $this->Fields();
		}
		return $this->editableFields;
	}

}
