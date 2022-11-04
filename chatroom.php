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
        <div class="row">
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
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <td>
                                <?php
                                foreach ($_SESSION['user'] as $key => $user) {
                                    $userId = $key;
                                    echo '<input type="hidden" name="userId" id="userId" value="' . $key . '">';
                                    echo "<div>" . $user['name'] . "</div>";
                                    echo "<div>" . $user['email'] . "</div>";
                                }
                                ?>
                            </td>
                            <td align="right" colspan="2">
                                <input type="button" class="btn btn-warning" id="leave-chat" name="leave-chat" value="Leave">
                            </td>
                        </tr>
                        <tr>
                            <th colspan="3">Users</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($users as $key => $user) {
                            $color = 'color: red';
                            if ($user['login_status'] == 1) {
                                $color = 'color: green';
                            }
                            if (!isset($_SESSION['user'][$user['id']])) {
                                echo "<tr><td>" . $user['name'] . "</td>";
                                echo "<td><span class='glyphicon glyphicon-globe' style='" . $color . "'></span></td>";
                                echo "<td>" . $user['last_login'] . "</td></tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="col-md-8">
                <div id="messages">
                    <table id="chats" class="table table-striped">
                        <thead>
                            <tr>
                                <th colspan="4" scope="col"><strong>Chat Room</strong></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($chatrooms as $key => $chatroom) {

                                if ($userId == $chatroom['userid']) {
                                    $from = "Me";
                                } else {
                                    $from = $chatroom['name'];
                                }
                                echo '<tr><td valign="top"><div><strong>' . $from . '</strong></div><div>' . $chatroom['msg'] . '</div><td align="right" valign="top">' . date("d/m/Y h:i:s A", strtotime($chatroom['created_on'])) . '</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <form id="chat-room-frm" method="post" action="">
                    <div class="form-group">
                        <textarea class="form-control" id="msg" name="msg" placeholder="Enter Message"></textarea>
                    </div>
                    <div class="form-group">
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
            var row = '<tr><td valign="top"><div><strong>' + data.from + '</strong></div><div>' + data.msg + '</div><td align="right" valign="top">' + data.dt + '</td></tr>';
            $('#chats > tbody').prepend(row);
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