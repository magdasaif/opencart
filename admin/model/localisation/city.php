<?php
namespace Opencart\Admin\Model\Localisation;
/**
 * Class City
 *
 * Can be loaded using $this->load->model('localisation/city');
 *
 * @package Opencart\Admin\Model\Localisation
 */
class City extends \Opencart\System\Engine\Model {
	/**
	 * Add City
	 *
	 * Create a new city record in the database.
	 *
	 * @param array<string, mixed> $data array of data
	 *
	 * @return int
	 *
	 * @example
	 *
	 * $city_data = [
	 *     'city_description' => [],
	 *     'code'             => 'City Code',
	 *     'zone_id'       => 1,
	 *     'status'           => 0
	 * ];
	 *
	 * $this->load->model('localisation/city');
	 *
	 * $city_id = $this->model_localisation_city->addCity($city_data);
	 */
	public function addCity(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "city` SET `code` = '" . $this->db->escape((string)$data['code']) . "', `zone_id` = '" . (int)$data['zone_id'] . "', `status` = '" . (bool)($data['status'] ?? 0) . "'");

		$city_id = $this->db->getLastId();

		foreach ($data['city_description'] as $language_id => $city_description) {
			$this->model_localisation_city->addDescription($city_id, $language_id, $city_description);
		}

		$this->cache->delete('city');

