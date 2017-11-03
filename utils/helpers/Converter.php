<?php

Class Converter
{
    static function convertPut($string)
    {
        $paramsArray = (explode('&', $string));
        foreach ($paramsArray as $param)
        {
            $string = preg_replace('/\+/', ' ', $param);
            $value = explode('=', $string);
            $params[$value[0]]=$value[1];
        }
        return $params;
    }
	
    static function convertFormat($format, $data)
    {
        switch ($format)
        {
            case '.json':
                header("Content-Type: application/json");
                $response = json_encode($data);
                break;
            case '.txt':
                header("Content-Type: text/plain");
                $response = print_r($data, true);
                break;
            case '.html':
                header("Content-Type: text/html");
                $result = print_r($data, true);
                $response = "<html><head></head><body><pre>" . $result . "</pre></body></html>";
                break;   
            case '.xml':
                header("Content-Type: application/xml"); 
                $xml = new SimpleXMLElement('<car/>');
                foreach($data as $car)
                {
                    $car = array_flip($car);
                    array_walk_recursive($car, array($xml, 'addChild'));
                }
                $response = $xml->asXML();
                break;
            default:
                break; 
        }
        return $response;
    }
}