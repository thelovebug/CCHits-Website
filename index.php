<?php
/**
 * CCHits.net is a website designed to promote Creative Commons Music,
 * the artists who produce it and anyone or anywhere that plays it.
 * These files are used to generate the site.
 *
 * PHP version 5
 *
 * @category Default
 * @package  CCHitsClass
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     http://cchits.net Actual web service
 * @link     http://code.cchits.net Developers Web Site
 * @link     http://gitorious.net/cchits-net Version Control Service
 */

require_once dirname(__FILE__) . '/CLASSES/autoloader.php';

$generator = microtime(true);

$arrUri = UI::getUri();
$content = null;

try {
    if (is_array($arrUri)
        and isset($arrUri['path_items'])
        and is_array($arrUri['path_items'])
        and count($arrUri['path_items']) > 0
    ) {
        switch($arrUri['path_items'][0]) {
        case 'media':
            switch($arrUri['path_items'][1]) {
            case 'track':
            case 'tracks':
                $file = ConfigBroker::getConfig('fileBase', '/var/www/media') . ConfigBroker::getConfig('fileBaseTrack', '/tracks') . "/";
                break;
            case 'daily':
                $file = ConfigBroker::getConfig('fileBase', '/var/www/media') . ConfigBroker::getConfig('fileBaseDaily', '/daily') . "/";
                break;
            case 'weekly':
                $file = ConfigBroker::getConfig('fileBase', '/var/www/media') . ConfigBroker::getConfig('fileBaseWeekly', '/weekly') . "/";
                break;
            case 'monthly':
                $file = ConfigBroker::getConfig('fileBase', '/var/www/media') . ConfigBroker::getConfig('fileBaseMonthly', '/monthly') . "/";
                break;
            default:
                UI::sendHttpResponse(404);
            }
            $file .= $arrUri['path_items'][2] . '.' . $arrUri['format'];
            if (!file_exists($file)) {
                error_log("Could not find $file");
                UI::sendHttpResponse(404);
            } else {
                if ($arrUri['path_items'][1] == 'track' and TrackBroker::getTrackByID($arrUri['path_items'][2])->get_isApproved() == false and UserBroker::getUser()->isAdmin()) {
                    UI::sendHttpResponse(401);
                } else {
                    UI::dl_file_resumable($file, TRUE);
                }
            }
        case 'api':
            $content = new API();
            break;
        case 'openid':
            if (isset($_POST['id'])) {
                $content = OpenID::request($_POST['id'], $arrUri['basePath'] . 'openid', $arrUri['basePath'] . 'admin', $arrUri['basePath'] . 'admin');
            } elseif (isset($_REQUEST['return'])) {
                $content = OpenID::response($arrUri['basePath'] . 'openid');
            } elseif (isset($_GET['logout'])) {
                session_start();
                unset($_SESSION['OPENID_AUTH']);
                UI::redirect($arrUri['basePath'] . 'admin');
            } else {
                UI::redirect($arrUri['basePath'] . 'admin');
            }
            break;
        default:
            $content = new HTML();
        }
    } else {
        $content = new HTML();
    }
} catch(Exception $e) {
    error_log($e);
    die("An error occurred - we are looking into it.");
}