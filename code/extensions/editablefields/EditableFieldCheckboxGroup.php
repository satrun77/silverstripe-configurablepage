<?php

/**
 * @author  Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class EditableFieldCheckboxGroupExtension extends DataExtension
{
    /**
     * Get field value that is suitable for the view template file.
     *
     * @return ArrayList
     */
    public function getViewValue()
    {
        $values = explode(',', $this->owner->Value);
        $value = new ArrayList();

        array_walk($values, function ($item) use ($value) {
            $value->push(['name' => $item]);
        });

        return $value;
    }

    /**
     * Get string value of a field.
     *
     * @return null|string
     */
    public function getValueAsString()
    {
        $return = '';

        $this->getViewValue()->each(function (ArrayData $item) use (&$return) {
            if ($return) {
                $return .= ', ';
            }
            $return .= $item->getField('name');
        });

        return $return;
    }
}
