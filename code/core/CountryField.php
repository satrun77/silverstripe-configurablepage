<?php

/**
 * CountryField is an object that hold a country locale details. It is callable from template files
 *
 * @author Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 * @package configurablepage
 */
class CountryField extends ViewableData {
	/**
	 * Country code
	 * @var string
	 */
	protected $countryCode;

	/**
	 * Set a country code
	 *
	 * @param string $value
	 * @return CountryField
	 */
	public function setValue($value) {
		$this->countryCode = $value;
		return $this;
	}

	/**
	 * Get country code
	 *
	 * @return string
	 */
	public function Code() {
		return $this->countryCode;
	}

	/**
	 * Get the country full name based on the code supplied to the object
	 *
	 * @return string
	 */
	public function Name() {
		return (string) Zend_Locale::getTranslation($this->Code(), "country", i18n::get_locale());
	}

}
