<?php

/**
 * @author  Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class EditableFieldCountryDropdownExtension extends DataExtension
{
    /**
     * Get field value that is suitable for the view template file.
     *
     * @return CountryField
     */
    public function getViewValue()
    {
        $value = Object::create('CountryField');

        return $value->setValue($this->owner->Value);
    }

    /**
     * Get string value of a field.
     *
     * @return null|string
     */
    public function getValueAsString()
    {
        $country = $this->getViewValue();

        return $country ? $country->Name() : '';
    }
}
