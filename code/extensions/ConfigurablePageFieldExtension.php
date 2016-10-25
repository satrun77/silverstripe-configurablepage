<?php

/**
 * ConfigurablePageFieldExtension is an extension class that adds extra methods to the EditableField classes.
 *
 * @author  Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 *
 * @package configurablepage
 */
class ConfigurablePageFieldExtension extends DataExtension
{
    private static $belongs_many_many = [
        'Parents' => 'ConfigurablePage',
    ];

    /**
     * Get field value that is suitable for the view template file.
     *
     * @return object|false|string
     */
    public function getViewValue()
    {
        $class = $this->owner;

        // Get country object
        if ($class instanceof EditableFieldCountryDropdown) {
            return $this->countryFieldValue();
        }

        // Get date object
        if ($class instanceof EditableFieldDate) {
            return $this->dateFieldValue();
        }

        // Get memeber object
        if ($class instanceof EditableFieldMemberList) {
            return $this->memberListField();
        }

        // Get ArrayList object with one field "name"
        if ($class instanceof EditableFieldCheckboxGroup) {
            return $this->nameListField();
        }

        // Get page type object
        if ($class instanceof EditableFieldPageTypeList) {
            return $this->pageTypeField();
        }

        // Header field only used in page type
        if ($class instanceof EditableFieldHeading) {
            return false;
        }

        // Default return string
        return (string) $this->owner->Value;
    }

    /**
     * Get string value of a field.
     *
     * @return null|string
     */
    public function getValueAsString()
    {
        $class = $this->owner;

        // Get country object
        if ($class instanceof EditableFieldCountryDropdown) {
            $country = $this->countryFieldValue();

            return $country ? $country->Name() : '';
        }

        // Get date object
        if ($class instanceof EditableFieldDate) {
            $date = $this->dateFieldValue();

            return $date ? $date->Nice() : '';
        }

        // Get memeber object
        if ($class instanceof EditableFieldMemberList) {
            $member = $this->memberListField();

            return $member ? $member->getName() : '';
        }

        // Get ArrayList object with one field "name"
        if ($class instanceof EditableFieldCheckboxGroup) {
            $return = '';
            $this->nameListField()->each(function (ArrayData $item) use (&$return) {
                if ($return) {
                    $return .= ', ';
                }
                $return .= $item->getField('name');
            });

            return $return;
        }

        // Get page type object
        if ($class instanceof EditableFieldPageTypeList) {
            $page = $this->pageTypeField();

            return $page ? $page->Title : '';
        }

        // Default return string
        return (string) $this->owner->Value;
    }

    /**
     * Format the field Title to be suitable as a variable in template.
     *
     * @return string
     */
    public function getViewFieldName()
    {
        return $this->owner->Name;
    }

    /**
     * Format field value into CountryField object.
     *
     * @return CountryField
     */
    protected function countryFieldValue()
    {
        $value = Object::create('CountryField');

        return $value->setValue($this->owner->Value);
    }

    /**
     * Format field value into Date object.
     *
     * @return Date
     */
    protected function dateFieldValue()
    {
        $value = Object::create('Date');
        $value->setValue($this->owner->Value);

        return $value;
    }

    /**
     * Format field value into Member object.
     *
     * @return Member
     */
    protected function memberListField()
    {
        return $this->objectField('Member');
    }

    /**
     * Format field value into ArrayList object with one field "name".
     *
     * @return ArrayList
     */
    protected function nameListField()
    {
        $values = explode(',', $this->owner->Value);
        $value  = new ArrayList();
        array_walk($values, function ($item) use ($value) {
            $value->push(['name' => $item]);
        });

        return $value;
    }

    protected function pageTypeField()
    {
        return $this->objectField('Page');
    }

    protected function objectField($name)
    {
        $value = $this->owner->Value;

        if (is_numeric($value) && $value > 0) {
            $value = $name::get()->byID($value);
        } else {
            if (is_null($value)) {
                $value = '';
            }
        }

        return $value;
    }

    /**
     * Editable field can only be changed if is not part of a group.
     *
     * @param null $member
     *
     * @return bool
     */
    public function canEdit($member = null)
    {
        return $this->owner->Group == 0;
    }
}
