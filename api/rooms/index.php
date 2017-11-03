<?php
include ('../../utils/Rest.php');

Class Rooms extends Rest
    {
        /*connect to db*/
        private $db = null;

        public function __construct()
        {
            $this->db=DateBase::getInstance();
        }

        /*get all users from DB*/
        public function getRooms()
        {
            $query = "SELECT id, name FROM booker_rooms";
            $sth = $this->db->query($query);
            if($sth)
            {
                $data = $sth->fetchAll(PDO::FETCH_ASSOC);
                $this->createResponse($data, 200);
            }
            else
                $this->createResponse("Incorrect request", 404);
        }

        /*Registration new user */
        public function postRooms()
        {
           
        }

    }

$obj = new Rooms();
$obj->start();

