<?php
include('config.php');
include('Db.php');
include('helpers/Validator.php');
include('helpers/Converter.php');

class Rest
{
    protected $params;
    protected $table;
    protected $method;
    protected $contentFormat;
    protected $responseCode;
	
    public function start()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: PUT, POST, GET, DELETE');
        header('Access-Control-Allow-Headers: Authorization, Content-Type');
        $this->parsUrl();

        switch($this->method)
        {
        case 'GET':
            if($this->params == '')
                $this->setMethod('get'.ucfirst($this->table));
            else
                $this->setMethod('get'.ucfirst($this->table).'ByParams');
            break;
        case 'DELETE':
            $this->setMethod('delete'.ucfirst($this->table));
            break;
        case 'POST':
            $this->params = $_POST;
            $this->setMethod('post'.ucfirst($this->table));
            break;
        case 'PUT':
            $id= explode('/', $this->params, 1);
            $this->params = Converter::convertPut(file_get_contents("php://input")."&id=".$id);
            $this->setMethod('put'.ucfirst($this->table));
            break;
        default:
            return false;
        }
    }

    protected function setMethod($meth)
    {
        if (method_exists($this, $meth))
            $data = $this->$meth();
        else
            $this->createResponse(ERR_001, 505);
    }

    protected function createResponse($data, $code = 200)
    {
        $this->responseCode = $code;
        if(strval($code)[0] == 2)
        {
            $useFormat = $this->contentType; 
            if (empty($useFormat) || ($useFormat != '.json' && $useFormat != '.txt' && $useFormat != '.html' && $useFormat != '.xml'))
                $useFormat = FORMAT_RESPONSE;    
            $response = Converter::convertFormat($useFormat, $data);
        }
        else
            $response = 'ERROR:' . $code . " Message: " . $data;
            $this->sendStatus($code);
            echo $response;
    }

    protected function sendStatus($code){
        switch($code)
        {
        case '200':
            header("HTTP/1.0 200 OK");            
            break;
        case '201':
            header("HTTP/1.0 201 Created");            
            break;
        case '202':
            header("HTTP/1.0 202 Accepted");            
            break;
        case '204':
            header("HTTP/1.0 204 No Content");            
            break;   
        case '401':
            header("HTTP/1.0 401 Unauthorized");            
            break;
        case '404':
            header("HTTP/1.0 404 Not Found");
            break;
        case '406':
            header("HTTP/1.0 406 Not Acceptable");
            break;
        case '500':
            header("HTTP/1.0 500 Internal Server Error");
            break;
        case '505':
            header("HTTP/1.0 505 HTTP Version Not Supported");
            break;
        default:
            break; 
        }  
    }

    protected function parsUrl()
    {   
        $url = $_SERVER['REQUEST_URI'];
        list($s, $a, $d, $f, $db, $table, $path) = explode('/', $url, 7);
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->table = $table;
        if($path != '')
        {
            $clearString = mb_strtolower(strip_tags($path));
            $data = trim($clearString);
            preg_match("/\.\w+$/", $data, $format);
            $this->contentType = $format[0];
            $params = preg_replace("/\.\w+$/", "", $data);
            $this->params = Validator::clearData($params);
        }
    }
}
