<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);
require(dirname(dirname(__FILE__)) . '/init.php');

$id = param_integer('id');
$user = get_record('usr', 'id', $id, 'deleted', 0);
$user->introduction = get_field('artefact', 'description', 'artefacttype', 'introduction', 'owner', $id);

$smarty = smarty_core();
$smarty->assign('user', $user);
$html = $smarty->fetch('user/simpleuser.tpl');

json_reply(false, array(
    'message' => null,
    'html' => $html,
));
