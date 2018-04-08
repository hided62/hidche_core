<?php
namespace sammo;

function MYDB_query($query, $connect)
{
    return mysqli_query($connect, $query);
}

function MYDB_num_rows($result) : int
{
    return mysqli_num_rows($result);
}

/**
 * @return mixed[]
 */
function MYDB_fetch_array($result)
{
    return mysqli_fetch_array($result);
}

function MYDB_fetch_row($result)
{
    return mysqli_fetch_row($result);
}

function MYDB_list_tables($db, $connect)
{
    return mysqli_query($connect, "show tables");
}

function MYDB_error($connect)
{
    return mysqli_error($connect);
}

function MYDB_close($connect)
{
    return mysqli_close($connect);
}
