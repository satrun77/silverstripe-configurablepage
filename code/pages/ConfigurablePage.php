<?php

/**
 * ConfigurablePage is the page class for the module.
 *
 * @author  Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class ConfigurablePage extends Page
{
    private static $many_many = [
        'Fields' => 'Moo_EditableField',
    ];
    private static $many_many_extraFields = [
        'Fields' => [
            'Value' => 'Text',
            'Sort'  => 'Int',
            'Group' => 'Int',
        ],
    ];
    private static $has_one = [
        'EditableFieldGroup' => 'Moo_EditableFieldGroup',
    ];
    private static $singular_name = 'Configurable Page';
    private static $plural_name   = 'Configurable Pages';
    private static $description   = 'Create page with configurable fields';
    private static $icon          = 'configurablepage/images/icon.png';

    /**
     * An array of required field names.
     *
     * @var array
     */
    protected $requiredFields = [];

    /**
     * An instance of ManyManyList containing the current values from the configurable fields.
     *
     * @var ManyManyList
     */
    protected $editableFields;

    /**
     * List of allowed child page types.
     *
     * @var array
     */
    private static $allowed_children = ['ConfigurablePage', 'SiteTree'];

    /**
     * (non-PHPdoc).
     *
     * @see SiteTree::getCMSFields()
     */
    public function getCMSFields()
    {
        // Get the fields from the parent implementation
        $fields = parent::getCMSFields();

        // List of available fields in the page
        $groupFields = $this->EditableFieldGroup()->Fields();
        $list        = $this->Fields()->addMany($groupFields)->sort('Sort', 'ASC');

        // Add tab to edit fields values
        $this->buildPageFieldsTab($list, $fields);

        // Add tab to manage page fields
        $this->buildManageFieldsTab($list, $fields);

        return $fields;
    }

    /**
     * Create tab to manage page fields.
     *
     * @param FieldList $fields
     */
    protected function buildManageFieldsTab(ManyManyList $list, FieldList $fields)
    {
        // GridField for managing page specific fields
        $config = GridFieldConfig_RelationEditor::create();
        $config->getComponentByType('GridFieldPaginator')->setItemsPerPage(10);
        $config->removeComponentsByType('GridFieldAddNewButton');
        $config->removeComponentsByType('GridFieldEditButton');
        $config->getComponentByType('GridFieldDataColumns')->setDisplayFields([
            'Name'  => _t('ConfigurablePage.NAME', 'Name'),
            'Title' => _t('ConfigurablePage.TITLE', 'Title'),
            'Group' => _t('ConfigurablePage.GROUP', 'Group'),
        ]);
        $config->addComponent(new GridFieldOrderableRows('Sort'));
        $config->getComponentByType('GridFieldDataColumns')
            ->setFieldFormatting([
                'Group' => function ($value) {
                    return !$value ? '' : $this->EditableFieldGroup()->Title;
                },
            ]);
        $fieldsField = new GridField('Fields', 'Fields', $list, $config);

        // Drop-down list of editable field groups
        $groups = Moo_EditableFieldGroup::get()->map();
        $groups->unshift('', '');

        $groupsField = new DropdownField(
            'EditableFieldGroupID',
            _t('ConfigurablePage.FIELDGROUP', 'Editable field group'),
            $groups
        );
        $groupsField->setDescription(_t(
            'ConfigurablePage.FIELDGROUP_HELP',
            'Select a group to load its collection of fields in the current page. '
            . 'You need to click save to update the page fields.'
        ));

        // Add fields to manage page fields tab
        $fields->addFieldsToTab('Root.ManagePageFields', [
            $groupsField, $fieldsField,
        ]);
    }

    /**
     * Create tab to edit fields values.
     *
     * @param ManyManyList $list
     * @param FieldList    $fields
     */
    protected function buildPageFieldsTab(ManyManyList $list, FieldList $fields)
    {
        $fields->findOrMakeTab('Root.Fields', _t('ConfigurablePage.FIELDS', 'Fields'));

        $list->each(function (Moo_EditableField $editableField) use ($fields) {
            $field = $this->getFieldFromEditableField($editableField);
            if (false !== $field) {
                $fields->addFieldToTab('Root.Fields', $field);
            }
        });
    }

    /**
     * Add an editable field to the fields tab.
     *
     * @param Moo_EditableField $editableField
     *
     * @return bool|FormField
     */
    protected function getFieldFromEditableField(Moo_EditableField $editableField)
    {
        // Get the raw form field from the editable version
        $field = $editableField->getFormField();
        if (!$field) {
            return false;
        }

        // Set the error / formatting messages
        $field->setCustomValidationMessage($editableField->getErrorMessage());

        // Set the right title on this field
        $right = $editableField->getSetting('RightTitle');
        if ($right) {
            $field->setRightTitle($right);
            $field->addExtraClass('help');
        }

        // Set the required field
        if ($editableField->Required) {
            $this->requiredFields[] = $editableField->Name;
        }

        // Set the value
        if (!$field instanceof DatalessField) {
            $field->value = Convert::raw2att($editableField->Value);
            $this->setField($editableField->Name, $editableField->Value);
        }

        return $field;
    }

    /**
     * Set required fields.
     *
     * @return RequiredFields
     */
    public function getCMSValidator()
    {
        return new RequiredFields($this->requiredFields);
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();

        // Skip on publishing
        if (Versioned::get_live_stage() == Versioned::current_stage()) {
            return;
        }

        // Update the values of all fields added from editable field
        if ($this->ID && $this->manyMany('Fields') && $pageFields = $this->getEditableFields()) {
            $pageFields->each(function (Moo_EditableField $pageField) use ($pageFields) {
                // Set submitted value into the field
                $field = $pageField->getFormField();
                if (!$field) {
                    return;
                }
                $field->setValue($this->{$pageField->Name});

                // Extra fields to be saved
                $value = $field->Value();
                $sort = $pageField->Sort;
                $group = $pageField->Group;

                // Clone the editable field object
                // Remove the current saved one
                $pageFields->remove($pageField);
                // Add the clone with the new extra data
                $pageFields->add($pageField, ['Value' => $value, 'Sort' => $sort, 'Group' => $group]);
            });
        }
    }

    /**
     * Format the page Content.
     *
     * @return string
     */
    public function Content()
    {
        // Get custom fields
        $fields = $this->getEditableFields();
        $values = [];

        // Add custom fields to the current object
        // & create dictionary of the custom fields values
        foreach ($fields as $field) {
            $value = $field->getViewValue();
            $name  = $field->getViewFieldName();

            // Fields with false value are not viewable data
            if ($value !== false) {
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
     * Get an array of all of the editable fields for the view template.
     *
     * @return ManyManyList
     */
    public function getEditableFields()
    {
        if (null === $this->editableFields) {
            // Fields from editable field groups
            $groupFields = $this->EditableFieldGroup()->Fields();
            $ids         = $groupFields->getIDList();

            // Set page specific fields
            $this->editableFields = $this->Fields();

            // Remove all fields that are used to belong to editable field group
            // Then sync editable field group with page specific fields
            // Else remove all of a group editable fields if exists
            if (!empty($ids)) {
                $this->editableFields->removeByFilter(
                    '"Group" > 0 AND EditableFieldID NOT IN (' . implode(',', $ids) . ')'
                )->addMany($groupFields);
            } else {
                $this->editableFields->removeByFilter('"Group" > 0');
            }
        }

        return $this->editableFields;
    }
}
