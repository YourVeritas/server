<?php
include ('../../utils/Rest.php');

Class Users extends Rest
    {
        /*connect to db*/
        private $db = null;

        public function __construct()
        {
            $this->db=DateBase::getInstance();
        }

        /*get all users from DB*/
        public function getUsers()
        {
            $query = "SELECT id, login, role, status, email FROM booker_users WHERE status = '1'";
            $sth = $this->db->query($query);
            if($sth)
            {
                $data = $sth->fetchAll(PDO::FETCH_ASSOC);
                $this->createResponse($data, 200);
            }
            else
                $this->createResponse(ERR_101, 404);
        }

        /*Registration new user */
        public function postUsers()
        {
            $login =  Validator::checkLogin($this->params['login'])? $this->params['login'] : false;
            $password = Validator::checkPassword($this->params['password'])? password_hash($this->params['password'], PASSWORD_BCRYPT):false;
            $email = Validator::checkEmail($this->params['email'])? $this->params['email'] : false;
            $role = $this->params['role']==1?1:2;

            if($login && $password && $email)
            {
                $query = "SELECT id from `booker_users` where login = '$login'";
                $sth = $this->db->query($query);
                if(!$sth->fetchColumn()>0)
                {
                    $hash =  md5(mt_rand());
                    $time = strtotime("now");
                    $query = "INSERT INTO booker_users (login, email, password, role, hash, time) VALUES ('$login', '$email', '$password', '$role', '$hash', '$time')";
                    $sth = $this->db->prepare($query);
                    if($sth->execute())
                        $this->createResponse('Succsses operation registration', 201);
                    else
                        $this->createResponse('Incorrect request registration', 404);               
                }
                else
                    $this->createResponse('Login reserved', 404);
            }
            else
                $this->createResponse('I need params login, email and password', 404);
        }

        /*Edit user info*/
        public function putUsers()
        {
            if($this->params['user_status'] && $this->params['user_status'] == 'disabled' && $this->params['id_user'])
            {
                $idUser = $this->params['id_user'];
                $query = "UPDATE booker_users SET status = '0' WHERE id = '$idUser'";
                $sth = $this->db->prepare($query);

                if($sth->execute())
                    $this->createResponse('Success operation disabled', 202);
                else
                    $this->createResponse('Incorrect request for disabled', 404);
            }
            
            if(Validator::checkParams($this->params, array('id_user', 'login', 'email')))
            {
                $idUser = $this->params['id_user'];
                $login = $this->params['login'];
                $email = str_replace('%40', '@', $this->params['email']);

                $query = "SELECT id FROM booker_users WHERE (login = '$login' OR email = '$email') AND id <> '$idUser'";       
                $sth = $this->db->query($query);

                if($sth->rowCount() == 0)
                {
                    $query = "UPDATE booker_users SET login = '$login', email = '$email' WHERE id = '$idUser'";
                    $sth = $this->db->prepare($query);

                    if($sth->execute())
                        $this->createResponse('Success operation PUT' ,202);
                    else
                        $this->createResponse('Incorrect request PUT user', 404);
                }
                else
                    $this->createResponse('Reserved', 200); 
            }
            else
                $this->createResponse('I need params', 404); 
        }
    }

$obj = new Users();
$obj->start();

