<?php
/**
 * Image UIType field file.
 *
 * @package UIType
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author	Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace YF\Modules\Base\FieldTypes;

/**
 * Image UIType field class.
 */
class MultiImageField extends BaseField
{
	/** {@inheritdoc} */
	public function isEditable()
	{
		return false;
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, \YF\Modules\Base\Model\Record $recordModel = null): string
	{
		if (empty($value)) {
			return '';
		}
		$result = '';
		if (\array_key_exists('postData', $value)) {
			$value = [$value];
		}
		$result = '<div class="c-multi-image__result" style="width:100%">';
		foreach ($value as $image) {
			$mime = $image['type'];
			$content = \App\Api::getInstance()->setCustomHeaders(['Accept' => $mime])->call('Files', $image['postData'], 'put');
			$base = base64_encode($content);
			$result .= "<div style=\"width:80px\" class=\"ml-1 d-inline-block mr-1\"><img src=\"data:{$mime};base64,{$base}\"/></div>";
		}
		return $result . '</div>';
	}
}
