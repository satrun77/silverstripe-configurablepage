<?php

/**
 * ConfigurablePageTest contains test cases for the module classes
 *
 * @author  Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 * @package configurablepage
 */
class ConfigurablePageTest extends FunctionalTest
{
    protected static $fixture_file = 'ConfigurablePageTest.yml';

    public function testGetCMSFields()
    {
        $this->logInWithPermission('EDITOR');

        $page = $this->objFromFixture('ConfigurablePage', 'page-1');
        $fields = $page->getCMSFields();

        $this->assertTrue($fields->dataFieldByName('textfield1') !== null);
        $this->assertTrue($page->getCMSValidator()->fieldIsRequired('textfield1'));
    }

    public function testWriteToField()
    {
        $this->logInWithPermission('EDITOR');

        $value = 'Value for text field 1';
        $page = $this->objFromFixture('ConfigurablePage', 'page-1');
        $page->textfield1 = $value;
        $page->Content = 'Page content $textfield1';
        $page->write();

        $page2 = $this->objFromFixture('ConfigurablePage', 'page-1');
        $this->assertContains($value, $page2->Content());
    }

    public function testFieldsRendering()
    {
        $this->logInWithPermission('EDITOR');

        $page3 = Page::get_by_link('page3');
        $memberId = Member::currentUserID();
        $values = [
            'checkboxgroup'  => '2,0',
            'countryfield'   => 'NZ',
            'dobfield'       => '2014-1-1',
            'memberfield1'   => (string)$memberId,
            'pagetypefield2' => (string)$page3->ID
        ];

        $page = $this->objFromFixture('ConfigurablePage', 'page-2');
        $page->Content = 'Page content ----->';
        foreach ($values as $name => $value) {
            $page->setField($name, $value);
            $page->$name = $value;
            $page->Content .= ' $' . $name;
        }
        $page->write();

        $content = $page->Content();

        $editableFields = $page->getEditableFields();
        foreach ($editableFields as $editableField) {
            $field = $editableField->getFormField();
            if (!$field instanceof DatalessField) {
                $field->value = $page->{$editableField->Name};
                $this->assertContains($editableField->getValueAsString(), $content);
            }
        }
    }
}
