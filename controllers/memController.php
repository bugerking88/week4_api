<?php

require_once("PDOConnect.php");

class memController extends PDOConnect
{

    //創會員
    public function addMember()
    {
        if (isset($_GET["username"]) && ($_GET["username"] != null))
        {
            $user = $_GET["username"];

            $sql = "INSERT INTO `memberList`(`userName`, `balance`)";
            $sql .= "VALUES(:userName, :balance)";
            $money = "100000";
            $result = $this->db->prepare($sql);
            $result->bindParam(':userName', $_GET["username"]);
            $result->bindParam(':balance', $money);
            if ($result->execute()) {
            $user_info = array("result" => "TRUE", "username" => $_GET["username"]);
            echo json_encode($user_info);
            } else {
                $user_info = array("result" => "FALSE", "username" => "repeat");
                echo json_encode($user_info);
            }
        } else {
            $user_info = array("result" => "FALSE", "errorMessage" => "parameter not enough");
            echo json_encode($user_info);
        }

    }

    //取得餘額
    public function getBalance()
    {
        if (isset($_GET["username"]) && ($_GET["username"] != null))
        {
            $user = $_GET["username"];
            $sql = "SELECT `balance`,`platformB` FROM `memberList` WHERE `userName`="."'".$user."'";
            $result = $this->db->prepare($sql);
            $result->execute();
            $getBalance = $result->fetchAll();

            $sql = "SELECT `userName` FROM `memberList` WHERE `userName`="."'".$user."'";
            $result = $this->db->prepare($sql);
            $result->execute();
            $getUser = $result->fetchAll();

            if ($getUser == null) {
                $show_wrong = array("result" => "false", "message" => "there is no user");
                echo json_encode($show_wrong);
            } else {
                $user_info = array("result" => "TRUE", "username" => $_GET["username"], "AplatBalance" => $getBalance[0]['balance'], "BplatBalance" => $getBalance[0]['platformB']);
                echo json_encode($user_info);
            }
        } else {
            $user_info = array("result" => "FALSE", "errorMessage" => "parameter not enough");
            echo json_encode($user_info);
        }
    }

    //轉帳
    public function transfer()
    {
        if (isset($_GET["username"]) && isset($_GET["transid"]) && isset($_GET["type"]) && isset($_GET["amount"])) {
            $user = $_GET["username"];
            $transid = $_GET["transid"];
            $type = $_GET["type"];
            $amount = $_GET["amount"];

            $sql = "SELECT `transid` FROM `memoList`";
            $result = $this->db->prepare($sql);
            $result->execute();
            $getTransid = $result->fetchAll();

            foreach ($getTransid as $value) {
                foreach ($value as $b){
                    if ($b == $transid) {
                        $show_wrong = array("result" => "false", "message" => "transid repeat");
                        echo json_encode($show_wrong);
                        exit;
                    }
                }
            }

                $sql = "SELECT `balance`,`platformB` FROM `memberList` WHERE `userName`="."'".$user."'";
                $result = $this->db->prepare($sql);
                $result->execute();
                $getBalance = $result->fetchAll();
                if ($type == "OUT") {
                    $Aplat = $getBalance[0]['balance'] - $amount;
                    $Bplat = $getBalance[0]['platformB'] + $amount;

                    if ($Aplat >= 0) {
                    $sql2 = "UPDATE `memberList` SET `balance` = :balance , `platformB` = :platformB WHERE `userName`="."'".$user."'";
                    $updateBalance = $this->db->prepare($sql2);
                    $updateBalance->bindParam(':balance', $Aplat);
                    $updateBalance->bindParam(':platformB', $Bplat);
                    $updateBalance->execute();

                    $sql = "INSERT INTO `memoList`(`transid`, `userName`, `status`)";
                    $sql .= "VALUES(:transid, :userName, :status)";
                    $status = "TRUE";
                    $result = $this->db->prepare($sql);
                    $result->bindParam(':transid', $transid);
                    $result->bindParam(':userName', $user);
                    $result->bindParam(':status', $status);
                    $result->execute();
                    $sql = "SELECT `balance`,`platformB` FROM `memberList` WHERE `userName`="."'".$user."'";
                    $result = $this->db->prepare($sql);
                    $result->execute();
                    $getLastBalance = $result->fetchAll();
                    $user_info = array("result" => "TRUE", "username" => $_GET["username"], "balance" => $getLastBalance[0]['balance'], "platformB" => $getLastBalance[0]['platformB']);
                    echo json_encode($user_info);
                    } else {
                        $user_info = array("result" => "false", "message" => "A platform not enough money");
                        echo json_encode($user_info);
                        return;
                    }
                }
                if ($type == "IN") {
                    $Aplat = $getBalance[0]['balance'] + $amount;
                    $Bplat = $getBalance[0]['platformB'] - $amount;

                    if ($Bplat >= 0) {
                        $sql3 = "UPDATE `memberList` SET `balance` = :balance , `platformB` = :platformB WHERE `userName`="."'".$user."'";
                        $updateBalance = $this->db->prepare($sql3);
                        $updateBalance->bindParam(':balance', $Aplat);
                        $updateBalance->bindParam(':platformB', $Bplat);
                        $updateBalance->execute();

                        $sql = "INSERT INTO `memoList`(`transid`, `userName`, `status`)";
                        $sql .= "VALUES(:transid, :userName, :status)";
                        $status = "TRUE";
                        $result = $this->db->prepare($sql);
                        $result->bindParam(':transid', $transid);
                        $result->bindParam(':userName', $user);
                        $result->bindParam(':status', $status);
                        $result->execute();
                        $sql = "SELECT `balance`,`platformB` FROM `memberList` WHERE `userName`="."'".$user."'";
                        $result = $this->db->prepare($sql);
                        $result->execute();
                        $getLastBalance = $result->fetchAll();
                        $user_info = array("result" => "TRUE", "username" => $_GET["username"], "balance" => $getLastBalance[0]['balance'], "platformB" => $getLastBalance[0]['platformB']);
                        echo json_encode($user_info);
                    } else {
                        $user_info = array("result" => "false", "message" => "B platform not enough money");
                        echo json_encode($user_info);
                        return;
                    }
                }
            } else {
                $user_info = array("result" => "FALSE", "errorMessage" => "parameter not enough");
                echo json_encode($user_info);
            }
    }

    //轉帳確認
    public function checkTransfer(){
         if (isset($_GET["username"]) && isset($_GET["transid"]) && ($_GET["username"] != null) && ($_GET["transid"] != null))
         {
            $user = $_GET["username"];
            $transid = $_GET["transid"];
            $sql = "SELECT * FROM `memoList` WHERE `transid`="."'".$transid."'"."AND `userName`="."'".$user."'";
            $result = $this->db->prepare($sql);
            $result->execute();
            $getMemo = $result->fetchAll();

            if ($getMemo == null) {
                $user_info = array("result" => "FALSE", "username" => $_GET["username"], "status" => "Not found Transaction.");
                echo json_encode($user_info);
            } else {
                $user_info = array("result" => "TRUE", "username" => $user, "transid" => $transid,"status" => $getMemo[0]['status']);
                echo json_encode($user_info);
            }
        } else {
            $user_info = array("result" => "FALSE", "errorMessage" => "parameter not enough");
            echo json_encode($user_info);
        }
    }
}

