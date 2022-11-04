<?php
class users
{
    private $id;
    private $name;
    private $email;
    private $loginStatus;
    private $lastLogin;
    public $dbConn;

    function setId($id)
    {
        $this->id = $id;
    }
    function getId()
    {
        return $this->id;
    }
    function setName($name)
    {
        $this->name = $name;
    }
    function getName()
    {
        return $this->name;
    }
    function setEmail($email)
    {
        $this->email = $email;
    }
    function getEmail()
    {
        return $this->email;
    }
    function setLoginStatus($loginStatus)
    {
        $this->loginStatus = $loginStatus;
    }
    function getLoginStatus()
    {
        return $this->loginStatus;
    }
    function setLastLogin($lastLogin)
    {
        $this->lastLogin = $lastLogin;
    }
    function getLastLogin()
    {
        return $this->lastLogin;
    }

    public function __construct()
    {
        require_once("DbConnect.php");
        $db = new DbConnect();
        $this->dbConn = $db->connect();
    }

    public function save()
    {
        $sql = "INSERT INTO `users`(`id`, `name`, `email`, `login_status`, `last_login`) VALUES (null, :name, :email, :loginStatus, :lastLogin)";
        $stmt = $this->dbConn->prepare($sql);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":loginStatus", $this->loginStatus);
        $stmt->bindParam(":lastLogin", $this->lastLogin);
        try {
            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
