<?php
/**
 * Native template engine realisation.
 * Author: Roman Hinex
 */

class View
{
  private $_array = [];
  private $_title = 'Default';
  private static $_action;
  private static $_isWeb = true;

  protected static $_instances = [];
  protected function __construct() {}
  protected function __clone() {}
  public static function main() {
    $class = get_called_class();
    if (!isset(self::$_instances[$class])) {
      header('Content-type: text/html; charset=UTF-8;');
      self::$_isWeb = !isset($_SESSION['mobile']);
      self::$_instances[$class] = new $class();
      $_SESSION['url'] = $_SERVER['REQUEST_URI'];
    }
    return self::$_instances[$class];
  }

  /**
   * @return bool
   */
  public function isWeb()
  {
    return self::$_isWeb;
  }

  /**
   * @return string
   */
  public function getTheme()
  {
    return self::$_isWeb?'web':'mobile';
  }

  /**
   * @param $controller
   * @param $action
   *
   * @return string
   */
  private function generateContent($template)
  {
    global $board, $db;
    $path = DIR . '/design/'.$this->getTheme().'/pages/' . $template . '.phtml';
    if (isset($path))
    {
      ob_start();
      include_once $path;
      $content = ob_get_contents();
      ob_end_clean();
      return $content;
    }
    return 'Template "' . $action . '" not found';
  }
  /**
   * @param $value
   *
   * @return mixed
   */
  public function setTitle($value)
  {
    return $this->_title = $value;
  }
  /**
   * @return string
   */
  protected function getTitle()
  {
    return $this->_title;
  }
  /**
   * @param $index
   * @param $value
   *
   * @return mixed
   */
  public function set($index, $value)
  {
    return $this->_array[$index] = $value;
  }
  /**
   * @param $index
   *
   * @return bool
   */
  protected function get($index)
  {
    return isset($this->_array[$index]) ? $this->_array[$index] : FALSE;
  }
  /**
   * @return mixed
   */
  protected function getContent()
  {
    return static::$_action;
  }
  /**
   * @param bool   $template
   * @param string $base
   */
  public function render($template = FALSE, $base = 'base')
  {
    global $board, $db, $action, $top;
    defined('ADVANCED_ON') or define('ADVANCED_ON', false);
    self::$_action = self::generateContent($template);
    include DIR . '/design/'.$this->getTheme().'/' . $base . '.phtml';
  }
}
