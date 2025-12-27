<?php

function getData($conn, $sqlQuery) 
{
    $result = mysqli_query($conn, $sqlQuery);
    if(!$result)
    {
        die('Error in query: '. mysqli_error($conn));
    }
    $data= array();
    while ($row = mysqli_fetch_assoc($result)) 
    {
        array_push($data, $row);            
    }
    return $data;
}

function setData($conn,$insertUpdateQuery) 
{
    if( mysqli_query($conn, $insertUpdateQuery))
    {
        $status = "Record created";			
    }
    else
    {
        $status = "Record not Created";			
    }
    return $status;
}

function getID($conn,$insertUpdateQuery) 
{
    if( mysqli_query($conn, $insertUpdateQuery))
    {
        $status = "success";	
        $ID = 	$conn->insert_id;	
        $jsonresponse = array('code' => '200', 'status' => $status, 'ID' => $ID  );
        echo json_encode($jsonresponse);
    }
    else
    {
        $status = "Record not Created";	
        $jsonresponse = array('code' => '200', 'status' => $status, 'ID' => '0'  );
        echo json_encode($jsonresponse);

    }
    return $status;
}
?>