<?php
/**
 * The file contains: Abstract class ListView.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace YF\Modules\Base\Model;

/**
 * Abstract class ListView.
 */
abstract class AbstractListView
{
	/** @var string Module name. */
	protected $moduleName;

	/** @var string[] Column fields */
	protected $fields = [];

	/** @var array Records list from api. */
	protected $recordsList = [];

	/** @var int Current page. */
	private $page = 1;

	/** @var int The number of items on the page. */
	protected $limit = 0;

	/** @var int Offset. */
	protected $offset = 0;

	/** @var string Sorting direction. */
	protected $order;

	/** @var string Sets the ORDER BY part of the query record list. */
	protected $orderField;

	/** @var array Conditions. */
	protected $conditions = [];

	/** @var bool Use raw data. */
	protected $rawData = false;

	/** @var string Action name */
	protected $actionName = 'RecordsList';

	/** @var array Custom views */
	protected $customViews;

	/** @var int Custom view ID */
	protected $cvId;

	/**
	 * Get instance.
	 *
	 * @param string $moduleName
	 * @param string $viewName
	 *
	 * @return $this
	 */
	public static function getInstance(string $moduleName, string $viewName = 'ListView')
	{
		$handlerModule = \App\Loader::getModuleClassName($moduleName, 'Model', $viewName);
		$self = new $handlerModule();
		$self->moduleName = $moduleName;
		$self->limit = \App\Config::$itemsPrePage ?: 15;
		return $self;
	}

	/**
	 * Function to get the Module Model.
	 *
	 * @return string
	 */
	public function getModuleName(): string
	{
		return $this->moduleName;
	}

	/**
	 * Function to set raw data.
	 *
	 * @param bool $rawData
	 *
	 * @return self
	 */
	public function setRawData(bool $rawData): self
	{
		$this->rawData = $rawData;
		return $this;
	}

	/**
	 * Set custom fields.
	 *
	 * @param array $fields
	 *
	 * @return self
	 */
	public function setFields(array $fields): self
	{
		$this->fields = $fields;
		return $this;
	}

	/**
	 * Set limit.
	 *
	 * @param int $limit
	 *
	 * @return self
	 */
	public function setLimit(int $limit): self
	{
		$this->limit = $limit;
		return $this;
	}

	/**
	 * Set offset.
	 *
	 * @param int $offset
	 *
	 * @return self
	 */
	public function setOffset(int $offset): self
	{
		$this->offset = $offset;
		return $this;
	}

	/**
	 * Set order.
	 *
	 * @param string $field
	 * @param string $direction
	 *
	 * @return self
	 */
	public function setOrder(string $field, string $direction): self
	{
		$this->orderField = $field;
		$this->order = $direction;
		return $this;
	}

	/**
	 * Set conditions.
	 *
	 * @param array $conditions
	 *
	 * @return $this
	 */
	public function setConditions(array $conditions)
	{
		$this->conditions = $conditions;
		return $this;
	}

	/**
	 * Set custom view ID.
	 *
	 * @param int $cvId
	 *
	 * @return $this
	 */
	public function setCvId(int $cvId): self
	{
		$this->cvId = $cvId;
		return $this;
	}

	/**
	 * Load a list of records from the API.
	 *
	 * @return $this
	 */
	public function loadRecordsList()
	{
		$this->recordsList = $this->getFromApi($this->getApiHeaders());
		return $this;
	}

	/**
	 * Gets headers for api.
	 *
	 * @return array
	 */
	public function getApiHeaders(): array
	{
		$headers = [
			'x-row-count' => 1,
			'x-row-limit' => $this->limit,
			'x-row-offset' => $this->offset,
		];
		if (!empty($this->fields)) {
			$headers['x-fields'] = \App\Json::encode($this->fields);
		}
		if (!empty($this->conditions)) {
			$headers['x-condition'] = \App\Json::encode($this->conditions);
		}
		if ($this->rawData) {
			$headers['x-raw-data'] = 1;
		}
		if (!empty($this->order) && $this->orderField) {
			$headers['x-order-by'] = \App\Json::encode([$this->orderField => $this->order]);
		}
		if ($cvId = $this->getDefaultCustomView()) {
			$headers['x-cv-id'] = $cvId;
		}
		return $headers;
	}

	/**
	 * Get data from api.
	 *
	 * @param array $headers
	 *
	 * @return array
	 */
	protected function getFromApi(array $headers): array
	{
		$api = \App\Api::getInstance();
		$api->setCustomHeaders($headers);
		return $api->call($this->getModuleName() . '/' . $this->actionName);
	}

	/**
	 * Get records list model.
	 *
	 * @return Record[]
	 */
	public function getRecordsListModel(): array
	{
		$recordsModel = [];
		if (!empty($this->recordsList['records'])) {
			foreach ($this->recordsList['records'] as $id => $value) {
				$recordModel = Record::getInstance($this->getModuleName());
				if (isset($value['recordLabel'])) {
					$recordModel->setName($value['recordLabel']);
					unset($value['recordLabel']);
				}
				if (isset($this->recordsList['rawData'][$id])) {
					$recordModel->setRawData($this->recordsList['rawData'][$id]);
				}
				$recordModel->setData($value)->setId($id)->setPrivileges($this->recordsList['permissions'][$id]);
				$recordsModel[$id] = $recordModel;
			}
		}
		return $recordsModel;
	}

	/**
	 * Get headers of list.
	 *
	 * @return array
	 */
	public function getHeaders(): array
	{
		if (empty($this->recordsList)) {
			$headers = ['x-only-column' => 1];
			if ($cvId = $this->getDefaultCustomView()) {
				$headers['x-cv-id'] = $cvId;
			}
			$this->recordsList = $this->getFromApi($headers);
		}
		return $this->recordsList['headers'] ?? [];
	}

	/**
	 * Gets custom views.
	 *
	 * @return array
	 */
	public function getCustomViews(): array
	{
		if (null === $this->customViews) {
			if (\App\Cache::has('CustomViews', $this->getModuleName())) {
				$this->customViews = \App\Cache::get('CustomViews', $this->getModuleName());
			} else {
				$this->customViews = \App\Api::getInstance()->call($this->getModuleName() . '/CustomView') ?: [];
				\App\Cache::save('CustomViews', $this->getModuleName(), $this->customViews, \App\Cache::LONG);
			}
		}
		return $this->customViews;
	}

	/**
	 * Get default custom view ID.
	 *
	 * @return int|null
	 */
	public function getDefaultCustomView(): ?int
	{
		if (!$this->cvId) {
			foreach ($this->getCustomViews() as $cvId => $cvData) {
				if ($cvData['isDefault']) {
					$this->cvId = $cvId;
					break;
				}
			}
		}
		return $this->cvId;
	}

	/**
	 * Get all rows count.
	 *
	 * @return int
	 */
	public function getCount(): int
	{
		return $this->recordsList['numberOfAllRecords'] ?? 0;
	}

	/**
	 * Get current page.
	 *
	 * @return int
	 */
	public function getPage(): int
	{
		if (!$this->page) {
			$this->page = floor($this->recordsList['numberOfRecords'] / ($this->recordsList['numberOfAllRecords'] ?: 1)) ?: 1;
		}
		return $this->page;
	}

	/**
	 * Sets page number.
	 *
	 * @param int $page
	 *
	 * @return $this
	 */
	public function setPage(int $page)
	{
		$this->page = $page;
		return $this;
	}

	/**
	 * Is there more pages.
	 *
	 * @return bool
	 */
	public function isMorePages(): bool
	{
		return $this->recordsList['isMorePages'] ?? false;
	}
}
