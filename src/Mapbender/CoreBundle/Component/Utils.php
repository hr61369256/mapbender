<?php
namespace Mapbender\CoreBundle\Component;

/**
 * The class with utility functions.
 *
 * @author Paul Schmidt
 */
class Utils
{

    /**
     * Checks the variable $booleanOrNull and returns the boolean or null.
     * @param type $booleanOrNull
     * @param type $nullable
     * @return boolean if $nullable is false, otherwise boolean or null.
     */
    public static function getBool($booleanOrNull, $nullable = false)
    {
        if ($nullable) {
            return $booleanOrNull;
        } else {
            return $booleanOrNull === null ? false : $booleanOrNull;
        }
    }

    /**
     * Generats an URL from base url and GET parameters
     * 
     * @param string $baseUrl A base URL
     * @param string $parameters GET Parameters as array or as string
     * @return generated Url
     */
    public static function getHttpUrl($baseUrl, $parameters)
    {
        $url = "";
        $pos = strpos($baseUrl, "?");
        if ($pos === false) {
            $url = $baseUrl . "?";
        } else if (strlen($baseUrl) - 1 !== $pos) {
            $pos = strpos($baseUrl, "&");
            if ($pos === false) {
                $url = $baseUrl . "&";
            } else if (strlen($baseUrl) - 1 !== $pos) {
                $url = $baseUrl . "&";
            } else {
                $url = $baseUrl;
            }
        } else {
            $url = $baseUrl;
        }
        if (is_string($parameters)) {
            return $url . $parameters;
        } else if (is_array($parameters)) {
            $params = array();
            foreach ($parameters as $key => $value) {
                if (is_string($key)) {
                    $params[] = $key . "=" . $value;
                } else {
                    $params[] = $value;
                }
            }
            return $url . implode("&", $params);
        }
        return null;
    }

    /**
     * Removes a file or directory (recursive)
     * 
     * @param string $path tha path of file/directory
     * @return boolean true if the file/directory is removed.
     */
    public static function deleteFileAndDir($path)
    {
        $class_func = array(__CLASS__, __FUNCTION__);
        return is_file($path) ?
            @unlink($path) :
            array_map($class_func, glob($path . '/*')) == @rmdir($path);
    }

    /**
     * Validates an URL
     * 
     * @param string $url URL
     * @param array $paramsToRemove  array of lower case parameter names to 
     * remove from url
     * @return string URL without parameter $paramName
     */
    public static function validateUrl($url, $paramsToRemove)
    {
        $rowUrl = parse_url($url);
        $newurl = $rowUrl["scheme"] . "://" . $rowUrl['host'];
        if (isset($rowUrl['port']) && intval($rowUrl['port']) !== 80) {
            $newurl .= ':' . $rowUrl['port'];
        }
        if (isset($rowUrl['path']) && strlen($rowUrl['path']) > 0) {
            $newurl .= $rowUrl['path'];
        }
        $queries = array();
        $getParams = array();
        if (isset($rowUrl["query"])) {
            parse_str($rowUrl["query"], $getParams);
        }
        foreach ($getParams as $key => $value) {
            if (!in_array(strtolower($key), $paramsToRemove)) {
                $queries[] = $key . "=" . $value;
            }
        }
        if (count($queries) > 0) {
            $newurl .= '?' . implode("&", $queries);
        }
        return $newurl;
    }

}
