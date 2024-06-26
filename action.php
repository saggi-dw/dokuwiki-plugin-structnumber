<?php
/**
 * DokuWiki Plugin structnumber (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  saggi <saggi@gmx.de>
 */

class action_plugin_structnumber extends \dokuwiki\Extension\ActionPlugin {
    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
  public function register(Doku_Event_Handler $controller) {
    $controller->register_hook('PLUGIN_STRUCT_TYPECLASS_INIT', 'BEFORE', $this, 'handle_init');
  }

  public function handle_init(Doku_Event &$event, $param) {
    $event->data['Number'] = 'dokuwiki\\plugin\\structnumber\\types\\Number';
  }
}

