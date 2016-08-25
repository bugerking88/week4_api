<?php

require_once("PDOConnect.php");

class memController extends PDOConnect
{

    //創會員
    public function addMember()
    {
        if (isset($_GET["username"]))
        {
            $sql = "INSERT INTO `memberList`(`userName`, `balance`)";
            $sql .= "VALUES(:userName, :balance)";
            $money = "100000";
            $result = $this->db->prepare($sql);
            $result->bindParam(':userName', $_GET["username"]);
            $result->bindParam(':balance', $money);
            $result->execute();
            $user_info = array("result" => "TRUE", "username" => $_GET["username"]);
            echo json_encode($user_info);
        } else {
            $user_info = array("result" => "FALSE", "username" => $_GET["username"]);
            echo json_encode($user_info);
        }

    }

    //取得餘額
    public function getBalance()
    {
        if (isset($_GET["username"]))
        {
            $user = $_GET["username"];
            $sql = "SELECT `balance` FROM `memberList` WHERE `userName`="."'".$user."'";
            $result = $this->db->prepare($sql);
            $result->execute();
            $getBalance = $result->fetchAll();
            $user_info = array("result" => "TRUE", "username" => $_GET["username"], "balance" => $getBalance[0]['balance']);
            echo json_encode($user_info);
        } else {
            $user_info = array("result" => "FALSE", "username" => $_GET["username"]);
            echo json_encode($user_info);
        }
    }

    //轉帳
    public function transfer()
    {
        if (isset($_GET["username"]) && isset($_GET["transid"]) && isset($_GET["type"]) && isset($_GET["amount"]))
        {
            $user = $_GET["username"];
            $transid = $_GET["transid"];
            $type = $_GET["type"];
            $amount = $_GET["amount"];

            $sql = "SELECT `balance`,`platformB` FROM `memberList` WHERE `userName`="."'".$user."'";
            $result = $this->db->prepare($sql);
            $result->execute();
            $getBalance = $result->fetchAll();

            if ($type == "OUT") {

                $Aplat = $getBalance[0]['balance'] - $amount;
                $Bplat = $getBalance[0]['platformB'] + $amount;

                $sql2 = "UPDATE `memberList` SET `balance` = :balance , `platformB` = :platformB WHERE `userName`="."'".$user."'";
                $updateBalance = $this->db->prepare($sql2);
                $updateBalance->bindParam(':balance', $Aplat);
                $updateBalance->bindParam(':platformB', $Bplat);
                $updateBalance->execute();

                $sql = "INSERT INTO `memoList`(`transid`, `userName`, `status`)";
                $sql .= "VALUES(:transid, :userName, :status)";
                $status = "TRUE";
                $result = $this->db->prepare($sql);
                $result->bindParam(':transid', $user);
                $result->bindParam(':userName', $transid);
                $result->bindParam(':status', $status);
                $result->execute();
            }
            if ($type == "IN") {

                $Aplat = $getBalance[0]['balance'] + $amount;
                $Bplat = $getBalance[0]['platformB'] - $amount;

                $sql3 = "UPDATE `memberList` SET `balance` = :balance , `platformB` = :platformB WHERE `userName`="."'".$user."'";
                $updateBalance = $this->db->prepare($sql3);
                $updateBalance->bindParam(':balance', $Aplat);
                $updateBalance->bindParam(':platformB', $Bplat);
                $updateBalance->execute();

                $sql = "INSERT INTO `memoList`(`transid`, `userName`, `status`)";
                $sql .= "VALUES(:transid, :userName, :status)";
                $status = "TRUE";
                $result = $this->db->prepare($sql);
                $result->bindParam(':transid', $user);
                $result->bindParam(':userName', $transid);
                $result->bindParam(':status', $status);
                $result->execute();
            }
            $user_info = array("result" => "TRUE", "username" => $_GET["username"], "balance" => $getBalance[0]['balance'], "platformB" => $getBalance[0]['platformB']);
            echo json_encode($user_info);
        } else {
            $sql = "INSERT INTO `memoList`(`transid`, `userName`, `status`)";
            $sql .= "VALUES(:transid, :userName, :status)";
            $status = "FALSE";
            $result = $this->db->prepare($sql);
            $result->bindParam(':transid', $user);
            $result->bindParam(':userName', $transid);
            $result->bindParam(':status', $status);
            $result->execute();
            $user_info = array("result" => "FALSE", "errorMessage" => "SomethingWrong");
            echo json_encode($user_info);
        }
    }

    //轉帳確認
    public function checkTransfer(){
         if (isset($_GET["username"]) && isset($_GET["transid"]))
         {
            $user = $_GET["username"];
            $transid = $_GET["transid"];
            $sql = "SELECT * FROM `memoList` WHERE `transid`="."'".$transid."'"."AND `userName`="."'".$user."'";
            $result = $this->db->prepare($sql);
            $result->execute();
            $getMemo = $result->fetchAll();

            $user_info = array("username" => $user, "transid" => $transid,"status" => $getMemo[0]['status']);
            echo json_encode($user_info);
        } else {
            $user_info = array("result" => "FALSE", "username" => $_GET["username"], "status" => "Not found Transaction.");
            echo json_encode($user_info);
        }
    }
}

