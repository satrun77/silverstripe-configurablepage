<?php

/**
 * @author  Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class EditableFieldPageTypeListExtension extends DataExtension
{
    /**
     * Get field value that is suitable for the view template file.
     *
     * @return Page
     */
    public function getViewValue()
    {
        return Page::get()->byID((int)$this->owner->Value);
    }

    /**
     * Get string value of a field.
     *
     * @return null|string
     */
    public function getValueAsString()
    {
        $page = $this->getViewValue();

        return $page ? $page->Title : '';
    }
}
