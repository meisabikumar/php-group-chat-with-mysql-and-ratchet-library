<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.min.js" integrity="sha512-aVKKRRi/Q/YV+4mjoKBsE4x3H+BkegoM/em46NNlCqNTmUYADjBbeNefNxYV7giUp0VxICtqdrbqU7iVaeZNXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
</head>

<body>
    <div class="container">
        <h2 class="text-center" style="margin-top: 5px; padding-top: 0;">Chat application in PHP & MySQL using Ratchet Library</h2>
        <hr>
        <?php
        if (isset($_POST['join'])) {
            session_start();
            require("db/Users.php");
            $objUser = new Users;
            $objUser->setEmail($_POST['email']);
            $objUser->setName($_POST['uname']);
            $objUser->setLoginStatus(1);
            $objUser->setLastLogin(date('Y-m-d h:i:s'));
            $userData = $objUser->getUserByEmail();
            if (is_array($userData) && count($userData) > 0) {
                $objUser->setId($userData['id']);
                if ($objUser->updateLoginStatus()) {
                    echo "User login..";
                    $_SESSION['user'][$userData['id']] = $userData;
                    header("location: chatroom.php");
                } else {
                    echo "Failed to login.";
                }
            } else {
                if ($objUser->save()) {
                    $lastId = $objUser->dbConn->lastInsertId();
                    $objUser->setId($lastId);
                    $_SESSION['user'][$lastId] = [
                        'id' => $objUser->getId(),
                        'name' => $objUser->getName(),
                        'email' => $objUser->getEmail(),
                        'login_status' => $objUser->getLoginStatus(),
                        'last_login' => $objUser->getLastLogin()
                    ];

                    echo "User Registred..";
                    header("location: chatroom.php");
                } else {
                    echo "Failed..";
                }
            }
        }
        ?>
        <div class="row join-room justify-content-center mt-5">
            <div class="col-md-4">
                <form id="join-room-frm" role="form" method="post" action="" class="form-horizontal">

                    <div class="mb-3">
                        <label for="exampleFormControlInput1" class="form-label">Name</label>
                        <input type="text" class="form-control" id="uname" name="uname" placeholder="Enter Name">
                    </div>

                    <div class="mb-3">
                        <label for="exampleFormControlInput1" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email Address" placeholder="name@example.com">
                    </div>

                    <div class="form-group">
                        <input type="submit" value="JOIN CHATROOM" class="btn btn-success btn-block" id="join" name="join">
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>
</body>

</html>