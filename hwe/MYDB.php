<?php
namespace sammo;

function MYDB_query($query, $connect)
{
    return mysqli_query($connect, $query);
}

function MYDB_num_rows(\mysqli_result $result) : int
{
    return mysqli_num_rows($result);
}

/**
 * @return mixed[]|null
 */
function MYDB_fetch_array(\mysqli_result $result)
{
    return mysqli_fetch_array($result);
}

function MYDB_fetch_row(\mysqli_result $result)
{
    return mysqli_fetch_row($result);
}

function MYDB_error($connect)
{
    return mysqli_error($connect);
}
