<?php
use Rs\Json\Pointer;

class Swagger extends AppModel {

	public $useTable = false;

	/** @var Pointer $swag */
	private $swag;
	private $elements;
	public $parseErrors = [];

	public function parse($json) {
		try {
			$this->swag = new Pointer($json);
		} catch (Exception $e) {
			$this->parseErrors[] = "Unable to parse Swagger doc";
			return false;
		}

		try {
			$paths = $this->swag->get('/paths');
		} catch (Exception $e) {
			$this->parseErrors[] = "Unable to find /paths in Swagger";
			return false;
		}

		$this->elements = [];
		foreach ($paths as $pathName => $path) {
			foreach ($path as $method => $operation) {
				$response = empty($operation['responses'][200]['$ref']) ? $operation['responses'][200] : $this->_getRef($operation['responses'][200]['$ref'])[1];
				if (empty($response['schema'])) {
					continue;
				}
				$schema = $response['schema'];

				if (!empty($schema['$ref'])) {
					list($name, $refProperties) = $this->_getRef($schema['$ref']);
					$properties = [$name => $refProperties];
				} else {
					$properties = ["result" => $schema];
				}
				$this->_addElements([], $properties);
			}
		}

		$hostRaw = $this->_getRef('/host');
		$host = $hostRaw[1];
		if (preg_match('/:443$/', $host)) {
			$host = substr($host, 0, strlen($host) - 4);
		}
		$basePath = $this->_getRef('/basePath');
		if (substr($basePath[1], -1) === '/') {
			$basePath[1] = substr($basePath[1], 0, -1);
		}
		$versionMatches = [];
		if (preg_match_all('/\/v[0-9]+(\.[0-9]+)*/', $basePath[1], $versionMatches)) {
			$basePath[1] = substr($basePath[1], 0, -strlen(end($versionMatches[0])));
		}
		$version = $this->_getRef('/info/version')[1];

		if (empty($this->elements)) {
			$this->parseErrors[] = "No fields found in Swagger";
			return false;
		}

		return [
			'host' => $host,
			'basePath' => $basePath[1],
			'version' => $version,
			'elements' => array_values($this->elements)
		];
	}

	public function downloadFile($url) {
		$requestOptions = [];
		if (preg_match('/api\.github\.com/', $url)) {
			//Special case for Github: need custom auth token
			$requestOptions['header'] = [
				'Accept' => 'application/vnd.github.v3.raw',
				'Authorization' => 'token ' . Configure::read('github.api_token')
			];
		}

		App::uses('HttpSocket', 'Network/Http');
		$HttpSocket = new HttpSocket();
		$HttpSocket->configProxy(Configure::read('proxy'));
		$results = $HttpSocket->get($url, [], $requestOptions);
		if (!$results || !$results->isOk()) {
			return null;
		}

		return $results->body();
	}

	protected function _getRef($ref) {
		if (strpos($ref, '#') !== false) {
			$ref = substr($ref, strpos($ref, '#') + 1);
		}
		$refBaseName = array_slice(explode('/', $ref), -1)[0];
		try {
			return [$refBaseName, $this->swag->get($ref)];
		} catch (Exception $e) {
			return [null, null];
		}
	}

	protected function _addElements($mainParents, $properties) {
		foreach ($properties as $propertyName => $property) {
			if ($propertyName == 'links' || $propertyName == 'metadata') {
				continue;
			}
			if (empty($property['type']) && !empty($property['$ref'])) {
				if (preg_match('#/api_([^/]*)$#', $property['$ref'], $matches)) {
					$property['type'] = $matches[1];
				} else {
					list($refName, $property) = $this->_getRef($property['$ref']);
					if ($refName != 'response') {
						$propertyName = $refName;
					}
				}
			}
			if (empty($property['type'])) {
				if (!empty($property['properties'])) {
					$property['type'] = 'object';
				} elseif (!empty($property['allOf'])) {
					foreach ($property['allOf'] as $subProp) {
						$this->_addElements($mainParents, [$propertyName => $subProp]);
					}
					continue;
				} else {
					continue;
				}
			}

			$parents = $mainParents;
			if (substr($propertyName, -5) != 'basic' && $propertyName != 'identity' && $propertyName != 'result' && $propertyName != 'response') {
				$parents[] = $propertyName;
			}

			switch ($property['type']) {
				case 'object':
					if (!empty($property['oneOf'])) {
						foreach ($property['oneOf'] as $subProp) {
							$this->_addElements($mainParents, [$propertyName => $subProp]);
						}
						break;
					}
					if (empty($property['properties'])) {
						break;
					}
					if (!empty($property['properties']['api_type'])) {
						$this->_addElement($parents, $property);
						break;
					}
					if (count($property['properties']) == 2 && !empty($property['properties']['request']) && !empty($property['properties']['response'])) {
						$this->_addElements($mainParents, [$propertyName => $property['properties']['response']]);
						break;
					}
					$this->_addElements($parents, $property['properties']);
					break;
				case 'array':
					if (empty($property['items'])) {
						break;
					}
					$parents = $mainParents;
					if ($propertyName == 'values') {
						$propertyName = array_pop($parents);
					}
					$this->_addElements($parents, [$propertyName => $property['items']]);
					break;
				default:
					$this->_addElement($parents, $property);
			}
		}
	}

	protected function _addElement($parents, $property, $type = 'field') {
		if (count($parents) > 1) {
			$this->_addElement(array_slice($parents, 0, -1), [], 'fieldset');
		}
		$key = implode('.', $parents);
		if (!array_key_exists($key, $this->elements)) {
			$this->elements[$key] = ['name' => $key, 'type' => $type];
		}
		if (!empty($property['description']) && empty($this->elements[$key]['description'])) {
			$this->elements[$key]['description'] = $property['description'];
		}
	}
}
