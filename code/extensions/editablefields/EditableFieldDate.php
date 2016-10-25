<?php

/**
 * @author  Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class EditableFieldDateExtension extends DataExtension
{
    /**
     * Get field value that is suitable for the view template file.
     *
     * @return Date
     */
    public function getViewValue()
    {
        $value = Object::create('Date');
        $value->setValue($this->owner->Value);

        return $value;
    }

    /**
     * Get string value of a field.
     *
     * @return null|string
     */
    public function getValueAsString()
    {
        $date = $this->getViewValue();

        return $date ? $date->Nice() : '';
    }
}
