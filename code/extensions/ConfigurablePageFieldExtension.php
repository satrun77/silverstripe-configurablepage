<?php

/**
 * ConfigurablePageFieldExtension is an extension class that adds extra methods to the EditableField classes.
 *
 * @author  Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
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

        // Header field only used in page type
        if ($class instanceof Moo_EditableFieldHeading) {
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
     * Editable field can only be changed if is not part of a group.
     *
     * @param null $member
     *
     * @return bool
     */
    public function canEdit($member = null)
    {
        return (int) $this->owner->Group === 0;
    }
}
