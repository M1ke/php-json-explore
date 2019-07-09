<?php
namespace M1ke\JsonExplore;

use InvalidArgumentException;

class JsonExplore {
	/**
	 * @var array
	 */
	private $data;
	/**
	 * @var array
	 */
	private $analysis;

	/**
	 * JsonExplore constructor.
	 * @param array $data
	 */
	private function __construct(array $data){
		$this->data = $data;
	}

	/**
	 * @param string $json
	 * @return JsonExplore
	 * @throws InvalidArgumentException
	 */
	public static function fromJson(string $json){
		$data = json_decode($json, true);

		if (!is_array($data)){
			throw new InvalidArgumentException("The provided JSON string could not be decoded: ".json_last_error_msg());
		}

		if (empty($data)){
			throw new InvalidArgumentException("The provided JSON string decoded to an empty array, which cannot be parsed");
		}

		return new self($data);
	}

	/**
	 * @param array $arr
	 * @return JsonExplore
	 */
	public static function fromArray(array $arr){
		if (empty($arr)){
			throw new InvalidArgumentException("The provided array was empty and cannot be parsed");
		}

		return new self($arr);
	}

	/**
	 * @param object $obj
	 * @return JsonExplore
	 */
	public static function fromObj(object $obj){
		$arr = (array)$obj;

		if (empty($arr)){
			throw new InvalidArgumentException("The provided object was empty when cast to an array and cannot be parsed");
		}

		return new self($arr);
	}

	/**
	 * @return $this
	 */
	public function analyse(){
		$analysis = $this->recurseTypes($this->data);
		$analysis = $this->recurseCompress($analysis);

		$this->analysis = $analysis;

		return $this;
	}

	/**
	 * @param array $arr
	 * @param array $analysis
	 * @return array
	 */
	private function recurseTypes(array $arr, array $analysis = []){
		$is_list = array_values($arr)===$arr;

		foreach ($arr as $key => $val){
			if ($is_list && is_array($val)){
				$analysis[0] = $this->recurseTypes($val, $analysis[0] ?? []);

				continue;
			}

			if (!$is_list && is_array($val) && !empty($val)){
				$analysis[$key] = $this->recurseTypes($val, $analysis[$key] ?? []);
			}
			else {
				$type = $this->getType($val);

				if ($is_list){
					$analysis[$type] = true;
				}
				else {
					$analysis[$key][$type] = true;
				}
			}
		}

		return $analysis;
	}

	/**
	 * @param mixed $val
	 * @return string
	 */
	private function getType($val){
		$type = gettype($val);
		if ($type==='string' && $this->isDate($val)){
			$type = 'date';
		}
		if ($type==='array'){
			$type .= '[empty]';
		}

		return $type;
	}

	private function isDate(string $val){
		switch (true){
			case \DateTime::createFromFormat('Y-m-d H:i:s', $val):
			case \DateTime::createFromFormat('Y-m-d', $val):
				return true;
		}

		return false;
	}

	/**
	 * @param array $analysis
	 * @return array
	 */
	private function recurseCompress(array $analysis){
		foreach ($analysis as &$val){
			$first_item = reset($val);
			if (is_array($first_item)){
				$val = $this->recurseCompress($val);
			}
			else {
				$val = implode('|', array_keys($val));
			}
		}

		ksort($analysis);

		return $analysis;
	}

	/**
	 * Dumps the analysis tree using var_dump
	 *
	 * @return $this
	 */
	public function dump(){
		var_dump($this->analysis);

		return $this;
	}

	/**
	 * @return string
	 */
	public function asJson(){
		return json_encode($this->analysis, JSON_PRETTY_PRINT);
	}

	/**
	 * @return array
	 */
	public function asPaths(){
		$analysis = $this->analysis;

		$list = $this->asPathsRecurse($analysis);

		return $list;
	}

	/**
	 * @return string
	 */
	public function asPathString(){
		return implode("\n", $this->asPaths());
	}

	/**
	 * @param array $analysis
	 * @param array $list
	 * @param string $prefix
	 * @return array
	 */
	private function asPathsRecurse(array $analysis, array $list = [], string $prefix = ''){
		foreach ($analysis as $key => $val){
			$key_path = "{$prefix}{$key}";
			if (is_array($val)){
				$list = $this->asPathsRecurse($val, $list, "{$key_path}.");
			}
			else {
				$list[] = "{$key_path}: $val";
			}
		}

		return $list;
	}

	/**
	 * @param array $arr
	 * @param array $param
	 * @return array
	 */
	private function arrayTypes(array $arr, array $param){
		foreach ($arr as $value){
			$type = $this->getType($value);
			$param[$type] = true;
		}

		return $param;
	}
}
