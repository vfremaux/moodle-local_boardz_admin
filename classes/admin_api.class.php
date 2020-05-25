<?php

namespace boardz;

/**
 *
 */
class admin_api {

    public static function call($method, $params) {
        global $CFG;

        $config = get_config('local_boardz_admin');

        $showremotequeries = optional_param('showqueries', false, PARAM_BOOL);

        $url = $config->baseurl.'/rest/server.php';
        $qs = '?token='.$config->admintoken;
        $qs .= '&wsfunction='.$method;
        $qs .= '&wwwroot='.urlencode($CFG->wwwroot);

        // Pass all params to the rest query.
        foreach ($params as $k => $v) {
            $qs .= '&'.$k.'='.urlencode($v);
        }

        if (function_exists('debug_trace')) {
            debug_trace('Boardz API Call : '.$url.$qs);
        }
        $ch = curl_init($url.$qs);

        if ($showremotequeries) {
            echo '<pre>';
            echo $url.$qs."\n";
            echo '</pre>';
        }

        $timeout = 30; // 30 seconds in standard.

        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Moodle');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml charset=UTF-8"));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if (!empty($CFG->proxytype)) {
            if ($CFG->proxytype == 'SOCKS5') {
                $proxytype = CURLPROXY_SOCKS5;
            } else {
                $proxytype = CURLPROXY_HTTP;
            }
            curl_setopt($ch, CURLOPT_PROXYTYPE, $proxytype);
        }

        $rawinput = curl_exec($ch);
        $input = json_decode($rawinput);
        $error = curl_error($ch);
        $info = curl_getinfo($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpcode != 200 && $httpcode != 301) {
            if ($CFG->debug == DEBUG_DEVELOPER) {
                mtrace("CURL: {$url}{$qs}<br/>\n");
            }
            throw new \Exception("Error in Boardz communication : Bad http response :  $httpcode \n");
        }

        if ($error) {
            if ($CFG->debug == DEBUG_DEVELOPER) {
                print_object($error);
            }
            throw new \Exception("Error in Boardz communication : Curl error.");
        }

        if (is_null($input)) {
            if ($CFG->debug == DEBUG_DEVELOPER) {
                echo '<pre>';
                echo $url.$qs."\n";
                echo "\n";
                echo 'Raw input : '.$rawinput."\n";
                echo 'Curl info : '.print_r($info, true)."\n";
                echo "\n";
                echo '</pre>';
            }
            throw new \Exception("Error in Boardz communication : Empty or unparsable returned object. The cause of this may be caused by debugging extraneous outputs that alter the expected json syntax. check in debug mode to see the call ra response.");
        }

        if (!empty($input->error)) {
            if ($CFG->debug == DEBUG_DEVELOPER) {
                print_object($error);
            }
            throw new \Exception("Boardz aplication error : $input->error with {$url}{$qs}");
        }

        return $input;
    }

    /**
     * Extract from defines usefull information for making the administration form.
     * Defines is an array of descriptors indexed by class name. Each descriptor provides
     * the list of fields arranged by inheritance layer.
     * @param array $defines
     */
    public static function process_defines_for_form($defines) {

        $attributes = [];
        foreach ($defines as $defineclass => $define) {
            self::process_define_for_form($attributes, $defineclass, $defineclass, $define);
        }
        return $attributes;
    }

    /**
     * this is a recursive "dig down" converter that assembles defines into an
     * attribute list for administration forms. Collect process starts with the highest
     * parent in hierarchy. Children will override or add some additional properties to
     * a deeper inheritance level declaration.
     * @param &$attributes the output attribute resulting structure
     * @param $defineclass the current class the defines struct is documenting
     * @param $define the define structure for the current class
     */
    public static function process_define_for_form(&$attributes, $defineclass, $topclass, $define) {
        // mtrace("Processing $defineclass <br/>");
        if (!empty($define[0]->parent)) {
            self::process_define_for_form($attributes, $define[0]->parentclass, $topclass, $define[0]->parent);
        }
        if (!empty($define[0]->fields)) {
            foreach ($define[0]->fields as $fieldname => $desc) {
                if ($fieldname == 'deleted' || $fieldname == 'deletetime') {
                    continue;
                }
                if (!array_key_exists($fieldname, $attributes)) {
                    $attrdesc = (object) $desc;
                    // mtrace("Adding $topclass to $fieldname<br/>");
                    $attrdesc->classes[] = $topclass;
                    $attributes[$fieldname] = $attrdesc;
                } else {
                    // If exists, overrides attributes from the current layer.
                    // mtrace("Adding $topclass to $fieldname<br/>");
                    $attributes[$fieldname]->classes[] = $topclass;
                    foreach ($desc as $descname => $descvalue) {
                        $attributes[$fieldname]->$descname = $descvalue;
                    }
                }
            }
        }

        // Process extension attributes.
        if (!empty($define[0]->attributes)) {
            foreach ($define[0]->attributes as $fieldname => $desc) {
                if (!array_key_exists($fieldname, $attributes)) {
                    $attrdesc = (object) $desc;
                    // mtrace("Adding $topclass to $fieldname (attr)<br/>");
                    $attrdesc->classes[] = $topclass;
                    $attributes[$fieldname] = $attrdesc;
                } else {
                    // If exists, overrides attributes from the current layer.
                    // mtrace("Adding $topclass to $fieldname (attr)<br/>");
                    $attributes[$fieldname]->classes[] = $topclass;
                    foreach ($desc as $descname => $descvalue) {
                        $attributes[$fieldname]->$descname = $descvalue;
                    }
                }
            }
        }
    }
}