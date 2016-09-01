<?php 
/* Open a socket and write data */
$address = '192.9.200.125';
$port = 50000;
$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP); 
//socket_connect($sock, $address, $port); 
//if ($sock === false) {
//    echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
//} else {
//    echo "OK.\n";//
//}
if (!socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1)) { 
    echo socket_strerror(socket_last_error($sock)); 
    exit; 
}

function connect(){
    global $sock;
    global $address;
    global $port;
    $connected = FALSE;
    while( $connected === FALSE )
    {
        sleep(1);
        $connected = socket_connect($sock, $address, $port);
        if ($connected == false) {
            echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
        } else {
            echo "Sucessfully Connected! to " . " " . $address . "\n";
        }
    }
}

function db_connect($addr, $line){
    $servername = "192.9.200.2";
    $username = "test";
    $password = "111";
    $dbname = "pi_server";

    //    $sql_address = "$address";
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    //$conn->select_db("pi_server") or die(mysql_error());
    //$result = mysqli_query($sql, null) or die(mysql_error());
    $result = mysqli_query($conn, "SELECT id FROM module_1 WHERE IP_ADDRESS = '$addr'");
//    $row_count = mysqli_num_rows($result);
    if($result->num_rows > 0) {
        echo "match found:  ";
        $i = 0;
        $myArray = explode(',', $line);
        foreach($myArray as $my_Array){
            $rc[$i++] = $my_Array;
        }
        $update = "UPDATE module_1 SET CH1_A1 = '$rc[0]', CH1_A2 = '$rc[1]', CH2_A1 = '$rc[2]', CH2_A2 = '$rc[3]', CH3_A1 = '$rc[4]', CH3_A2 = '$rc[5]', CH4_A1 = '$rc[6]', CH4_A2 = '$rc[7]'
        , CH5_A1 = '$rc[8]', CH5_A2 = '$rc[9]', CH6_A1 = '$rc[10]', CH6_A2 = '$rc[11]', CH7_A1 = '$rc[12]', CH7_A2 = '$rc[13]', CH8_A1 = '$rc[14]', CH8_A2 = '$rc[15]'
         WHERE IP_ADDRESS = '$addr'";
        if ($conn->query($update) === TRUE) {
            echo "Updated record successfully\n";
        } else {
            echo "Error: " . $update . "<br>" . $conn->error;
        }
    } else {
        $sql = "INSERT INTO module_1 " ."(IP_ADDRESS, CH1_A1, CH1_A2, CH2_A1, CH2_A2, CH3_A1, CH3_A2, CH4_A1, CH4_A2, CH5_A1, CH5_A2, CH6_A1, CH6_A2, CH7_A1, CH7_A2, CH8_A1, CH8_A2) ".
        "VALUES ('$addr'," . "$line)";
        if ($conn->query($sql) === TRUE) {
            echo "New record created successfully\n";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
            echo $line;
        }
    }

    $conn->close();
}


connect();

//if (socket_bind($sock, $address, $port) === false) {
//    echo "socket_bind() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";//
//}
while (true) {
    sleep(1);
    $line = socket_read($sock, 256);

    if ($line == false) {
        //Prototype reconnect after a fail read return
        echo "connection has been shutdown\n";
        socket_shutdown($sock, 2);
        socket_close($sock);
        unset($sock);
        global $sock;
        //$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        connect();
    }else{
        $newline = array_shift(explode("\n", $line));
//        echo $newline . "\n";
        $n_line = substr($newline, 0, -2);
        ereg_replace("[[:cntrl:]]", "", $n_line);
//        echo $n_line;
        if(strlen($n_line) > 15)
	        db_connect($address, $n_line);
    }
}
?>

