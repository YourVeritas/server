<?php
include ('../../utils/Rest.php');

Class Auth extends Rest
    {
        /*Connect to DB*/
        private $db = null;

        public function __construct()
        {
            $this->db=DateBase::getInstance();
        }

        /*LogIn and create new hash of user*/
        public function putAuth()
        {
            if (count($this->params)>0)
            {
                $login =  Validator::checkLogin($this->params['login'])? $this->params['login'] : false;
                $password = Validator::checkPassword($this->params['password'])? $this->params['password']:false;

                $query = "SELECT id as id_user, password from booker_users where login = '$login'";
                $sth = $this->db->query($query);
                $user = $sth->fetch(PDO::FETCH_ASSOC);
                if (count($user)>0)
                {
                    if (password_verify($password, $user['password']))
                    {
                        $hash =  md5(mt_rand());
                        $time = time();

                        $query = "UPDATE booker_users SET hash = '$hash', time = '$time' where login = '$login'";
                        $sth = $this->db->prepare($query);
                        if ($sth->execute())
                        {
                            $data['id_user']=$user['id_user'];
                            $data['hash']=$hash;
                            $this->createResponse($data, 202);
                        } 
                        else 
                            $this->createResponse('Incorrect request', 404);             
                    }
                    else
                        $this->createResponse($login.'.'.$password.'.', 404);
                }
                else
                    $this->createResponse('We didn\' find user by this login', 404);
            }         
            else
                $this->createResponse('I need params', 404);
        }




        /*Check auth by id_user and hash*/
        public function getAuthByParams()
        {
            list($id_user, $hash) = explode('/', $this->params, 2);
            $id_user = Validator::checkId($id_user)? $id_user : false;

            if ($id_user && $hash && !empty($hash))
            {
                $sth = $this->db->prepare("SELECT hash, login, role from `booker_users` where id = '$id_user'");
                $sth->execute();

                if ($sth->execute())
                {
                    $hashInput = strlen($hash)==32?$hash:false;
                    $dataUser = $sth->fetch(PDO::FETCH_ASSOC);

                    if ($hashInput == $dataUser['hash'])
                    {
                        unset($dataUser['hash']);
                        $this->createResponse($dataUser, 200);
                    }
                    else
                        $this->createResponse('Incorrect hash', 404);
                }
                else
                    $this->createResponse($sth->errorInfo(), 404); 
            }
            else
                $this->createResponse(ERR_209, 404);               
        }
    }

$obj = new Auth();
$obj->start();

