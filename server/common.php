<?php

function Debug() {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

function Breakpoint($message) {
    header("Content-type: application/json");
    print_r($message);
    exit();
}

function Alert($message) {
    exit("<script>alert(\"{$message}\"); window.history.back();</script>");
}

function GetSiteData() {
    $data = file_get_contents("data.json");
    $data = json_decode($data, true);
    return $data;
}

function SetSiteData($input) {
    $input = json_encode($input);

    if ($input == false) {
        return false;
    }

    LogData($input);
    return file_put_contents("data.json", $input);
}

function LogData($input) {
    $date = date("Y-m-d H-i-s");
    return file_put_contents("log/{$date}.json", $input);
}

function FindIndex($input, $key, $value) {
    foreach ($input as $index => $item) {
        if ($item[$key] == $value) {
            return $index;
        }
    }

    return -1;
}

function PasswordStretch($userId, $password) {
    $userId = base64_encode($userId);
    $password = base64_encode($password);
    $result = "";

    while (strlen($password) < strlen($userId)) {
        $password .= $password;
    }

    foreach (str_split($password) as $key => $value) {
        $result .= $value.substr($userId, $key % strlen($userId), 1);
    }

    return $result;
}

function NewSession($userId) {
    $data = GetSiteData();
    $userIndex = FindIndex($data["users"], "id", $userId);
    
    if ($userIndex == -1) {
        return false;
    }

    $user = $data["users"][$userIndex];

    foreach($data["sessions"] as $key => $element) {
        if ($element["userid"] == $user["id"]) {
            unset($data["sessions"][$key]);
        }
    }

    $data["sessions"] = array_values($data["sessions"]);

    $newSession = [
        "id" => uniqid("session"),
        "userid" => $user["id"],
        "expiry" => time() + 86400,
        "time" => time()
    ];
    
    $data["sessions"][] = $newSession;
    
    if (SetSiteData($data) == false) {
        Alert("There was an oopsy woopsy, a little fucky wucky processing the data. Pls check if you typed any invalid characters.");
    }

    return $newSession["id"];
}

?>