		return $city_id;
	}

	/**
	 * Edit City
	 *
	 * Edit city record in the database.
	 *
	 * @param int                  $city_id primary key of the city record
	 * @param array<string, mixed> $data    array of data
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $city_data = [
	 *     'city_description' => [],
	 *     'code'             => 'City Code',
	 *     'zone_id'       	  => 1,
	 *     'status'           => 1
	 * ];
	 *
	 * $this->load->model('localisation/city');
	 *
	 * $this->model_localisation_city->editCity($city_id, $city_data);
	 */
	public function editCity(int $city_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "city` SET `code` = '" . $this->db->escape((string)$data['code']) . "', `zone_id` = '" . (int)$data['zone_id'] . "', `status` = '" . (bool)($data['status'] ?? 0) . "' WHERE `city_id` = '" . (int)$city_id . "'");

		$this->model_localisation_city->deleteDescriptions($city_id);

		foreach ($data['city_description'] as $language_id => $city_description) {
			$this->model_localisation_city->addDescription($city_id, $language_id, $city_description);
		}

		$this->cache->delete('city');
	}

	/**
	 * Delete City
	 *
	 * Delete city record in the database.
	 *
	 * @param int $city_id primary key of the city record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('localisation/city');
	 *
	 * $this->model_localisation_city->deleteCity($city_id);
	 */
	public function deleteCity(int $city_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "city` WHERE `city_id` = '" . (int)$city_id . "'");

		$this->model_localisation_city->deleteDescriptions($city_id);

		$this->cache->delete('city');
	}

	/**
	 * Get City
	 *
	 * Get the record of the city record in the database.
	 *
	 * @param int $city_id primary key of the city record
	 *
	 * @return array<string, mixed> city record that has city ID
	 *
	 * @example
	 *
	 * $this->load->model('localisation/city');
	 *
	 * $city_info = $this->model_localisation_city->getCity($city_id);
	 */
	public function getCity(int $city_id): array {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "city` `z` LEFT JOIN `" . DB_PREFIX . "city_description` `zd` ON (`z`.`city_id` = `zd`.`city_id`) WHERE `z`.`city_id` = '" . (int)$city_id . "' AND `zd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	/**
	 * Get Citys
	 *
	 * Get the record of the city records in the database.
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return array<int, array<string, mixed>> city records
	 *
	 * @example
	 *
	 * $filter_data = [
	 *     'filter_name'    => 'City Name',
	 *     'filter_zone' => 'zone Name',
	 *     'filter_code'    => 'City Code',
	 *     'sort'           => 'c.name',
	 *     'order'          => 'DESC',
	 *     'start'          => 0,
	 *     'limit'          => 10
	 * ];
	 *
	 * $this->load->model('localisation/city');
	 *
	 * $results = $this->model_localisation_city->getCities($filter_data);
	 */
	public function getCities(array $data = []): array {
		$sql = "SELECT *,`cd`.`name` AS `city_name` ,  `zd`.`name` AS `zone_name` FROM `" . DB_PREFIX . "city` `c` LEFT JOIN `" . DB_PREFIX . "city_description` `cd` ON (`c`.`city_id` = `cd`.`city_id` AND `cd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "') LEFT JOIN `" . DB_PREFIX . "zone_description` `zd` ON (`c`.`zone_id` = `zd`.`zone_id` AND `zd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "')";

		$implode = [];

		if (!empty($data['filter_name'])) {
			$implode[] = "LCASE(`cd`.`name`) LIKE '" . $this->db->escape(oc_strtolower($data['filter_name']) . '%') . "'";
		}

		if (!empty($data['filter_zone'])) {
			$implode[] = "`zd`.`name` LIKE '" . $this->db->escape(oc_strtolower($data['filter_zone']) . '%') . "'";
		}

		if (!empty($data['filter_code'])) {
			$implode[] = "LCASE(`c`.`code`) LIKE '" . $this->db->escape(oc_strtolower($data['filter_code']) . '%') . "'";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$sort_data = [
			'zd.name',
			'cd.name',
			'c.code'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY `zd`.`name`";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	/**
	 * Get Cities By zone ID
	 *
	 * Get the record of cities by zone records in the database.
	 *
	 * @param int $zone_id primary key of the zone record
	 *
	 * @return array<int, array<string, mixed>> city records that have zone ID
	 *
	 * @example
	 *
	 * $this->load->model('localisation/city');
	 *
	 * $citys = $this->model_localisation_city->getCitiesByZoneId($zone_id);
	 */
	public function getCitiesByZoneId(int $zone_id): array {
		$sql = "SELECT * FROM `" . DB_PREFIX . "city` `z` LEFT JOIN `" . DB_PREFIX . "city_description` `zd` ON (`z`.`city_id` = `zd`.`city_id`) WHERE `z`.`zone_id` = '" . (int)$zone_id . "' AND `zd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND `z`.`status` = '1' ORDER BY `zd`.`name`";

		$key = md5($sql);

		$city_data = $this->cache->get('city.' . $key);

		if (!$city_data) {
			$query = $this->db->query($sql);

			$city_data = $query->rows;

			$this->cache->set('city.' . $key, $city_data);
		}

		return $city_data;
	}

	/**
	 * Add Description
	 *
	 * Create a new city description record in the database.
	 *
	 * @param int                  $city_id     primary key of the city record
	 * @param int                  $language_id primary key of the language record
	 * @param array<string, mixed> $data        array of data
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $city_data['city_description'] = [
	 *     'name' => 'City Name',
	 * ];
	 *
	 * $this->load->model('localisation/city');
	 *
	 * $this->model_catalog_category->addDescription($city_id, $language_id, $city_data);
	 */
	public function addDescription(int $city_id, int $language_id, array $data): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "city_description` SET `city_id` = '" . (int)$city_id . "', `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape($data['name']) . "'");
	}

	/**
	 * Delete Descriptions
	 *
	 * Delete city description records in the database.
	 *
	 * @param int $city_id primary key of the city record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('localisation/city');
	 *
	 * $this->model_localisation_city->deleteDescriptions($city_id);
	 */
	public function deleteDescriptions(int $city_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "city_description` WHERE `city_id` = '" . (int)$city_id . "'");
	}

	/**
	 * Delete Descriptions By Language ID
	 *
	 * Delete city descriptions by language records in the database.
	 *
	 * @param int $language_id primary key of the language record
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->load->model('localisation/city');
	 *
	 * $this->model_localisation_city->deleteDescriptionsByLanguageId($zone_id, $language_id);
	 */
	public function deleteDescriptionsByLanguageId(int $language_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "city_description` WHERE `language_id` = '" . (int)$language_id . "'");
	}

	/**
	 * Get Descriptions
	 *
	 * Get the record of the city description records in the database.
	 *
	 * @param int $city_id primary key of the city record
	 *
	 * @return array<int, array<string, string>> description records that have city ID
	 *
	 * @example
	 *
	 * $this->load->model('localisation/city');
	 *
	 * $city_description = $this->model_localisation_city->getDescriptions($city_id);
	 */
	public function getDescriptions(int $city_id): array {
		$city_description_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "city_description` WHERE `city_id` = '" . (int)$city_id . "'");

		foreach ($query->rows as $result) {
			$city_description_data[$result['language_id']] = $result;
		}

		return $city_description_data;
	}

	/**
	 * Get Descriptions By Language ID
	 *
	 * Get the record of the city descriptions by language records in the database.
	 *
	 * @param int $language_id primary key of the language record
	 *
	 * @return array<int, array<string, string>> description records that have language ID
	 *
	 * @example
	 *
	 * $this->load->model('localisation/city');
	 *
	 * $results = $this->model_localisation_city->getDescriptionsByLanguageId($language_id);
	 */
	public function getDescriptionsByLanguageId(int $language_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "city_description` WHERE `language_id` = '" . (int)$language_id . "'");

		return $query->rows;
	}

	/**
	 * Get Total Citys
	 *
	 * Get the total number of total city records in the database.
	 *
	 * @param array<string, mixed> $data array of filters
	 *
	 * @return int total number of city records
	 *
	 * @example
	 *
	 * $filter_data = [
	 *     'filter_name'    => 'City Name',
	 *     'filter_zone'    => 'Zone Name',
	 *     'filter_code'    => 'City Code',
	 *     'sort'           => 'c.name',
	 *     'order'          => 'DESC',
	 *     'start'          => 0,
	 *     'limit'          => 10
	 * ];
	 *
	 * $this->load->model('localisation/city');
	 *
	 * $city_total = $this->model_localisation_city->getTotalCities();
	 */
	public function getTotalCities(array $data = []): int {
		$sql = "SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "city` `z`";

		if (!empty($data['filter_name'])) {
			$sql .= " LEFT JOIN `" . DB_PREFIX . "city_description` `zd` ON (`z`.`city_id` = `zd`.`city_id`) AND `zd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "'";
		}

		if (!empty($data['filter_zone'])) {
			$sql .= " LEFT JOIN `" . DB_PREFIX . "zone_description` `cd` ON (`z`.`zone_id` = `cd`.`zone_id` AND `cd`.`language_id` = '" . (int)$this->config->get('config_language_id') . "')";
		}

		$implode = [];

		if (!empty($data['filter_name'])) {
			$implode[] = "LCASE(`zd`.`name`) LIKE '" . $this->db->escape(oc_strtolower($data['filter_name']) . '%') . "'";
		}

		if (!empty($data['filter_zone'])) {
			$implode[] = "LCASE(`cd`.`name`) LIKE '" . $this->db->escape(oc_strtolower($data['filter_zone']) . '%') . "'";
		}

		if (!empty($data['filter_code'])) {
			$implode[] = "LCASE(`z`.`code`) LIKE '" . $this->db->escape(oc_strtolower($data['filter_code']) . '%') . "'";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$query = $this->db->query($sql);

		return (int)$query->row['total'];
	}

	/**
	 * Get Total Citys By zone ID
	 *
	 * Get the total number of total citys by zone records in the database.
	 *
	 * @param int $zone_id primary key of the zone record
	 *
	 * @return int total number of city records that have zone ID
	 *
	 * @example
	 *
	 * $this->load->model('localisation/city');
	 *
	 * $city_total = $this->model_localisation_city->getTotalCitiesByZoneId($zone_id);
	 */
	public function getTotalCitiesByZoneId(int $zone_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "city` WHERE `zone_id` = '" . (int)$zone_id . "'");

		return (int)$query->row['total'];
	}
}
