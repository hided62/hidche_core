<?php

function MYDB_connect($host, $id, $pw) {
    $conn = mysqli_connect($host, $id, $pw);
    mysqli_query($conn, 'set names utf8');
    return $conn;
}

function MYDB_select_db($db, $connect) {
    return mysqli_select_db($connect, $db);
}

function MYDB_query($query, $connect) {
    return mysqli_query($connect, $query);
}

function MYDB_num_rows($result) {
    return mysqli_num_rows($result);
}

function MYDB_fetch_array($result) {
    return mysqli_fetch_array($result);
}

function MYDB_fetch_row($result) {
    return mysqli_fetch_row($result);
}

function MYDB_list_tables($db, $connect) {
    return mysqli_query($connect, "show tables");
}

function MYDB_error($connect) {
    return mysqli_error($connect);
}

function MYDB_close($connect) {
    return mysqli_close($connect);
}

?>
