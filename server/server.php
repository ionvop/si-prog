<?php

include("common.php");
Debug();
header("Content-Type: application/json");
$_POST = json_decode(file_get_contents("php://input"), true);

if (isset($_POST["method"])) {
    switch ($_POST["method"]) {
        case "register":
            Register();
            break;
        case "login":
            Login();
            break;
    }
} else {
    DefaultMethod();
}

function Register() {
    $data = GetSiteData();

    $res = [
        "status" => 0,
        "response" => ""
    ];

    if ($_POST["name"] == "") {
        $res["status"] = 1;
        $res["response"] = "Full name is empty.";
        exit(json_encode($res));
    }

    if ($_POST["email"] == "" || filter_var($_POST["email"], FILTER_VALIDATE_EMAIL) == false) {
        $res["status"] = 1;
        $res["response"] = "The email you have entered is not valid.";
        exit(json_encode($res));
    }

    if ($_POST["password"] == "") {
        $res["status"] = 1;
        $res["response"] = "The password is empty.";
        exit(json_encode($res));
    }

    if ($_POST["password"] != $_POST["repassword"]) {
        $res["status"] = 1;
        $res["response"] = "The passwords do not match.";
        exit(json_encode($res));
    }

    if (strlen($_POST["password"]) < 4) {
        $res["status"] = 1;
        $res["response"] = "Your password is too short. Please have atleast 4 characters.";
        exit(json_encode($res));
    }

    if (FindIndex($data["users"], "email", $_POST["email"]) != -1) {
        $res["status"] = 1;
        $res["response"] = "There's already a user registered with that email.";
        exit(json_encode($res));
    }

    $newUser = [
        "id" => uniqid("user", false),
        "name" => $_POST["name"],
        "email" => $_POST["email"],
        "hash" => "",
        "avatar" => "uploads/default.jpg"
    ];

    $newUser["hash"] = PasswordStretch($newUser["id"], $_POST["password"]);
    $data["users"][] = $newUser;
    SetSiteData($data);
    $res["status"] = 0;
    $res["response"] = NewSession($newUser["id"]);
    exit(json_encode($res));
}

function Login() {
    $data = GetSiteData();

    $res = [
        "status" => 0,
        "response" => ""
    ];

    $userIndex = FindIndex($data["users"], "email", $_POST["email"]);

    if ($userIndex == -1) {
        $res["status"] = 1;
        $res["response"] = "Invalid login credentials.";
        exit(json_encode($res));
    }

    $user = $data["users"][$userIndex];

    if (PasswordStretch($user["id"], $_POST["password"]) != $user["hash"]) {
        $res["status"] = 1;
        $res["response"] = "Invalid login credentials.";
        exit(json_encode($res));
    }

    $res["status"] = 0;
    $res["response"] = NewSession($user["id"]);
    exit(json_encode($res));
}

function DefaultMethod() {
    echo "Penis";
}

?>