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
        <h2 class="text-center" style="margin-top: 5px; padding-top: 0;">Chat application using Ratchet Library</h2>
        <hr>
        <div class="row mt-5">
            <div class="col-md-4">
                <?php
                session_start();
                if (!isset($_SESSION['user'])) {
                    header("location: index.php");
                }
                require("db/Users.php");
                require("db/Chatrooms.php");

                $objChatroom = new Chatrooms;
                $chatrooms   = $objChatroom->getAllChatRooms();

                $objUser = new Users;
                $users   = $objUser->getAllUsers();
                ?>

                <div>
                    <div class="card mb-4">
                        <div class="card-body d-flex justify-content-between">
                            <div>
                                <?php
                                foreach ($_SESSION['user'] as $key => $user) {
                                    $userId = $key;
                                    echo '<input type="hidden" name="userId" id="userId" value="' . $key . '">';
                                    echo '<h5 class="card-title">' . $user['name'] . "</h5>";
                                    echo '<p class="card-text">' . $user['email'] . "</p>";
                                }
                                ?>
                            </div>
                            <input type="button" class="btn btn-warning" id="leave-chat" name="leave-chat" value="Leave">
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <strong> List of Users</strong>
                        </div>
                        <ul class="list-group list-group-flush">
                            <?php
                            foreach ($users as $key => $user) {
                                $color = 'bg-danger';
                                if ($user['login_status'] == 1) {
                                    $color = '.bg-success';
                                }
                                if (!isset($_SESSION['user'][$user['id']])) {

                                    echo '<li class="list-group-item ' . $color . ' ">
                                    <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="fw-bold">' . $user['name'] . '</div>
                                        <div class="fst-italic"> ' . $user['email'] . '</div>
                                    </div>                                        
                                        <div>
                                            <div  class="fw-light">Last Login : </div>
                                            <div class="fst-italic">' . $user['last_login'] . ' </div>
                                        </div>
                                    </div>
                                </li>';
                                }
                            }
                            ?>
                        </ul>
                    </div>
                </div>


            </div>
            <div class="col-md-8">
                <div class="card mb-4">
                    <div id="messages">
                        <div id="chats" class="p-2">
                            <?php
                            foreach ($chatrooms as $key => $chatroom) {

                                if ($userId == $chatroom['userid']) {
                                    $from = "Me";
                                    $class = "alert-success ms-auto";
                                } else {
                                    $from = $chatroom['name'];
                                    $class = "alert-primary me-auto";
                                }

                                echo ' 
                                    <div class="alert w-75 ' . $class . '" role="alert">
                                        <div class="d-flex justify-content-between">
                                            <div class="text-capitalize fw-bold"> ' . $from . ' :</div>
                                            <div class="fst-italic fw-light">' . date("d/m/Y h:i:s A", strtotime($chatroom['created_on'])) . '</div>
                                        </div>
                                        <div>' . $chatroom['msg'] . '</div>
                                    </div>
                                ';
                            }
                            ?>
                        </div>

                    </div>
                </div>


                <form id="chat-room-frm" method="post" action="">
                    <div class="form-group">
                        <textarea class="form-control" id="msg" name="msg" placeholder="Enter Message"></textarea>
                    </div>
                    <div class="form-group mt-2">
                        <input type="button" value="Send" class="btn btn-success btn-block" id="send" name="send">
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>
</body>

<script type="text/javascript">
    $(document).ready(function() {
        var conn = new WebSocket('ws://localhost:8080');
        conn.onopen = function(e) {
            console.log("Connection established!");
        };

        conn.onmessage = function(e) {
            console.log(e.data);
            var data = JSON.parse(e.data);
            var row = `       <div class="alert alert-success w-75 ms-auto" role="alert">
                                    <div class="d-flex justify-content-between">
                                        <div class="text-capitalize fw-bold"> ${data.from} :</div>
                                        <div class="fst-italic fw-light">${data.dt} </div>
                                    </div>
                                    <div>${data.msg} </div>
                                </div>`;
            $('#chats').append(row);
        };

        conn.onclose = function(e) {
            console.log("Connection Closed!");
        }

        $("#send").click(function() {
            var userId = $("#userId").val();
            var msg = $("#msg").val();
            var data = {
                userId: userId,
                msg: msg
            };
            conn.send(JSON.stringify(data));
            $("#msg").val("");
        });

        $("#leave-chat").click(function() {
            var userId = $("#userId").val();
            $.ajax({
                url: "action.php",
                method: "post",
                data: "userId=" + userId + "&action=leave"
            }).done(function(result) {
                var data = JSON.parse(result);
                if (data.status == 1) {
                    conn.close();
                    location = "index.php";
                } else {
                    console.log(data.msg);
                }

            });

        })
    })
</script>

</html>