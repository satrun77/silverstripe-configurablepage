<?php

/**
 * ConfigurablePageFieldGroupExtension is an extension class for EditableFieldGroup class
 *
 * @author  Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 * @package configurablepage
 */
class ConfigurablePageFieldGroupExtension extends DataExtension
{
	private static $has_many = [
		'ConfigurablePages' => 'ConfigurablePage'
	];
}
