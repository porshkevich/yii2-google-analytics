<?php

/*
 *  @link https://github.com/porshkevich/yii2-google-analytics
 *  @copyright Copyright (c) 2015 NeoSonic
 *  @license http://opensource.org/licenses/MIT
 */

namespace porshkevich\googleanalytics;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\web\View;
use yii\helpers\Json;

/**
 * Description of GoogleAnalyticsAPI
 *
 * @author NeoSonic
 */
class GoogleAnalyticsAPI extends Component {

	/**
	 *
	 * @var string
	 */
	public $defaultTrackingId;

	/**
	 *
	 * @var array
	 */
	public $defaultTrackingOpts = [];

	/**
	 *
	 * @var boolean Whether the component registered in View
	 */
	public $autoreg = true;

	/**
	 *
	 * @var type Whether the component send user id
	 */
	public $useUserId = true;

	private $_sends = [];
	private $_sets = [];
	private $_plugins = [];


	public function init()
	{
		if (!$this->defaultTrackingId)
			throw new InvalidConfigException('The "defaultTrackingId" property must be set.');

		if ($this->autoreg) {
			$view = Yii::$app->view;
			if ($view instanceof View) {
				$view->on(View::EVENT_BEFORE_RENDER, [$this,'viewBeforeRenderHandler']);
			}
		}

		$this->sendPageview();
	}

	/**
	 *
	 * @param \yii\base\ViewEvent $event
	 */
	public function viewBeforeRenderHandler($event)
	{
		$this->register($event->sender);
	}

	/**
	 *
	 * @param View $view
	 */
	public function register($view)
	{
		$js = "(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
						(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
						m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
					})(window,document,'script','//www.google-analytics.com/analytics.js','ga');\n";

		if ($this->useUserId && !Yii::$app->user->isGuest)
			$this->defaultTrackingOpts['userId'] = 'gauid_'.Yii::$app->user->id;

		$opts = !$this->defaultTrackingOpts?:', ' . Json::encode($this->defaultTrackingId);

		$js .= "ga('create', '<?= {$this->defaultTrackingId} ?>', 'auto'$opts);";
		if ($this->_plugins)
			$js .= implode("\n", $this->_plugins);
		if ($this->_sets)
			$js .= "ga('set', " . Json::encode($this->_sets) . ");";
		if ($this->_sends)
			$js .= implode("\n", $this->_sends);

		$view->registerJs($js, View::POS_HEAD, 'ga');
	}

	public function set($key, $value = null)
	{
		if(is_array($key)) {
			foreach ( $key as $k => $v ) {
				$this->_sets[$k] = $v;
			}
		}
		elseif ($value) {
			$this->_sets[$key] = $value;
		}
		else
		{
			unset($this->_sets[$key]);
		}
	}

	protected function send($hitType, $options = [], $trackerName = '')
	{
		$options['hitType'] = $hitType;
		$opts = Json::encode($options);
		$method = $trackerName ? $trackerName.'.send':'send';
		$this->_sends[] = "ga('$method', $opts);";
	}

	public function sendEvent($category, $action, $label = '', $value = '', $trackerName = '')
	{
		$options = [
			'eventCategory' => $category,
			'eventAction' => $action,
		];

		if ($label) $options['eventLabel'] = $label;
		if ($value) $options['eventValue'] = $value;

		$this->send('event', $options, $trackerName);
	}

	public function sendPageview($options = [], $trackerName = [])
	{
		$this->send('pageview', $options, $trackerName);
	}

	public function requirePlugin($name, $options)
	{
		$opts = !$options?:', ' . Json::encode($options);
		$this->_plugins[] = "ga('require','$name'$opts);";
	}
}
