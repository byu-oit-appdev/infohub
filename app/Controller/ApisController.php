<?php

class ApisController extends AppController {
	public $uses = ['CollibraAPI', 'BYUAPI'];
	public $helpers = ['Fieldset'];

	public function index() {
		$hosts = $this->CollibraAPI->getApiHosts();
		if (count($hosts) == 1) {
			return $this->redirect(['action' => 'host', 'hostname' => $hosts[0]]);
		}
		$this->set('hosts', $hosts);
	}

	public function host($hostname) {
		if ($this->Session->check('recentAPIs')) {
			$this->set('recent', $this->Session->read('recentAPIs'));
		}

		$community = $this->CollibraAPI->findTypeByName('community', $hostname, ['full' => true]);
		if (empty($community->resourceId)) {
			$this->redirect(['action' => 'index']);
		}
		if (!empty($community->vocabularyReferences->vocabularyReference)) {
			usort(
				$community->vocabularyReferences->vocabularyReference,
				function ($a, $b) {
					return strcmp(strtolower($a->name), strtolower($b->name));
				});
		}
		$this->set(compact('hostname', 'community'));
	}

	public function view() {
		$args = func_get_args();
		$hostname = array_shift($args);
		$basePath = '/' . implode('/', $args);
		if (!isset($this->request->query['upper'])) {
			$basePath = strtolower($basePath);
		}
		$terms = $this->CollibraAPI->getApiTerms($hostname, $basePath, true);
		if (empty($terms) && !isset($this->request->query['upper'])) {
			return $this->redirect($hostname.'/'.implode('/', $args).'?upper=1');
		}
		if (empty($terms)) {
			//Check if non-existent API, or simply empty API
			$community = $this->CollibraAPI->findTypeByName('community', $hostname, ['full' => true]);
			if (empty($community->vocabularyReferences->vocabularyReference)) {
				return $this->redirect(['action' => 'host', 'hostname' => $hostname]);
			}
			$found = false;
			foreach ($community->vocabularyReferences->vocabularyReference as $endpoint) {
				if ($endpoint->name == $basePath) {
					$found = true;
					break;
				}
			}
			if (!$found) {
				return $this->redirect(['action' => 'host', 'hostname' => $hostname]);
			}
		}
		$containsFieldset = false;
		foreach ($terms as $term) {
			if (!empty($term->descendantFields)) {
				$containsFieldset = true;
				break;
			}
		}
		$isOITEmployee = $this->BYUAPI->isGROGroupMember($this->Auth->user('username'), 'oit04');
		$this->set(compact('hostname', 'basePath', 'terms', 'isOITEmployee', 'containsFieldset'));

		$arrRecent = $this->Session->check('recentAPIs') ? $this->Session->read('recentAPIs') : [];
		array_unshift($arrRecent, $basePath);
		$arrRecent = array_unique($arrRecent);
		$this->Session->write('recentAPIs', array_slice($arrRecent, 0, 5));

		if (array_key_exists('checkout', $this->request->query)) {
			return $this->_autoCheckout($hostname, $basePath, $terms);
		}
	}

	protected function _autoCheckout($hostname, $basePath, $terms) {
		$queue = $this->Session->read('queue');
		foreach ($terms as $term) {
			if (empty($term->businessTerm[0])) {
				continue;
			}
			$queue->businessTerms[$term->businessTerm[0]->termId] = [
				'term' => $term->businessTerm[0]->term,
				'communityId' => $term->businessTerm[0]->termCommunityId,
				'apiHost' => $hostname,
				'apiPath' => $basePath];
		}

		$this->Session->write('queue', $queue);
		return $this->redirect(['controller' => 'request', 'action' => 'index']);
	}

	public function deep_links() {
		$args = func_get_args();
		$hostname = array_shift($args);
		$basePath = '/' . implode('/', $args);
		return new CakeResponse(['type' => 'json', 'body' => json_encode($this->BYUAPI->deepLinks($basePath))]);
	}
}
