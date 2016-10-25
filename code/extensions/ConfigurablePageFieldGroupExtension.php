<?php

/**
 * ConfigurablePageFieldGroupExtension is an extension class for EditableFieldGroup class.
 *
 * @author  Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class ConfigurablePageFieldGroupExtension extends DataExtension
{
    private static $has_many = [
        'ConfigurablePages' => 'ConfigurablePage',
    ];

    public function updateCMSFields(FieldList $fields)
    {
        // Remove Configurable Page Tab
        if (!$this->owner->ConfigurablePages) {
            $fields->removeByName('ConfigurablePages');
        }
    }
}
