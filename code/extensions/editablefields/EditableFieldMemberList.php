<?php

/**
 * @author  Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 */
class EditableFieldMemberListExtension extends DataExtension
{
    /**
     * Get field value that is suitable for the view template file.
     *
     * @return Member
     */
    public function getViewValue()
    {
        return Member::get()->byID((int) $this->owner->Value);
    }

    /**
     * Get string value of a field.
     *
     * @return null|string
     */
    public function getValueAsString()
    {
        $member = $this->getViewValue();

        return $member ? $member->getName() : '';
    }
}
